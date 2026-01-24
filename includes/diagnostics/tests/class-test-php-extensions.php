<?php

/**
 * WPShadow System Diagnostic Test: PHP Extensions
 *
 * Tests if critical PHP extensions are loaded and available.
 * Covers: gzip, imagick, finfo, opcache, curl, json, mbstring
 *
 * Testable via: extension_loaded()
 * Can be requested by Guardian: "test-php-extensions", "test-php-extension-gzip", etc.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #7 Ridiculously Good - Ensure performance extensions available
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: PHP Extensions
 *
 * Main diagnostic that checks for critical PHP extensions.
 * Can request specific extension tests via Guardian.
 *
 * @verified Not yet tested
 */
class Test_PHP_Extensions extends Diagnostic_Base
{

	protected static $slug = 'php-extensions';
	protected static $title = 'PHP Extensions Status';
	protected static $description = 'Checks for required and recommended PHP extensions.';

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		$missing_critical = array();
		$missing_recommended = array();

		// Critical extensions (WordPress won't function without these)
		$critical = array('json', 'mbstring', 'curl');
		foreach ($critical as $ext) {
			if (! extension_loaded($ext)) {
				$missing_critical[] = $ext;
			}
		}

		if (! empty($missing_critical)) {
			return array(
				'id'            => static::$slug . '-critical',
				'title'         => 'Critical PHP Extensions Missing',
				'description'   => 'WordPress requires: ' . implode(', ', $missing_critical) . '. Contact your hosting provider.'
				'kb_link'       => 'https://wpshadow.com/kb/php-extensions/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=php-extensions',
				'training_link' => 'https://wpshadow.com/training/php-extensions/',
				'auto_fixable'  => false,
				'threat_level'  => 95,
				'module'        => 'System',
				'priority'      => 1,
				'meta'          => array(
					'missing' => $missing_critical,
				),
			);
		}

		// Recommended extensions (performance/functionality)
		$recommended = array('gzip' => 'zlib', 'imagick' => 'imagick', 'opcache' => 'opcache');
		foreach ($recommended as $name => $ext) {
			if (! extension_loaded($ext)) {
				$missing_recommended[] = array('name' => $name, 'ext' => $ext);
			}
		}

		if (! empty($missing_recommended)) {
			$ext_names = array_map(fn($x) => $x['name'], $missing_recommended);
			return array(
				'id'            => static::$slug . '-recommended',
				'title'         => 'Recommended PHP Extensions Not Installed',
				'description'   => 'Missing: ' . implode(', ', $ext_names) . '. These improve performance. Ask your host to install them.'
				'kb_link'       => 'https://wpshadow.com/kb/php-extensions/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=php-extensions',
				'training_link' => 'https://wpshadow.com/training/php-extensions/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
				'module'        => 'System',
				'priority'      => 2,
				'meta'          => array(
					'missing' => $ext_names,
					'impact' => array(
						'gzip' => 'Responses 70-80% larger without compression',
						'imagick' => 'Cannot optimize images, slower thumbnails',
						'opcache' => 'PHP code not cached, 2-3x slower execution',
					),
				),
			);
		}

		return null;
	}

	/**
	 * Guardian can request: "test-php-extensions-critical"
	 * Checks: json, mbstring, curl
	 */
	public static function test_php_extensions_critical(): array
	{
		$critical = array('json', 'mbstring', 'curl');
		$missing = array();

		foreach ($critical as $ext) {
			if (! extension_loaded($ext)) {
				$missing[] = $ext;
			}
		}

		$passed = empty($missing);

		return array(
			'passed'  => $passed,
			'message' => $passed
				? "✓ All critical extensions are loaded"
				: "✗ Missing critical extensions: " . implode(', ', $missing),
			'data'    => array(
				'extensions_checked' => $critical,
				'missing' => $missing,
				'loaded' => array_diff($critical, $missing),
			),
		);
	}

	/**
	 * Guardian can request: "test-php-extension-gzip"
	 * Checks: zlib extension
	 */
	public static function test_php_extension_gzip(): array
	{
		$has_gzip = extension_loaded('zlib');

		return array(
			'passed'  => $has_gzip,
			'message' => $has_gzip
				? "✓ Gzip (zlib) extension is loaded - responses will be compressed"
				: "✗ Gzip (zlib) extension not available - responses will be 70-80% larger",
			'data'    => array(
				'extension' => 'zlib',
				'loaded' => $has_gzip,
				'performance_impact' => $has_gzip ? 'Minimal (transparent)' : 'Significant (bloat)',
			),
		);
	}

	/**
	 * Guardian can request: "test-php-extension-imagick"
	 * Checks: ImageMagick extension
	 */
	public static function test_php_extension_imagick(): array
	{
		$has_imagick = extension_loaded('imagick');

		return array(
			'passed'  => $has_imagick,
			'message' => $has_imagick
				? "✓ ImageMagick extension is loaded - images can be optimized"
				: "⚠ ImageMagick extension not available - image optimization will be slower",
			'data'    => array(
				'extension' => 'imagick',
				'loaded' => $has_imagick,
				'fallback' => 'GD library (slower)',
			),
		);
	}

	/**
	 * Guardian can request: "test-php-extension-opcache"
	 * Checks: Opcache extension
	 */
	public static function test_php_extension_opcache(): array
	{
		$has_opcache = extension_loaded('opcache');

		return array(
			'passed'  => $has_opcache,
			'message' => $has_opcache
				? "✓ Opcache extension is loaded - PHP code will be cached and compiled"
				: "✗ Opcache extension not loaded - every request will recompile PHP (2-3x slower)",
			'data'    => array(
				'extension' => 'opcache',
				'loaded' => $has_opcache,
				'performance_gain' => $has_opcache ? '50-80% faster execution' : '0%',
			),
		);
	}

	/**
	 * Guardian can request: "test-php-extension-finfo"
	 * Checks: Finfo (file type detection)
	 */
	public static function test_php_extension_finfo(): array
	{
		$has_finfo = function_exists('finfo_open');

		return array(
			'passed'  => $has_finfo,
			'message' => $has_finfo
				? "✓ Finfo extension available - accurate MIME type detection for uploads"
				: "⚠ Finfo not available - file upload validation will be less secure",
			'data'    => array(
				'function' => 'finfo_open',
				'available' => $has_finfo,
				'security_impact' => $has_finfo ? 'Strong' : 'Weak',
			),
		);
	}

	/**
	 * Guardian can request: "test-php-extensions-all"
	 * Returns complete list of all loaded extensions
	 */
	public static function test_php_extensions_all(): array
	{
		$all = get_loaded_extensions();
		sort($all);

		$critical = array('json', 'mbstring', 'curl');
		$recommended = array('zlib', 'imagick', 'opcache', 'openssl');

		$loaded_critical = array_intersect($critical, $all);
		$loaded_recommended = array_intersect($recommended, $all);

		return array(
			'passed'  => count($loaded_critical) === count($critical),
			'message' => "Total extensions loaded: " . count($all),
			'data'    => array(
				'total_extensions' => count($all),
				'all_extensions' => $all,
				'critical_loaded' => $loaded_critical,
				'recommended_loaded' => $loaded_recommended,
			),
		);
	}
}
