<?php
/**
 * Matomo Analytics Log Storage Diagnostic
 *
 * Matomo Analytics Log Storage misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1354.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Matomo Analytics Log Storage Diagnostic Class
 *
 * @since 1.1354.0000
 */
class Diagnostic_MatomoAnalyticsLogStorage extends Diagnostic_Base {

	protected static $slug = 'matomo-analytics-log-storage';
	protected static $title = 'Matomo Analytics Log Storage';
	protected static $description = 'Matomo Analytics Log Storage misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MATOMO_ANALYTICS_FILE' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/matomo-analytics-log-storage',
			);
		}
		
		return null;
	}
}
