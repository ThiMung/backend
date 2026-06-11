<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    private const EVENT_CATEGORIES = [
        'Music',
        'Sports',
        'Food & Drink',
        'Arts',
        'Education',
        'Community',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(self::EVENT_CATEGORIES)],
            'start_time' => ['required', 'date', 'after:now'],
            'capacity' => ['required', 'integer', 'min:1'],
            'image_url' => ['nullable', 'url'],
        ];
    }
}
