<?php

/**
 * WPShadow System Diagnostic Test: wp-cron.php Performance Impact
 *
 * Flags when wp-cron runs inline on every page load instead of via real cron.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2301
 * @category    System
 * @philosophy  #9 Show Value - recommends offloading cron for faster responses
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

class Test_Wp_Cron_Php_Performance_Impact extends Diagnostic_Base
{
	protected static $slug = 'wp-cron-php-performance-impact';
	protected static $title = 'wp-cron.php Performance Impact';
	protected static $description = 'Checks if wp-cron runs inline on every page load instead of via system cron.';

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		$inline_cron = ! defined('DISABLE_WP_CRON') || ! DISABLE_WP_CRON;
		$alternate_cron = defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON;

		if ($inline_cron && ! $alternate_cron) {
			return array(
				'id'           => static::$slug,
				'title'        => static::$title,
				'description'  => __('wp-cron.php is executing on every page load. Disable inline cron and trigger it via a real cron job for faster responses.', 'wpshadow'),
				'kb_link'      => 'https://wpshadow.com/kb/disable-wp-cron/',
				'training_link' => 'https://wpshadow.com/training/wp-cron-optimization/',
				'category'     => 'system',
				'severity'     => 'medium',
				'auto_fixable' => false,
				'threat_level' => 45,
				'priority'     => 11,
				'module'       => 'system',
				'meta'         => array(
					'inline_cron'    => $inline_cron,
					'alternate_cron' => $alternate_cron,
				),
			);
		}

		return null;
	}

	public static function get_info(): array
	{
		return array(
			'name'        => 'wp-cron.php Performance Impact',
			'category'    => 'system',
			'priority'    => 11,
			'severity'    => 'medium',
			'description' => 'Detects inline wp-cron execution and recommends real cron scheduling.',
		);
	}
}
