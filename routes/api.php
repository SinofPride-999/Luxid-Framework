<?php
// Application Routes

use App\Actions\WelcomeAction;
use Luxid\Foundation\Application;

route('welcome')
    ->get('/')
    ->uses(WelcomeAction::class, 'index')
    ->open();
