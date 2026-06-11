<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $eventId = $this->route('eventId');
            $userId = $this->user()->id;

            // Check event exists
            $event = Event::find($eventId);
            if (!$event) {
                $validator->errors()->add('event_id', 'Event not found.');
                return;
            }

            // Check event has ended
            $eventEndTime = $event->end_time ?? $event->end_date;
            if ($event->status !== 'ended' || ($eventEndTime && Carbon::parse($eventEndTime)->isFuture())) {
                $validator->errors()->add('event_id', 'You can only review an event after it has ended.');
                return;
            }

            // Check user attended event
            $hasAttended = Registration::where('event_id', $eventId)
                ->where('user_id', $userId)
                ->where('status', 'confirmed')
                ->exists();

            if (!$hasAttended) {
                $validator->errors()->add('event_id', 'You have not successfully attended this event, so you cannot review it.');
                return;
            }

            // Check not already reviewed
            $alreadyReviewed = Review::where('event_id', $eventId)
                ->where('user_id', $userId)
                ->exists();

            if ($alreadyReviewed) {
                $validator->errors()->add('event_id', 'You have already reviewed this event.');
            }
        });
    }
}
