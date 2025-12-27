<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = auth()->user()->rooms()->with('users')->get();

        return Inertia::render('Rooms/Index', [
            'rooms' => $rooms,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['exists:users,id'],
        ]);

        $avatarPath = null;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $room = Room::create([
            'name' => $validated['name'],
            'avatar' => $avatarPath,
            'created_by' => auth()->id(),
        ]);

        $room->users()->attach(auth()->id());

        if (isset($validated['member_ids'])) {
            $room->users()->attach($validated['member_ids']);
        }

        return redirect()->route('rooms.index');
    }

    public function edit(Room $room)
    {
        $memberIds = $room->users->pluck('id');
        $availableUsers = User::whereNotIn('id', $memberIds)->get();

        $room->load('users');

        return Inertia::render('Rooms/Edit', [
            'room' => $room,
            'availableUsers' => $availableUsers,
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($room->avatar) {
                Storage::disk('public')->delete($room->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $room->avatar = $avatarPath;
        }

        $room->name = $validated['name'];
        $room->save();

        return redirect()->route('rooms.index');
    }

    public function destroy(Room $room)
    {
        if ($room->avatar) {
            Storage::disk('public')->delete($room->avatar);
        }

        $room->delete();

        return redirect()->route('rooms.index');
    }

    public function addMember(Room $room, User $user)
    {
        if ($room->users->contains($user->id)) {
            return back();
        }

        $room->users()->attach($user->id);

        return back();
    }

    public function removeMember(Room $room, User $user)
    {
        $room->users()->detach($user->id);

        return back();
    }

    public function getMessages(Room $room)
    {
        $messages = $room->messages()->with('user')->oldest()->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Room $room)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $message = $room->messages()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'messageable_type' => Room::class,
            'messageable_id' => $room->id,
        ]);

        return response()->json($message->load('user'), 201);
    }
}
