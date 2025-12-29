<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Room;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    Route::get('/chat', function () {
        return Inertia::render('Chat/Index', ['users' => User::where('id', '!=', auth()->id())->get()]);
    })->name('chat');

    Route::get('/messages/{user}', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/create', function () {
        return Inertia::render('Rooms/Create', [
            'users' => User::where('id', '!=', auth()->id())->get(),
        ]);
    })->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    Route::get('/rooms/{room}/messages', [RoomController::class, 'getMessages']);
    Route::post('/rooms/{room}/messages', [RoomController::class, 'sendMessage']);

    Route::post('/rooms/{room}/members/{user}', [RoomController::class, 'addMember'])->name('rooms.members.add');
    Route::delete('/rooms/{room}/members/{user}', [RoomController::class, 'removeMember'])->name('rooms.members.remove');

    Route::get('/rooms/{room}/available-users', [RoomController::class, 'availableUsers']);

    Route::get('/salas/convite/{token}', function ($token) {
        $room = Room::where('invite_token', $token)->firstOrFail();

        if (! $room->users->contains(auth()->id())) {
            $room->users()->attach(auth()->id());
        }

        return redirect()->route('rooms.index')->with('success', 'Entraste na sala '.$room->name);
    })->name('rooms.invite.accept');
});

require __DIR__.'/auth.php';
