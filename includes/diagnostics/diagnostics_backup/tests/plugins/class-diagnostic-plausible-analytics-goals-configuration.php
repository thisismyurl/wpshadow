<?php
/**
 * Plausible Analytics Goals Configuration Diagnostic
 *
 * Plausible Analytics Goals Configuration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1367.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plausible Analytics Goals Configuration Diagnostic Class
 *
 * @since 1.1367.0000
 */
class Diagnostic_PlausibleAnalyticsGoalsConfiguration extends Diagnostic_Base {

	protected static $slug = 'plausible-analytics-goals-configuration';
	protected static $title = 'Plausible Analytics Goals Configuration';
	protected static $description = 'Plausible Analytics Goals Configuration misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'plausible_analytics_init' ) && ! defined( 'PLAUSIBLE_ANALYTICS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Goals configured.
		$goals = get_option( 'plausible_goals', array() );
		if ( empty( $goals ) ) {
			$issues[] = 'no goals configured';
		}

		// Check 2: Custom events.
		$custom_events = get_option( 'plausible_custom_events', '1' );
		if ( '0' === $custom_events ) {
			$issues[] = 'custom events disabled';
		}

		// Check 3: Outbound link tracking.
		$outbound = get_option( 'plausible_track_outbound', '1' );
		if ( '0' === $outbound ) {
			$issues[] = 'outbound tracking disabled';
		}

		// Check 4: File downloads tracking.
		$downloads = get_option( 'plausible_track_downloads', '1' );
		if ( '0' === $downloads ) {
			$issues[] = 'download tracking disabled';
		}

		// Check 5: 404 tracking.
		$track_404 = get_option( 'plausible_track_404', '1' );
		if ( '0' === $track_404 ) {
			$issues[] = '404 tracking disabled';
		}

		// Check 6: Event metadata.
		$metadata = get_option( 'plausible_event_metadata', '1' );
		if ( '0' === $metadata ) {
			$issues[] = 'event metadata disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Plausible Analytics issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/plausible-analytics-goals-configuration',
			);
		}

		return null;
	}
}
