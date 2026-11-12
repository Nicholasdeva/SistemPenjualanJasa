<?php

namespace App\Support;

/**
 * Ikon (Bootstrap Icons) dan warna tile untuk setiap kategori jasa.
 * Dipakai di beranda dan kartu jasa agar tampilannya konsisten.
 */
class CategoryStyle
{
    private const MAP = [
        ['keys' => ['desain', 'design', 'grafis'],          'icon' => 'bi-palette',           'tone' => ''],
        ['keys' => ['program', 'web', 'aplikasi', 'kode'],  'icon' => 'bi-code-slash',        'tone' => 'violet'],
        ['keys' => ['tulis', 'konten', 'artikel'],          'icon' => 'bi-pencil',            'tone' => 'amber'],
        ['keys' => ['terjemah', 'bahasa'],                  'icon' => 'bi-translate',         'tone' => 'sky'],
        ['keys' => ['video', 'animasi'],                    'icon' => 'bi-camera-video',      'tone' => 'rose'],
        ['keys' => ['pemasaran', 'marketing', 'iklan'],     'icon' => 'bi-megaphone',         'tone' => 'green'],
        ['keys' => ['data', 'analitik', 'excel'],           'icon' => 'bi-bar-chart',         'tone' => 'sky'],
        ['keys' => ['musik', 'audio', 'suara'],             'icon' => 'bi-music-note-beamed', 'tone' => 'violet'],
    ];

    public static function icon(?string $name): string
    {
        return self::match($name)['icon'] ?? 'bi-briefcase';
    }

    public static function tone(?string $name): string
    {
        return self::match($name)['tone'] ?? '';
    }

    private static function match(?string $name): ?array
    {
        $n = strtolower((string) $name);

        foreach (self::MAP as $row) {
            foreach ($row['keys'] as $key) {
                if (str_contains($n, $key)) {
                    return $row;
                }
            }
        }

        return null;
    }
}
