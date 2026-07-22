<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = ['key', 'value', 'serialize'];

    /* ---------------- writer ---------------- */
    public static function setValue(array $rows): void
    {
        $now = now();
        $payload = [];

        foreach ($rows as $k => $v) {
            $needSer = is_array($v);
            $payload[] = [
                'key' => $k,
                'value' => $needSer ? json_encode($v) : $v,
                'serialize' => $needSer,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        static::upsert($payload, ['key'], ['value', 'serialize', 'updated_at']);
    }

    /* ---------------- reader ---------------- */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $row = static::find($key);
        if (! $row) {
            return $default;
        }

        return $row->serialize ? json_decode($row->value, true) : $row->value;
    }

    /* ---------------- delete old file ---------------- */
    public static function deleteOldFile(string $key): void
    {
        $old = static::getValue($key);
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
    }
}
