<?php

namespace App;

use Symfony\Component\Yaml\Yaml;

class Config
{
    private static $config = null;

    public static function load()
    {
        if (self::$config === null) {
            $configPath = __DIR__ . '/../tempmail.yml';
            if (!file_exists($configPath)) {
                throw new \Exception("Configuration file tempmail.yml not found.");
            }
            self::$config = Yaml::parseFile($configPath);
        }
        return self::$config;
    }

    public static function get($key, $default = null)
    {
        $config = self::load();
        return $config[$key] ?? $default;
    }

    public static function getAllDomains()
    {
        $domains = [];
        $accounts = self::get('accounts', []);
        foreach ($accounts as $account) {
            if (isset($account['domains']) && is_array($account['domains'])) {
                $domains = array_merge($domains, $account['domains']);
            }
        }
        return array_values(array_unique($domains));
    }
}
