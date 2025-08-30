<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Traits\ApiResponse;

/**
 * Base controller class for Auth microservice.
 *
 * All controllers extend this base class and inherit the ApiResponse trait
 * for consistent API response formatting.
 */
abstract class Controller
{
    use ApiResponse;
}
