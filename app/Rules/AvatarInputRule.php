<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Http\UploadedFile;

class AvatarInputRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value instanceof UploadedFile) {
            return;
        }

        if (is_string($value)) {
            $isBase64 = preg_match('/^data:[^;]+;base64,/', $value);
            $isUrl = filter_var($value, FILTER_VALIDATE_URL);

            if ($isBase64 || $isUrl) {
                return;
            }
        }

        $fail('The :attribute must be an uploaded image, a base64 string, a valid URL, or null.');
    }
}
