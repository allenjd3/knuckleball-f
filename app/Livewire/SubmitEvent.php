<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Player;
use App\Services\GeocodingService;
use Laravel\Cashier\Cashier;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmitEvent extends Component
{
    use WithFileUploads;

    // Step tracking
    public int  $step      = 1;
    public bool $submitted = false;
    public ?int $submittedEventId = null;

    // Feature checkout (shown after successful submission)
    public bool   $featureCheckoutOpen    = false;
    public bool   $featureSuccess         = false;
    public string $featurePlan            = 'one_time';
    public string $featureError           = '';

    // Promoter Pass upsell (shown after successful submission)
    public bool   $promoterCheckoutOpen    = false;
    public bool   $promoterCheckoutSuccess = false;
    public string $promoterPlan            = 'monthly';
    public string $promoterError           = '';

    // Event Type
    #[Validate('required|in:player_signing,card_show,comic_con,memorabilia_show')]
    public string $type = 'player_signing';

    #[Validate('required_if:type,player_signing|in:in_person,mail_in|nullable')]
    public ?string $event_subtype = null;

    // Basic info
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|exists:players,id')]
    public ?int $player_id = null;

    public string $playerQuery      = '';
    public ?string $playerName      = null;   // display label (db or custom)
    public string $customPlayerName = '';     // free-text when not in db

    #[Validate(['expected_signer_ids' => 'array', 'expected_signer_ids.*' => 'exists:players,id'])]
    public array $expected_signer_ids = [];

    // Date & time
    #[Validate('boolean')]
    public bool $is_multi_day = false;

    #[Validate('required|date')]
    public string $start_date = '';

    #[Validate('required_if:is_multi_day,true|nullable|date|after_or_equal:start_date')]
    public ?string $end_date = null;

    #[Validate('nullable|date_format:H:i')]
    public ?string $start_time = null;

    #[Validate('nullable|date_format:H:i')]
    public ?string $end_time = null;

    // Location
    #[Validate('required_unless:event_subtype,mail_in|nullable|string|max:255')]
    public ?string $venue_name = null;

    #[Validate('nullable|string|max:255')]
    public ?string $address = null;

    #[Validate('required_unless:event_subtype,mail_in|nullable|string|max:100')]
    public ?string $city = null;

    #[Validate('required_unless:event_subtype,mail_in|nullable|string|max:100')]
    public ?string $state = null;

    #[Validate('nullable|string|max:10')]
    public ?string $zip_code = null;

    #[Validate('nullable|string|size:2')]
    public string $country = 'US';

    // Pricing items — dynamic list of {label, price}
    public array $pricingItems = [];

    // Options
    #[Validate('nullable|integer|min:1')]
    public ?int $max_items = null;

    #[Validate('boolean')]
    public bool $personalization_allowed = false;

    // Details
    #[Validate('nullable|string|max:2000')]
    public ?string $special_instructions = null;

    #[Validate('boolean')]
    public bool $registration_required = false;

    #[Validate('nullable|string|max:500')]
    public ?string $registration_link = null;

    #[Validate('nullable|string|max:255')]
    public ?string $contact_name = null;

    #[Validate('nullable|email')]
    public ?string $contact_email = null;

    #[Validate('nullable|integer|min:0')]
    public ?int $estimated_attendance = null;

    #[Validate('nullable|string|max:500')]
    public ?string $parking_info = null;

    #[Validate('nullable|string|max:500')]
    public ?string $accessibility_notes = null;

    #[Validate('nullable|string|max:100')]
    public ?string $age_restrictions = null;

    // Card show specific
    #[Validate('nullable|string|max:3000')]
    public ?string $description = null;

    #[Validate('nullable|string|max:500')]
    public ?string $website = null;

    #[Validate('nullable|string|max:255')]
    public ?string $promoter_name = null;

    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:5120')]
    public $heroImage = null;
    public array $photos = [];

    // Mail-in specific
    #[Validate('nullable|date')]
    public ?string $submission_deadline = null;

    #[Validate('nullable|date')]
    public ?string $expected_return_by = null;

    #[Validate('nullable|string|max:255')]
    public ?string $make_check_payable_to = null;

    #[Validate('boolean')]
    public bool $return_envelope_required = false;

    #[Validate('nullable|array')]
    public array $payment_methods = [];

    private function buildPricingItems(): ?array
    {
        $items = collect($this->pricingItems)
            ->filter(fn ($item) => ! empty($item['label']))
            ->map(fn ($item) => [
                'label' => trim($item['label']),
                'price' => is_numeric($item['price'] ?? '') ? (float) $item['price'] : null,
            ])
            ->values()
            ->all();

        return ! empty($items) ? $items : null;
    }

    public function addPricingItem(): void
    {
        $this->pricingItems[] = ['label' => '', 'price' => ''];
    }

    public function removePricingItem(int $index): void
    {
        array_splice($this->pricingItems, $index, 1);
        $this->pricingItems = array_values($this->pricingItems);
    }

    public function getPlayerResults(): array
    {
        if (strlen($this->playerQuery) < 2) return [];
        return Player::where('name', 'like', "%{$this->playerQuery}%")
            ->whereNotNull('published_at')
            ->limit(8)
            ->get()
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])
            ->toArray();
    }

    public function selectPlayer(int $id, string $name): void
    {
        $this->player_id          = $id;
        $this->customPlayerName   = '';
        $this->playerName         = $name;
        $this->playerQuery        = '';
    }

    public function useCustomPlayer(string $name): void
    {
        $this->player_id          = null;
        $this->customPlayerName   = $name;
        $this->playerName         = $name;
        $this->playerQuery        = '';
    }

    public function clearPlayer(): void
    {
        $this->player_id          = null;
        $this->customPlayerName   = '';
        $this->playerName         = null;
        $this->playerQuery        = '';
    }

    public function submit(): void
    {
        $this->validate(messages: [
            'player_id.exists' => 'Please select a valid player from the search results.',
        ]);

        if ($this->type === 'player_signing' && ! $this->player_id && ! $this->customPlayerName) {
            $this->addError('player_id', 'Please search for and select a player, or enter their name manually.');
            return;
        }

        $data = [
            'type'                    => $this->type,
            'event_subtype'           => $this->event_subtype,
            'name'                    => $this->name,
            'player_id'               => $this->player_id ?: null,
            'custom_player_name'      => $this->customPlayerName ?: null,
            'is_multi_day'            => $this->is_multi_day,
            'start_date'              => $this->start_date,
            'end_date'                => $this->is_multi_day ? $this->end_date : null,
            'start_time'              => $this->start_time,
            'end_time'                => $this->end_time,
            'venue_name'              => $this->venue_name,
            'address'                 => $this->address,
            'city'                    => $this->city,
            'state'                   => $this->state,
            'zip_code'                => $this->zip_code,
            'country'                 => $this->country,
            'pricing_items'           => $this->buildPricingItems(),
            'max_items'               => $this->max_items,
            'personalization_allowed' => $this->personalization_allowed,
            'special_instructions'    => $this->special_instructions,
            'registration_required'   => $this->registration_required,
            'registration_link'       => $this->registration_link,
            'contact_name'            => $this->contact_name,
            'contact_email'           => $this->contact_email,
            'estimated_attendance'    => $this->estimated_attendance,
            'parking_info'            => $this->parking_info,
            'accessibility_notes'     => $this->accessibility_notes,
            'age_restrictions'        => $this->age_restrictions,
            'description'             => $this->description,
            'website'                 => $this->website,
            'promoter_name'           => $this->promoter_name,
            'submission_deadline'     => $this->submission_deadline,
            'expected_return_by'      => $this->expected_return_by,
            'make_check_payable_to'   => $this->make_check_payable_to,
            'return_envelope_required' => $this->return_envelope_required,
            'payment_methods'         => $this->payment_methods ?: null,
            'user_id'                 => auth()->id(),
            'status'                  => 'pending',
        ];

        // Geocode address
        $fullAddress = collect([$this->address, $this->city, $this->state, $this->zip_code, $this->country])->filter()->implode(', ');
        if ($fullAddress) {
            $coords = app(GeocodingService::class)->geocode($fullAddress);
            if ($coords) {
                $data['latitude']  = $coords['latitude'];
                $data['longitude'] = $coords['longitude'];
            }
        }

        if ($this->heroImage) {
            $data['photos'] = [$this->heroImage->store('events', 'public')];
        }

        $addScheme = fn (?string $url): ?string =>
            $url && ! preg_match('#^https?://#i', $url) ? 'https://' . $url : $url;

        $data['website']           = $addScheme($data['website']);
        $data['registration_link'] = $addScheme($data['registration_link']);

        $event = Event::create($data);

        // Attach expected signers (card shows, comic cons, memorabilia shows)
        if (in_array($this->type, ['card_show', 'comic_con', 'memorabilia_show']) && ! empty($this->expected_signer_ids)) {
            $event->expectedSigners()->sync($this->expected_signer_ids);
        }

        $this->submittedEventId = $event->id;
        $this->submitted = true;
    }

    public function openFeatureCheckout(string $plan = 'one_time'): void
    {
        $this->featurePlan          = $plan;
        $this->featureError         = '';
        $this->featureCheckoutOpen  = true;
        $this->createFeatureIntent($plan);
    }

    private function createFeatureIntent(string $plan): void
    {
        $user = auth()->user();
        $user->createOrGetStripeCustomer();

        if ($plan === 'one_time') {
            $amount = match ($this->type) {
                'card_show', 'comic_con', 'memorabilia_show' => config('stripe_products.amounts.featured_card_show'),
                default => config('stripe_products.amounts.featured_signing'),
            };
            $intent = Cashier::stripe()->paymentIntents->create([
                'amount'               => $amount,
                'currency'             => 'usd',
                'customer'             => $user->stripe_id,
                'capture_method'       => 'automatic',
                'payment_method_types' => ['card'],
            ]);
            $this->dispatch('stripe-feature-intent-ready', clientSecret: $intent->client_secret, intentType: 'payment');
        } else {
            $intent = $user->createSetupIntent();
            $this->dispatch('stripe-feature-intent-ready', clientSecret: $intent->client_secret, intentType: 'setup');
        }
    }

    public function confirmFeaturePayment(string $paymentIntentId): void
    {
        $this->featureError = '';
        try {
            $pi = Cashier::stripe()->paymentIntents->retrieve($paymentIntentId);
            if ($pi->status !== 'succeeded') {
                $this->featureError = 'Payment not confirmed. Please try again.';
                return;
            }
            if ($pi->customer !== auth()->user()->stripe_id) {
                $this->featureError = 'Payment verification failed.';
                return;
            }
            $amount = match ($this->type) {
                'card_show', 'comic_con', 'memorabilia_show' => 19.99,
                default => 9.99,
            };
            \App\Models\FeaturedListing::create([
                'event_id'                 => $this->submittedEventId,
                'user_id'                  => auth()->id(),
                'plan_type'                => 'one_time',
                'stripe_payment_intent_id' => $pi->id,
                'amount_paid'              => $amount,
                'starts_at'               => now(),
                'expires_at'              => \App\Models\FeaturedListing::expiresAt('one_time'),
            ]);
            \App\Models\Event::find($this->submittedEventId)?->update(['is_featured' => true]);
            $this->featureSuccess        = true;
            $this->featureCheckoutOpen   = false;
        } catch (\Exception $e) {
            $this->featureError = $e->getMessage();
        }
    }

    public function confirmFeatureSubscription(string $paymentMethodId): void
    {
        $this->featureError = '';
        try {
            $user    = auth()->user();
            $priceId = config("stripe_products.prices.promoter_{$this->featurePlan}");
            if (! $priceId) {
                $this->featureError = 'Promoter Pass is not yet configured. Please contact support.';
                return;
            }
            $user->updateDefaultPaymentMethod($paymentMethodId);
            $subscription = $user->newSubscription('promoter', $priceId)->create($paymentMethodId);
            $user->events()->where('status', 'approved')->update(['is_featured' => true]);
            \App\Models\FeaturedListing::create([
                'event_id'               => $this->submittedEventId,
                'user_id'                => $user->id,
                'plan_type'              => $this->featurePlan,
                'stripe_subscription_id' => $subscription->stripe_id,
                'amount_paid'            => $this->featurePlan === 'yearly' ? 249.00 : 29.99,
                'starts_at'             => now(),
                'expires_at'            => null,
            ]);
            \App\Models\Event::find($this->submittedEventId)?->update(['is_featured' => true]);
            $this->featureSuccess       = true;
            $this->featureCheckoutOpen  = false;
            $this->promoterCheckoutSuccess = true;
        } catch (\Exception $e) {
            $this->featureError = $e->getMessage();
        }
    }

    public function openPromoterCheckout(string $plan = 'monthly'): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->promoterPlan            = $plan;
        $this->promoterError           = '';
        $this->promoterCheckoutSuccess = false;
        $this->promoterCheckoutOpen    = true;
        $this->createPromoterIntent();
    }

    private function createPromoterIntent(): void
    {
        $user = auth()->user();
        $user->createOrGetStripeCustomer();
        $intent = $user->createSetupIntent();
        $this->dispatch('stripe-promoter-intent-ready', clientSecret: $intent->client_secret);
    }

    public function selectPromoterPlan(string $plan): void
    {
        $this->promoterPlan = $plan;
    }

    public function confirmPromoterSubscription(string $paymentMethodId): void
    {
        $this->promoterError = '';

        try {
            $user    = auth()->user();
            $priceId = config("stripe_products.prices.promoter_{$this->promoterPlan}");

            if (! $priceId) {
                $this->promoterError = 'Promoter Pass is not yet configured. Please contact support.';
                return;
            }

            $user->updateDefaultPaymentMethod($paymentMethodId);
            $user->newSubscription('promoter', $priceId)->create($paymentMethodId);

            // Feature all of the user's currently-approved events
            $user->events()->where('status', 'approved')->update(['is_featured' => true]);

            $this->promoterCheckoutSuccess = true;
            $this->promoterCheckoutOpen    = false;
        } catch (\Exception $e) {
            $this->promoterError = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.submit-event')
            ->layout('layouts.app');
    }
}
