<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NationalHoliday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class NationalHolidayController extends Controller
{
    public function index(Request $request): View
    {
        $yearFilter = $request->input('year');
        $categoryFilter = $request->input('category');
        $search = $request->input('search');

        $query = NationalHoliday::query();

        if ($yearFilter) {
            $query->where('year', $yearFilter);
        }

        if ($categoryFilter) {
            $query->where('category', $categoryFilter);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $holidays = $query->orderBy('date', 'asc')->paginate(15)->withQueryString();
        $years = NationalHoliday::distinct()->pluck('year')->sort()->values();
        $categories = NationalHoliday::categories();

        return view('admin.national_holidays.index', compact('holidays', 'years', 'categories', 'yearFilter', 'categoryFilter', 'search'));
    }

    public function create(): View
    {
        $categories = NationalHoliday::categories();
        $defaultColors = NationalHoliday::defaultColors();

        return view('admin.national_holidays.create', compact('categories', 'defaultColors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'date' => 'required|date',
            'category' => 'required|string|in:'.implode(',', array_keys(NationalHoliday::categories())),
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'is_holiday' => 'sometimes|boolean',
            'source' => 'nullable|string|max:150',
        ]);

        $validated['year'] = (int) date('Y', strtotime($validated['date']));
        $validated['is_holiday'] = $request->boolean('is_holiday');
        if (empty($validated['color'])) {
            $validated['color'] = NationalHoliday::defaultColorFor($validated['category']);
        }

        // Prevent duplicate date & name
        $existing = NationalHoliday::where('date', $validated['date'])
            ->where('name', $validated['name'])
            ->first();

        if ($existing) {
            return back()->withInput()->with('error', 'Hari penting dengan nama dan tanggal yang sama sudah tersimpan.');
        }

        NationalHoliday::create($validated);

        return redirect()->route('admin.national-holidays.index')
            ->with('success', 'Hari penting/libur nasional berhasil ditambahkan.');
    }

    public function edit(NationalHoliday $nationalHoliday): View
    {
        $categories = NationalHoliday::categories();
        $defaultColors = NationalHoliday::defaultColors();

        return view('admin.national_holidays.edit', compact('nationalHoliday', 'categories', 'defaultColors'));
    }

    public function update(Request $request, NationalHoliday $nationalHoliday): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'date' => 'required|date',
            'category' => 'required|string|in:'.implode(',', array_keys(NationalHoliday::categories())),
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'is_holiday' => 'sometimes|boolean',
            'source' => 'nullable|string|max:150',
        ]);

        $validated['year'] = (int) date('Y', strtotime($validated['date']));
        $validated['is_holiday'] = $request->boolean('is_holiday');
        if (empty($validated['color'])) {
            $validated['color'] = NationalHoliday::defaultColorFor($validated['category']);
        }

        $duplicate = NationalHoliday::where('date', $validated['date'])
            ->where('name', $validated['name'])
            ->where('id', '!=', $nationalHoliday->id)
            ->first();

        if ($duplicate) {
            return back()->withInput()->with('error', 'Hari penting dengan nama dan tanggal yang sama sudah ada.');
        }

        $nationalHoliday->update($validated);

        return redirect()->route('admin.national-holidays.index')
            ->with('success', 'Hari penting berhasil diperbarui.');
    }

    public function destroy(NationalHoliday $nationalHoliday): RedirectResponse
    {
        $nationalHoliday->delete();

        return redirect()->route('admin.national-holidays.index')
            ->with('success', 'Hari penting berhasil dihapus.');
    }

    public function syncDefault(): RedirectResponse
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'NationalHolidaySeeder',
                '--force' => true,
            ]);

            return redirect()->route('admin.national-holidays.index')
                ->with('success', 'Data resmi hari libur nasional & peringatan berhasil disinkronisasi.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.national-holidays.index')
                ->with('error', 'Gagal menyinkronkan data: '.$e->getMessage());
        }
    }
}

