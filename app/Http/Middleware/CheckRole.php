<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Middleware này tách quyền truy cập giữa Organizer và Attendee.
        if (!$request->user() || $request->user()->role !== $role) {
            return response()->json(['message' => 'Trái quyền truy cập (Forbidden)'], 403);
        }

        return $next($request);
    }
}
