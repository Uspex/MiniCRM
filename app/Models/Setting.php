<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    const TYPE_SHIFTS = 'shifts';
    const TYPE_DEPARTMENTS = 'departments';

    protected $fillable = ['type', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Список всех типов настроек для index
     */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_SHIFTS,
            self::TYPE_DEPARTMENTS,
        ];
    }

    /**
     * Получить значение настройки по типу
     */
    public static function get(string $type, mixed $default = null): mixed
    {
        $setting = self::where('type', $type)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Установить значение настройки
     */
    public static function set(string $type, mixed $value): self
    {
        return self::updateOrCreate(
            ['type' => $type],
            ['value' => $value]
        );
    }
}
