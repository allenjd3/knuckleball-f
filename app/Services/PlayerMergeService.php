<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Card;
use App\Models\Event;
use App\Models\Fee;
use App\Models\InPersonAutograph;
use App\Models\Player;
use App\Models\PlayerTag;
use App\Models\PostalMail;
use App\Models\SetEntry;
use App\Models\SignerTag;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Merges or deletes duplicate Player profiles found by DuplicatePlayerFinder.
 *
 * Merging moves every relationship that hangs off the duplicate (TTM mail,
 * in-person autographs, addresses, fees, tags, packs, want lists,
 * watchlists, event invites, set entries) onto the surviving player, then
 * deletes the duplicate. Rows that
 * would collide with something the survivor already has (e.g. both are
 * already on the same want list, or share a tag) are dropped rather than
 * tripping the table's unique constraint.
 */
class PlayerMergeService
{
    /**
     * Plain pivot tables keyed by player_id with no dedicated Eloquent
     * model, and the column that identifies "the other side" of the pivot
     * for collision checks.
     *
     * @var array<string, string>
     */
    private const PLAYER_PIVOT_TABLES = [
        'pack_player' => 'pack_id',
        'want_list_player' => 'want_list_id',
        'watchlist_players' => 'user_id',
        'event_player' => 'event_id',
    ];

    public function merge(Player $survivor, Player $duplicate): void
    {
        if ($survivor->is($duplicate)) {
            throw new InvalidArgumentException('A player cannot be merged into itself.');
        }

        DB::transaction(function () use ($survivor, $duplicate) {
            $this->mergeSigner($survivor, $duplicate);
            $this->mergeMedia($survivor, $duplicate);
            $this->mergePlayerTags($survivor, $duplicate);

            foreach (self::PLAYER_PIVOT_TABLES as $table => $otherKey) {
                $this->reassignPivot($table, $otherKey, $survivor->id, $duplicate->id);
            }

            SetEntry::where('player_id', $duplicate->id)->update(['player_id' => $survivor->id]);
            Event::where('player_id', $duplicate->id)->update(['player_id' => $survivor->id]);

            $duplicate->delete();
        });
    }

    /**
     * Permanently deletes a player and everything that hangs off it (TTM
     * mail, cards, addresses, fees, tags, photo). Pack/want-list/watchlist/
     * event rows are cleaned up by the database's own cascading foreign
     * keys.
     */
    public function delete(Player $player): void
    {
        DB::transaction(function () use ($player) {
            $this->purgeSignerData($player);

            PlayerTag::where('player_id', $player->id)->delete();

            $player->media?->delete();

            $player->delete();
        });
    }

    private function mergeSigner(Player $survivor, Player $duplicate): void
    {
        $duplicateSigner = $duplicate->signer;

        if (! $duplicateSigner) {
            return;
        }

        $survivorSigner = $survivor->signer ?? $survivor->signer()->create();

        Fee::where('signer_id', $duplicateSigner->id)->update(['signer_id' => $survivorSigner->id]);
        Address::where('signer_id', $duplicateSigner->id)->update(['signer_id' => $survivorSigner->id]);
        PostalMail::where('signer_id', $duplicateSigner->id)->update(['signer_id' => $survivorSigner->id]);
        InPersonAutograph::where('signer_id', $duplicateSigner->id)->update(['signer_id' => $survivorSigner->id]);

        $survivorTagIds = SignerTag::where('signer_id', $survivorSigner->id)->pluck('tag_id');
        SignerTag::where('signer_id', $duplicateSigner->id)
            ->whereNotIn('tag_id', $survivorTagIds)
            ->update(['signer_id' => $survivorSigner->id]);
        SignerTag::where('signer_id', $duplicateSigner->id)->delete();

        $duplicateSigner->delete();
    }

    private function mergeMedia(Player $survivor, Player $duplicate): void
    {
        if (! $duplicate->media) {
            return;
        }

        // Keep the survivor's own photo if it has one — but the duplicate's
        // photo still needs to go, or it's left pointing at a player id
        // that's about to stop existing.
        if ($survivor->media) {
            $duplicate->media->delete();

            return;
        }

        $duplicate->media->update([
            'imageable_id' => $survivor->id,
            'imageable_type' => $survivor->getMorphClass(),
        ]);
    }

    private function mergePlayerTags(Player $survivor, Player $duplicate): void
    {
        $survivorTagIds = PlayerTag::where('player_id', $survivor->id)->pluck('tag_id');

        PlayerTag::where('player_id', $duplicate->id)
            ->whereNotIn('tag_id', $survivorTagIds)
            ->update(['player_id' => $survivor->id]);

        PlayerTag::where('player_id', $duplicate->id)->delete();
    }

    /**
     * Repoints every row in a player pivot table from $duplicateId to
     * $survivorId, dropping any that would collide with a row the survivor
     * already has (same $otherKey value) rather than erroring on the
     * table's unique constraint.
     */
    private function reassignPivot(string $table, string $otherKey, int $survivorId, int $duplicateId): void
    {
        $survivorOtherIds = DB::table($table)->where('player_id', $survivorId)->pluck($otherKey);

        DB::table($table)
            ->where('player_id', $duplicateId)
            ->whereNotIn($otherKey, $survivorOtherIds)
            ->update(['player_id' => $survivorId]);

        DB::table($table)->where('player_id', $duplicateId)->delete();
    }

    private function purgeSignerData(Player $player): void
    {
        $signer = $player->signer;

        if (! $signer) {
            return;
        }

        SignerTag::where('signer_id', $signer->id)->delete();
        Fee::where('signer_id', $signer->id)->delete();
        Address::where('signer_id', $signer->id)->delete();
        InPersonAutograph::where('signer_id', $signer->id)->delete();

        PostalMail::where('signer_id', $signer->id)->get()->each(function (PostalMail $postalMail) {
            $postalMail->feeMaterials()->detach();

            $postalMail->cards->each(function (Card $card) {
                $card->media()->delete();
                $card->delete();
            });

            $postalMail->delete();
        });

        $signer->delete();
    }
}
