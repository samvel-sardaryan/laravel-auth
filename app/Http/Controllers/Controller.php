<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract readonly class Controller
{
    use AuthorizesRequests;
}
