<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Cashier\Cashier;

class SetupStripeProducts extends Command
{
    protected $signature   = 'stripe:setup';
    protected $description = 'Create Stripe products and prices for Knuckleball, then print the price IDs to paste into .env';

    public function handle(): void
    {
        $stripe = Cashier::stripe();

        $this->info('Creating Stripe products and prices...');

        $products = [
            'shop'     => $this->ensureProduct($stripe, 'knuckleball_shop_featured', 'Featured Card Shop'),
            'promoter' => $this->ensureProduct($stripe, 'knuckleball_promoter_pass', 'Knuckleball Promoter Pass'),
        ];

        $prices = [
            'STRIPE_SHOP_MONTHLY_PRICE_ID'     => $this->ensurePrice($stripe, $products['shop'],     999,   'month', 'shop_monthly'),
            'STRIPE_SHOP_YEARLY_PRICE_ID'       => $this->ensurePrice($stripe, $products['shop'],     9900,  'year',  'shop_yearly'),
            'STRIPE_PROMOTER_MONTHLY_PRICE_ID'  => $this->ensurePrice($stripe, $products['promoter'], 2999,  'month', 'promoter_monthly'),
            'STRIPE_PROMOTER_YEARLY_PRICE_ID'   => $this->ensurePrice($stripe, $products['promoter'], 24900, 'year',  'promoter_yearly'),
        ];

        $this->newLine();
        $this->info('✓ Done. Paste these into your .env file:');
        $this->newLine();

        foreach ($prices as $key => $priceId) {
            $this->line("{$key}={$priceId}");
        }

        $this->newLine();
    }

    private function ensureProduct($stripe, string $metadata_key, string $name): string
    {
        $existing = $stripe->products->search(['query' => "metadata['knuckleball_key']:'{$metadata_key}'"]);

        if (! empty($existing->data)) {
            $this->line("  Product exists: {$name}");
            return $existing->data[0]->id;
        }

        $product = $stripe->products->create([
            'name'     => $name,
            'metadata' => ['knuckleball_key' => $metadata_key],
        ]);

        $this->line("  Created product: {$name} ({$product->id})");
        return $product->id;
    }

    private function ensurePrice($stripe, string $productId, int $amountCents, string $interval, string $metaKey): string
    {
        $existing = $stripe->prices->search(['query' => "product:'{$productId}' AND metadata['knuckleball_key']:'{$metaKey}'"]);

        if (! empty($existing->data)) {
            return $existing->data[0]->id;
        }

        $price = $stripe->prices->create([
            'product'    => $productId,
            'currency'   => 'usd',
            'unit_amount' => $amountCents,
            'recurring'  => ['interval' => $interval],
            'metadata'   => ['knuckleball_key' => $metaKey],
        ]);

        return $price->id;
    }
}
