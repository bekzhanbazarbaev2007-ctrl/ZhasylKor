<?php

test('homepage redirects to the GreenFund dashboard', function () {
    $this->get(route('home'))->assertRedirect('/greenfund/dashboard');
    $this->get('/greenfund/dashboard')->assertOk();
});
