<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function __construct(
        private RegistrationService $registrationService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $registration = $this->registrationService->registerUser(
            Auth::id(),
            $data['event_id']
        );

        return response()->json([
            'message' => $registration->status === 'confirmed'
                ? 'Registration confirmed.'
                : 'Event is full. You have been added to the waitlist.',
            'registration' => $registration,
        ], 201);
    }

    public function myRegistrations(): JsonResponse
    {
        $registrations = Registration::query()
            ->with('event')
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->get();

        return response()->json($registrations);
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->registrationService->cancelRegistration(Auth::id(), $event->id);

        return response()->json(['message' => 'Registration cancelled successfully.']);
    }
}
