<?php
/**
 * User Consent Tracking Diagnostic
 *
 * Verifies consent tracking is configured for data collection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Consent Tracking Diagnostic
 *
 * Checks if consent tracking is enabled when analytics are active.
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Consent_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-consent-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Consent Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies consent tracking is configured for data collection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );
		$issues = array();
		$analytics_detected = array();

		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php' => 'Google Site Kit',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'jetpack/jetpack.php' => 'Jetpack Stats',
			'wp-statistics/wp-statistics.php' => 'WP Statistics',
		);

		foreach ( $analytics_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$analytics_detected[] = $name;
			}
		}

		$consent_tracking = (bool) get_option( 'wpshadow_consent_tracking_enabled', false );
		$consent_banner = (bool) get_option( 'wpshadow_cookie_banner_enabled', false );

		if ( ! empty( $analytics_detected ) && ! $consent_tracking ) {
			$issues[] = __( 'Analytics detected without consent tracking', 'wpshadow' );
		}

		if ( ! empty( $analytics_detected ) && ! $consent_banner ) {
			$issues[] = __( 'Cookie consent banner not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Consent tracking is not configured for data collection', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-consent-tracking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues'             => $issues,
					'analytics_detected' => $analytics_detected,
					'consent_tracking'   => $consent_tracking,
					'consent_banner'     => $consent_banner,
				),
			);
		}

		return null;
	}
}
