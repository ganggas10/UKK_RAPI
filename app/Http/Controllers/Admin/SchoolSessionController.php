<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolSessionController extends Controller
{
    /**
     * Tampilkan seluruh daftar sesi yang tersedia
     */
    public function index(): View
    {
        $sessions = SchoolSession::orderBy('sort_order')->orderBy('start_time')->get();

        $stats = [
            'total' => $sessions->count(),
            'active' => $sessions->where('is_active', true)->count(),
            'inactive' => $sessions->where('is_active', false)->count(),
        ];

        return view('admin.sessions.index', compact('sessions', 'stats'));
    }

    /**
     * Simpan sesi baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:school_sessions,name'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Nama sesi wajib diisi.',
            'name.unique' => 'Nama sesi sudah digunakan.',
            'start_time.required' => 'Jam mulai sesi wajib diisi.',
            'end_time.required' => 'Jam selesai sesi wajib diisi.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? (SchoolSession::max('sort_order') + 1);

        SchoolSession::create($validated);

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Sesi baru berhasil ditambahkan.');
    }

    /**
     * Update data sesi (nama, jam mulai, jam selesai, status)
     */
    public function update(Request $request, SchoolSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('school_sessions', 'name')->ignore($session->id)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Nama sesi wajib diisi.',
            'name.unique' => 'Nama sesi sudah digunakan.',
            'start_time.required' => 'Jam mulai sesi wajib diisi.',
            'end_time.required' => 'Jam selesai sesi wajib diisi.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? $session->sort_order;

        $session->update($validated);

        return redirect()->route('admin.sessions.index')
            ->with('success', "Pengaturan sesi {$session->name} berhasil diperbarui.");
    }

    /**
     * Toggle aktif / nonaktifkan sesi
     */
    public function toggle(SchoolSession $session): RedirectResponse
    {
        $session->update([
            'is_active' => ! $session->is_active,
        ]);

        $statusText = $session->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.sessions.index')
            ->with('success', "Sesi {$session->name} berhasil {$statusText}.");
    }

    /**
     * Hapus sesi jika belum digunakan pada peminjaman aktif
     */
    public function destroy(SchoolSession $session): RedirectResponse
    {
        if ($session->isUsedInActiveEvents()) {
            return back()->with('error', "Tidak dapat menghapus {$session->name} karena masih terhubung dengan data peminjaman yang aktif.");
        }

        $sessionName = $session->name;
        $session->delete();

        return redirect()->route('admin.sessions.index')
            ->with('success', "Sesi {$sessionName} berhasil dihapus.");
    }
}
