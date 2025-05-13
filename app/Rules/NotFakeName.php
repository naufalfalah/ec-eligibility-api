<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotFakeName implements Rule
{
    public function passes($attribute, $value)
    {
        return !is_fake_name($value);
    }

    public function message()
    {
        return 'Name invalid.';
    }
}