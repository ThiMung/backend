<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventStatusRequest extends FormRequest
{
    private const ALLOWED_STATUS_TRANSITIONS = [
        'draft' => ['published', 'cancelled'],
        'published' => ['cancelled', 'ended'],
        'cancelled' => [],
        'ended' => [],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['published', 'cancelled', 'ended'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $event = $this->route('event');
            $currentStatus = $event->status;
            $nextStatus = $this->input('status');

            $allowedTransitions = self::ALLOWED_STATUS_TRANSITIONS[$currentStatus] ?? [];

            if (!in_array($nextStatus, $allowedTransitions, true)) {
                $validator->errors()->add(
                    'status',
                    'Cannot transition event status to the requested state.'
                );
            }
        });
    }
}
