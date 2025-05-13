<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotFakeEmail implements Rule
{
    public function passes($attribute, $value)
    {
        return !is_fake_email($value);
    }

    public function message()
    {
        return 'Email invalid.';
    }
}