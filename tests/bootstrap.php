<?php
/**
 * PHPUnit Bootstrap File
 *
 * @package WPShadow
 * @subpackage Tests
 */

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define WordPress constants that diagnostics may use
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

if (!defined('WPSHADOW_PATH')) {
    define('WPSHADOW_PATH', dirname(__DIR__) . '/');
}

// Bootstrap WP_Mock
WP_Mock::bootstrap();

// Load core classes needed by diagnostics
require_once dirname(__DIR__) . '/includes/core/class-diagnostic-base.php';
require_once dirname(__DIR__) . '/includes/core/class-diagnostic-lean-checks.php';

// Autoload diagnostic classes
spl_autoload_register(function ($class) {
    // Only autoload WPShadow\Diagnostics classes
    if (strpos($class, 'WPShadow\\Diagnostics\\') !== 0) {
        return;
    }

    // Convert class name to file path
    $class_name = str_replace('WPShadow\\Diagnostics\\', '', $class);
    
    // Convert CamelCase and underscores to kebab-case for file naming
    $file_name = 'class-' . strtolower(str_replace('_', '-', preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_name))) . '.php';
    
    // Search in all diagnostic subdirectories
    $base_dir = dirname(__DIR__) . '/includes/diagnostics';
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $file_name) {
            require_once $file->getPathname();
            return;
        }
    }
});
