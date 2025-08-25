<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * Class Controller
 *
 * @package App\Http\Controllers
 */
abstract class Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
