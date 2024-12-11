<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ValidateSignature as BaseValidator;

class ValidateSignature extends BaseValidator
{
    /**
     * The names of the parameters that should be ignored.
     *
     * @var array<int, string>
     */
    protected $except = [
        // 'fbclid',
        // 'utm_campaign',
        // 'utm_content',
        // 'utm_medium',
        // 'utm_source',
        // 'utm_term',
    ];
} 