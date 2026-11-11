<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NationalHoliday extends Model
{
    use HasFactory;

    public const CATEGORY_LIBUR_NASIONAL = 'libur_nasional';

    public const CATEGORY_KEAGAMAAN_ISLAM = 'keagamaan_islam';

    public const CATEGORY_KEAGAMAAN_KRISTEN = 'keagamaan_kristen';

    public const CATEGORY_KEAGAMAAN_HINDU = 'keagamaan_hindu';

    public const CATEGORY_KEAGAMAAN_BUDDHA = 'keagamaan_buddha';

    public const CATEGORY_KEAGAMAAN_KONGHUCU = 'keagamaan_konghucu';

    public const CATEGORY_PERINGATAN_NASIONAL = 'peringatan_nasional';

    public const CATEGORY_CUTI_BERSAMA = 'cuti_bersama';

    protected $fillable = [
        'name',
        'date',
        'year',
        'category',
        'color',
        'description',
        'is_holiday',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
        'is_holiday' => 'boolean',
        'year' => 'integer',
    ];

    public static function categories(): array
    {
        return [
            self::CATEGORY_LIBUR_NASIONAL => 'Hari Libur Nasional',
            self::CATEGORY_KEAGAMAAN_ISLAM => 'Hari Besar Keagamaan Islam',
            self::CATEGORY_KEAGAMAAN_KRISTEN => 'Hari Besar Keagamaan Kristen/Katolik',
            self::CATEGORY_KEAGAMAAN_HINDU => 'Hari Besar Keagamaan Hindu',
            self::CATEGORY_KEAGAMAAN_BUDDHA => 'Hari Besar Keagamaan Buddha',
            self::CATEGORY_KEAGAMAAN_KONGHUCU => 'Hari Besar Keagamaan Konghucu',
            self::CATEGORY_PERINGATAN_NASIONAL => 'Hari Peringatan Nasional',
            self::CATEGORY_CUTI_BERSAMA => 'Cuti Bersama',
        ];
    }

    public static function defaultColors(): array
    {
        return [
            self::CATEGORY_LIBUR_NASIONAL => '#dc3545',      // Merah
            self::CATEGORY_KEAGAMAAN_ISLAM => '#198754',     // Hijau
            self::CATEGORY_KEAGAMAAN_KRISTEN => '#6f42c1',   // Ungu
            self::CATEGORY_KEAGAMAAN_HINDU => '#fd7e14',     // Oranye
            self::CATEGORY_KEAGAMAAN_BUDDHA => '#ffc107',    // Kuning
            self::CATEGORY_KEAGAMAAN_KONGHUCU => '#d63384',  // Merah muda
            self::CATEGORY_PERINGATAN_NASIONAL => '#0d6efd', // Biru
            self::CATEGORY_CUTI_BERSAMA => '#f472b6',        // Merah muda pucat
        ];
    }

    public static function defaultColorFor(string $category): string
    {
        return self::defaultColors()[$category] ?? '#dc3545';
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categories()[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function getIsHolidayLabelAttribute(): string
    {
        return $this->is_holiday ? 'Hari Libur Nasional' : 'Bukan Hari Libur (Hari Peringatan)';
    }

    public function getTextColorAttribute(): string
    {
        if (in_array($this->category, [self::CATEGORY_KEAGAMAAN_BUDDHA, self::CATEGORY_CUTI_BERSAMA], true)) {
            return '#1e293b';
        }

        return '#ffffff';
    }
}
