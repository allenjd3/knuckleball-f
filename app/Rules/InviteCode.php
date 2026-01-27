<?php

namespace App\Rules;

use Illuminate\Translation\PotentiallyTranslatedString;
use App\Models\InviteCode as InviteCodeModel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InviteCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=):PotentiallyTranslatedString $fail
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
