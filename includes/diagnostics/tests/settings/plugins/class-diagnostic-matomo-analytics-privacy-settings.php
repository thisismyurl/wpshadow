<?php
/**
 * Matomo Analytics Privacy Settings Diagnostic
 *
 * Matomo Analytics Privacy Settings misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1353.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Matomo Analytics Privacy Settings Diagnostic Class
 *
 * @since 1.1353.0000
 */
class Diagnostic_MatomoAnalyticsPrivacySettings extends Diagnostic_Base {

	protected static $slug = 'matomo-analytics-privacy-settings';
	protected static $title = 'Matomo Analytics Privacy Settings';
	protected static $description = 'Matomo Analytics Privacy Settings misconfigured';
	protected static $family = 'security';

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
				'severity'    => 65,
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/matomo-analytics-privacy-settings',
			);
		}
		
		return null;
	}
}
