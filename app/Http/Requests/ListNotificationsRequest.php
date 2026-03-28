<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Notification listing filters.
 *
 * @queryParam search string Search notifications by title/message/type. Example: password
 * @queryParam status string Filter notifications by read status. Allowed: read, unread. Example: unread
 * @queryParam type string Filter notifications by type. Example: password_changed
 * @queryParam per_page int Number of items per page. Example: 15
 */
class ListNotificationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:read,unread'],
            'type' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Return normalized filter values.
     */
    public function filters(): array
    {
        return array_filter([
            'per_page' => $this->perPage(),
            'search' => $this->search(),
            'status' => $this->status(),
            'type' => $this->type(),
        ], fn ($value) => $value !== null);
    }

    public function perPage(): ?int
    {
        return $this->filled('per_page') ? $this->integer('per_page') : null;
    }

    public function search(): ?string
    {
        return $this->normalize($this->input('search'));
    }

    public function status(): ?string
    {
        return $this->normalize($this->input('status'));
    }

    public function type(): ?string
    {
        return $this->normalize($this->input('type'));
    }

    protected function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
