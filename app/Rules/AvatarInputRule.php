<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Http\UploadedFile;

class AvatarInputRule implements ValidationRule
{
    private int $maxSizeBytes;

    public function __construct(?int $maxSizeKb = null)
    {
        $this->maxSizeBytes = ($maxSizeKb ?? config('uploads.avatar_max_size_kb')) * 1024;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value instanceof UploadedFile) {
            if ($value->getSize() > $this->maxSizeBytes) {
                $fail($this->sizeErrorMessage($attribute));
            }

            return;
        }

        if (is_string($value)) {
            $isBase64 = preg_match('/^data:[^;]+;base64,/', $value);
            $isUrl = filter_var($value, FILTER_VALIDATE_URL);

            if ($isBase64) {
                if ($this->estimatedBase64Size($value) > $this->maxSizeBytes) {
                    $fail($this->sizeErrorMessage($attribute));
                }

                return;
            }

            if ($isUrl) {
                return;
            }
        }

        $fail('The :attribute must be an uploaded image, a base64 string, a valid URL, or null.');
    }

    /**
     * Estimate the decoded byte size of a base64 data URI without fully
     * decoding it, so an oversized payload is rejected cheaply.
     */
    private function estimatedBase64Size(string $value): int
    {
        $payload = preg_replace('/^data:[^;]+;base64,/', '', $value);
        $payload = rtrim($payload, '=');

        return (int) floor(strlen($payload) * 3 / 4);
    }

    private function sizeErrorMessage(string $attribute): string
    {
        $maxSizeMb = round($this->maxSizeBytes / (1024 * 1024), 1);

        return "The {$attribute} must not be larger than {$maxSizeMb}MB.";
    }
}
