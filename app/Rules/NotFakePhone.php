<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotFakePhone implements Rule
{
    public function passes($attribute, $value)
    {
        return !is_fake_phone($value);
    }

    public function message()
    {
        return 'Phone number invalid.';
    }
}
