<?php
namespace App\Services;

use App\Models\Feed;
use App\Models\Player;
use App\Models\PostalMail;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserStatsService
{
    public function compute(User $user): array
    {
        $isSqlite = config('database.default') === 'sqlite';
        $diffExpr = $isSqlite
            ? 'CAST(JULIANDAY(postal_mails.returned_date) - JULIANDAY(postal_mails.date_sent) AS INTEGER)'
            : 'DATEDIFF(postal_mails.returned_date, postal_mails.date_sent)';

        // ── base queries ──────────────────────────────────────────────────────
        $base = PostalMail::query()
            ->where('postal_mails.user_id', $user->id)
            ->whereNotNull('postal_mails.date_sent');

        $withReturns = (clone $base)->whereNotNull('postal_mails.returned_date');

        // ── headline numbers ──────────────────────────────────────────────────
        $totalSends   = (clone $base)->count();
        $totalReturns = (clone $withReturns)->count();
        $successRate  = $totalSends > 0 ? round(($totalReturns / $totalSends) * 100) : 0;

        $uniquePlayers = (clone $base)
            ->whereNotNull('postal_mails.signer_id')
            ->distinct()
            ->count('postal_mails.signer_id');

        // ── first / latest ────────────────────────────────────────────────────
        $firstSend        = (clone $base)->min('postal_mails.date_sent');
        $mostRecentReturn = (clone $withReturns)->max('postal_mails.returned_date');

        // ── fastest / longest (needs player name) ─────────────────────────────
        $playerJoin = function ($join) {
            $join->on('signers.signable_id', '=', 'players.id')
                 ->where('signers.signable_type', '=', Player::class);
        };

        $withPlayerQuery = (clone $withReturns)
            ->join('signers', 'postal_mails.signer_id', '=', 'signers.id')
            ->join('players', $playerJoin)
            ->selectRaw("postal_mails.id, postal_mails.date_sent, postal_mails.returned_date,
                          players.name as player_name, players.slug as player_slug,
                          ({$diffExpr}) as turnaround_days");

        $fastest = (clone $withPlayerQuery)->orderByRaw("{$diffExpr} ASC")->first();
        $longest = (clone $withPlayerQuery)->orderByRaw("{$diffExpr} DESC")->first();

        // ── favorite team ──────────────────────────────────────────────────────
        $favoriteTeam = (clone $base)
            ->join('signers', 'postal_mails.signer_id', '=', 'signers.id')
            ->join('players', $playerJoin)
            ->join('teams', 'players.team_id', '=', 'teams.id')
            ->whereNotNull('players.team_id')
            ->selectRaw('teams.name, COUNT(*) as cnt')
            ->groupBy('teams.id', 'teams.name')
            ->orderByDesc('cnt')
            ->first();

        // ── favorite category ──────────────────────────────────────────────────
        $favoriteCategory = (clone $base)
            ->join('signers', 'postal_mails.signer_id', '=', 'signers.id')
            ->join('players', $playerJoin)
            ->join('teams', 'players.team_id', '=', 'teams.id')
            ->join('categories', 'teams.category_id', '=', 'categories.id')
            ->whereNotNull('teams.category_id')
            ->selectRaw('categories.name, COUNT(*) as cnt')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('cnt')
            ->first();

        // ── most reacted return post ───────────────────────────────────────────
        // Use whereExists (not polymorphic whereHas) to avoid SQLite HAVING issue
        $mostReacted = Feed::where('followable_id', $user->id)
            ->where('feedable_type', PostalMail::class)
            ->whereExists(fn ($q) => $q
                ->from('postal_mails')
                ->whereColumn('postal_mails.id', 'feeds.feedable_id')
                ->whereNotNull('postal_mails.returned_date')
            )
            ->withCount('reactions')
            ->orderByDesc('reactions_count')
            ->first();

        if ($mostReacted && $mostReacted->reactions_count === 0) {
            $mostReacted = null;
        }

        return [
            'total_sends'        => $totalSends,
            'total_returns'      => $totalReturns,
            'success_rate'       => $successRate,
            'unique_players'     => $uniquePlayers,
            'first_send'         => $firstSend ? Carbon::parse($firstSend) : null,
            'most_recent_return' => $mostRecentReturn ? Carbon::parse($mostRecentReturn) : null,
            'fastest_return'     => $fastest ? [
                'player_name' => $fastest->player_name,
                'player_slug' => $fastest->player_slug,
                'days'        => (int) $fastest->turnaround_days,
            ] : null,
            'longest_wait'       => $longest ? [
                'player_name' => $longest->player_name,
                'player_slug' => $longest->player_slug,
                'days'        => (int) $longest->turnaround_days,
            ] : null,
            'favorite_team'      => $favoriteTeam?->name,
            'favorite_category'  => $favoriteCategory?->name,
            'most_reacted_return' => $mostReacted ? [
                'player_name'    => data_get($mostReacted->meta, 'player'),
                'reaction_count' => $mostReacted->reactions_count,
            ] : null,
        ];
    }
}
