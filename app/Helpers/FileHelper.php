<?php

namespace App\Helpers;

class FileHelper
{
    /**
     * Получить максимальный допустимый размер загрузки (в КБ для Laravel валидации)
     */
    public static function maxUpload(): int
    {
        return (int) floor(self::maxUploadBytes() / 1024);
    }

    /**
     * Получить максимальный допустимый размер загрузки (в байтах)
     */
    private static function maxUploadBytes(): int
    {
        $uploadMax = self::toBytes(ini_get('upload_max_filesize'));
        $postMax = self::toBytes(ini_get('post_max_size'));

        return min($uploadMax, $postMax);
    }



    /**
     * Конвертировать строку из php.ini ("10M", "2G", "512K") в байты
     */
    private static function toBytes(string $val): int
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $num = (float) $val;

        switch ($last) {
            case 'g':
                $num *= 1024;
            case 'm':
                $num *= 1024;
            case 'k':
                $num *= 1024;
        }

        return (int) $num;
    }

    /**
     * Вернем размер base64 строки в байтах
     * @param $base64
     * @return float|int
     */
    public static function getBase64SizeBytes($base64) {
        $len = strlen($base64);
        $padding = substr_count($base64, '=');
        return ($len * 3) / 4 - $padding;
    }
}
