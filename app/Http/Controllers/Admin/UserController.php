<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,pic,guru,organisasi,siswa',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated)->forceFill([
            'email_verified_at' => now(),
            'is_active' => true,
        ])->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => "required|email|unique:users,email,{$user->id}",
            'role' => 'required|in:admin,pic,guru,organisasi,siswa',
            'phone' => 'nullable|string|max:20',
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan untuk login.');
        }

        $isPic = $user->isPic();

        $isDeleted = DB::transaction(function () use ($user, $isPic): bool {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($isPic && $lockedUser->hasActiveRoomResponsibility()) {
                return false;
            }

            $lockedUser->delete();

            return true;
        });

        if (! $isDeleted) {
            return back()->with('error', 'Tidak dapat menghapus pengguna ini karena masih bertanggung jawab atas satu atau lebih ruangan. Silakan pindahkan atau hapus penugasan PIC Ruang terlebih dahulu.');
        }

        return redirect()->route('admin.users.index')
            ->with('success', $isPic ? 'Pengguna PIC Ruang berhasil dihapus.' : 'User berhasil dihapus!');
    }
}
