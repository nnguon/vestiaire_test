<?php

namespace VestiaireCollective\Configuration;

use Exception;

class Configuration
{
    private static $instance = null;
    private $configData;

    private function __construct() 
    {
        $configPath = __DIR__ . '/../../config/config.ini'; 

        if (!file_exists($configPath)) {
            throw new Exception(message: "Config file not found: " . $configPath);
        }

        $this->configData = parse_ini_file($configPath, true);
        if ($this->configData === false) {
          throw new Exception("Error parsing config file: " . $configPath);
        }
    }

    public static function getInstance() : Configuration
    {
        if (!isset(self::$instance)) {
            self::$instance = new Configuration();
        }

        return self::$instance;
    }

    public function get(string $key) 
    {
        return $this->configData[$key] ?? null;
    }

    // Prevent cloning and serialization (Singleton pattern)
    private function __clone(): void {}
    public function __wakeup(): void {}
}