<?php

it('renders the login screen with the ivory logo on a red background', function () {
    $this->get(route('login'))
        ->assertStatus(200)
        ->assertSee('images/knuckleball-logo-ivory.png')
        ->assertSee('bg-[#d83c40]', false);
});
