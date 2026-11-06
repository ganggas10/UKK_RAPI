<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('pic')->paginate(10);

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:150',
            'capacity' => 'required|integer|min:1',
        ]);

        Room::create($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil ditambahkan!');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:150',
            'capacity' => 'required|integer|min:1',
        ]);

        $room->update($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil diperbarui!');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil dihapus!');
    }
}
