<?php
/**
 * Environment Configuration Loader
 * 
 * Loads settings from .env file for local development
 */

declare(strict_types=1);

class EnvLoader
{
    private static bool $loaded = false;
    
    /**
     * Load .env file if it exists
     */
    public static function load(string $path = '.env'): void
    {
        if (self::$loaded) {
            return;
        }
        
        if (!file_exists($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '\'"');
                
                // Set as environment variable if not already set
                if (!getenv($key)) {
                    putenv("{$key}={$value}");
                }
            }
        }
        
        self::$loaded = true;
    }
}

// Auto-load .env file
EnvLoader::load();
