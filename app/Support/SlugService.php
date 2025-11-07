<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SlugService
{
    /**
     * Generate a unique slug for the given table/column combination.
     */
    public static function generate(
        string $value,
        string $table,
        string $column = 'slug',
        ?int $ignoreId = null,
        int $maxLength = 120
    ): string {
        $base = Str::slug(Str::limit($value, $maxLength, ''));

        if ($base === '') {
            $base = Str::random(6);
        }

        $slug = $base;
        $counter = 1;

        while (self::exists($table, $column, $slug, $ignoreId)) {
            $suffix = '-' . $counter;
            $slug = Str::limit($base, $maxLength - strlen($suffix), '') . $suffix;
            $counter++;
        }

        return $slug;
    }

    protected static function exists(string $table, string $column, string $slug, ?int $ignoreId = null): bool
    {
        return DB::table($table)
            ->when($ignoreId, function ($query, $id) {
                $query->where('id', '!=', $id);
            })
            ->where($column, $slug)
            ->exists();
    }
}
