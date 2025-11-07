<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $fillable = [
        'config_key',
        'config_value',
        'value_type',
        'description',
    ];

    protected $casts = [
        'value_type' => 'string',
    ];

    public static function get($key, $default = null)
    {
        $config = self::where('config_key', $key)->first();
        
        if (!$config) {
            return $default;
        }

        return match ($config->value_type) {
            'json' => json_decode($config->config_value, true),
            'integer' => (int) $config->config_value,
            'boolean' => (bool) $config->config_value,
            default => $config->config_value,
        };
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
        $valueToStore = $value;
        if ($type === 'json') {
            $valueToStore = json_encode($value);
        } elseif ($type === 'boolean') {
            $valueToStore = $value ? '1' : '0';
        }

        return self::updateOrCreate(
            ['config_key' => $key],
            [
                'config_value' => $valueToStore,
                'value_type' => $type,
                'description' => $description,
            ]
        );
    }
}
