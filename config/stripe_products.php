<?php

return [

    // Stripe Price IDs for recurring subscriptions (set in .env)
    'prices' => [
        'shop_monthly'      => env('STRIPE_SHOP_MONTHLY_PRICE_ID'),
        'shop_yearly'       => env('STRIPE_SHOP_YEARLY_PRICE_ID'),
        'promoter_monthly'  => env('STRIPE_PROMOTER_MONTHLY_PRICE_ID'),
        'promoter_yearly'   => env('STRIPE_PROMOTER_YEARLY_PRICE_ID'),
    ],

    // One-time amounts in cents (no price ID needed)
    'amounts' => [
        'featured_signing'   =>  999,  // $9.99
        'featured_card_show' => 1999,  // $19.99
        'featured_comic_con' => 1999,  // $19.99
        'featured_memorabilia' => 1999, // $19.99
        'shop_monthly'       =>  999,  // $9.99
        'shop_yearly'        => 9900,  // $99.00
        'promoter_monthly'   => 2999,  // $29.99
        'promoter_yearly'    => 24900, // $249.00
    ],

];
