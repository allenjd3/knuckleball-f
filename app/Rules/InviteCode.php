<?php

namespace App\Rules;

use Closure;
use App\Models\InviteCode as InviteCodeModel;
use Illuminate\Contracts\Validation\ValidationRule;

class InviteCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $codeExists = InviteCodeModel::query()
            ->hasCode(code: $value)
            ->exists();

        if (! $codeExists) {
            $fail('The :attribute must be valid');
        }
    }
}
