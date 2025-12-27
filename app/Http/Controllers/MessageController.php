<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(User $user)
    {
        $authUserId = auth()->id();

        $messages = Message::where(function ($query) use ($authUserId, $user) {
            $query->where('user_id', $authUserId)
                ->where('messageable_type', User::class)
                ->where('messageable_id', $user->id);
        })
            ->orWhere(function ($query) use ($authUserId, $user) {
                $query->where('user_id', $user->id)
                    ->where('messageable_type', User::class)
                    ->where('messageable_id', $authUserId);
            })
            ->with('user')
            ->oldest()
            ->get();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'receiver_id' => ['required', 'exists:users,id'],
        ]);

        $message = Message::create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'messageable_type' => User::class,
            'messageable_id' => $validated['receiver_id'],
        ]);

        return response()->json(
            $message->load('user'),
            201
        );
    }
}
