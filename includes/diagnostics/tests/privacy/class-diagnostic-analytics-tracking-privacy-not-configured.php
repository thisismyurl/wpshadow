<?php
/**
 * Analytics Tracking Privacy Not Configured Diagnostic
 *
 * Checks if analytics privacy settings are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Analytics Tracking Privacy Not Configured Diagnostic Class
 *
 * Detects analytics privacy issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Analytics_Tracking_Privacy_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'analytics-tracking-privacy-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Analytics Tracking Privacy Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if analytics privacy is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for analytics plugins
		$analytics_plugins = array(
			'jetpack/jetpack.php',
			'google-analytics-for-wordpress/googleanalytics.php',
			'monsterinsights/monsterinsights.php',
		);

		$analytics_active = false;
		foreach ( $analytics_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$analytics_active = true;
				break;
			}
		}

		if ( ! $analytics_active ) {
			return null; // No analytics tracking
		}

		// Check if privacy disclosure is set
		$privacy_policy_id = get_option( 'wp_page_for_privacy_policy' );

		if ( empty( $privacy_policy_id ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Analytics tracking is active but no privacy policy is set. Disclose analytics in privacy policy for GDPR compliance.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/analytics-tracking-privacy-not-configured',
			);
		}

		return null;
	}
}
