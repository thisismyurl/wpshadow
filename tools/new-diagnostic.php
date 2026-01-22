#!/usr/bin/php
<?php
/**
 * New Diagnostic Scaffolder
 *
 * Usage:
 *   php tools/new-diagnostic.php --slug="seo-missing-h1" --name="SEO: Missing H1 Tag" --category=seo
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

$options = getopt('', ['slug:', 'name:', 'category::']);
$slug = $options['slug'] ?? '';
$name = $options['name'] ?? '';
$category = $options['category'] ?? 'general';

if ($slug === '' || $name === '') {
    fwrite(STDERR, "Usage: php tools/new-diagnostic.php --slug=slug --name=\"Name\" [--category=seo]\n");
    exit(1);
}

$root = dirname(__DIR__);
$diagnosticsDir = $root . '/includes/diagnostics';

$slug_safe = preg_replace('/[^a-z0-9\-]/', '-', strtolower($slug));
$slug_safe = trim(preg_replace('/-+/', '-', $slug_safe), '-');

$class_slug = preg_replace('/(^|\-)([a-z])/', function ($m) { return strtoupper($m[2]); }, $slug_safe);
$className = 'Diagnostic_' . $class_slug;

$filePath = $diagnosticsDir . '/class-diagnostic-' . $slug_safe . '.php';
if (file_exists($filePath)) {
    fwrite(STDERR, "File already exists: $filePath\n");
    exit(1);
}

$contents = <<<PHP
<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Diagnostic: {$name}
 *
 * Philosophy: Add 1-2 lines summarizing helpful-neighbor value.
 * KB Link: https://wpshadow.com/kb/{$slug_safe}
 * Training: https://wpshadow.com/training/{$slug_safe}
 */
class {$className} extends Diagnostic_Base {
    public static function get_slug(): string { return '{$slug_safe}'; }
    public static function get_name(): string { return __('{$name}', 'wpshadow'); }
    public static function get_category(): string { return '{$category}'; }

    public static function get_description(): string {
        return sprintf(
            __('Brief explanation with <a href="%s" target="_blank">KB link</a>.', 'wpshadow'),
            'https://wpshadow.com/kb/{$slug_safe}'
        );
    }

    /**
     * @return array Findings
     */
    public static function run(): array {
        // TODO: Implement diagnostic logic
        KPI_Tracker::record_diagnostic_run(self::get_slug(), true);
        return [];
    }
}
PHP;

if (!is_dir($diagnosticsDir)) {
    @mkdir($diagnosticsDir, 0775, true);
}

file_put_contents($filePath, $contents);

fwrite(STDOUT, "Created: $filePath\nClass: $className\n");
