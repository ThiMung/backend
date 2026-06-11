<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index(int $eventId)
    {
        $reviews = Review::with('user:id,name')
            ->where('event_id', $eventId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ], 200);
    }

    public function store(StoreReviewRequest $request, int $eventId)
    {
        $data = $request->validated();

        $review = Review::create([
            'user_id' => $request->user()->id,
            'event_id' => $eventId,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);

        $review->load(['user' => function($query) {
            $query->select('id', 'name');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for reviewing the event!',
            'data' => $review
        ], 201);
    }
}
