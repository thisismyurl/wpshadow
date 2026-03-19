<?php
/**
 * Do Not Track Diagnostic
 *
 * Checks whether analytics respect the browser's Do Not Track setting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Do Not Track Diagnostic Class
 *
 * Verifies that analytics tooling respects DNT preferences.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Do_Not_Track_Respect extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'do-not-track-respect';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Analytics Don\'t Respect Do Not Track';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if analytics respect browser Do Not Track';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-analytics-premium/googleanalytics-premium.php' => 'MonsterInsights Pro',
			'ga-google-analytics/ga-google-analytics.php' => 'GA Google Analytics',
			'woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php' => 'WooCommerce Google Analytics',
		);

		$dnt_plugins = array(
			'ga-disable-dnt/ga-disable-dnt.php' => 'GA Disable DNT',
			'matomo/matomo.php' => 'Matomo',
			'plausible-analytics/plausible-analytics.php' => 'Plausible',
		);

		$active_analytics = array();
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_analytics[] = $plugin_name;
			}
		}

		$active_dnt = array();
		foreach ( $dnt_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_dnt[] = $plugin_name;
			}
		}

		$stats['analytics_tools'] = ! empty( $active_analytics ) ? implode( ', ', $active_analytics ) : 'none';
		$stats['dnt_tools'] = ! empty( $active_dnt ) ? implode( ', ', $active_dnt ) : 'none';

		if ( ! empty( $active_analytics ) && empty( $active_dnt ) ) {
			$issues[] = __( 'Analytics detected without a Do Not Track safeguard', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some visitors ask not to be tracked. Respecting Do Not Track builds trust and supports privacy-friendly analytics.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/do-not-track-respect',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
