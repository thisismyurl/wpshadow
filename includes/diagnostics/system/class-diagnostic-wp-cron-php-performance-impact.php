<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: wp-cron.php Performance Impact (CORE-005)
 * 
 * Checks if wp-cron runs on every page load.
 * Philosophy: Show value (#9) with external cron benefits.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Wp_Cron_Php_Performance_Impact extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$inline_cron = !defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON;
		$alternate_cron = defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON;

		if ($inline_cron && !$alternate_cron) {
			return array(
				'id' => 'wp-cron-php-performance-impact',
				'title' => __('wp-cron.php runs on every page load', 'wpshadow'),
				'description' => __('Inline wp-cron can slow page responses. Disable wp-cron and trigger it with a real cron job or hosting scheduler.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/disable-wp-cron/',
				'training_link' => 'https://wpshadow.com/training/wp-cron-optimization/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}

		return null;
	}
}
