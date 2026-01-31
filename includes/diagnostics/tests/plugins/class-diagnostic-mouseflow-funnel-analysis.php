<?php
/**
 * Mouseflow Funnel Analysis Diagnostic
 *
 * Mouseflow Funnel Analysis misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1378.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mouseflow Funnel Analysis Diagnostic Class
 *
 * @since 1.1378.0000
 */
class Diagnostic_MouseflowFunnelAnalysis extends Diagnostic_Base {

	protected static $slug = 'mouseflow-funnel-analysis';
	protected static $title = 'Mouseflow Funnel Analysis';
	protected static $description = 'Mouseflow Funnel Analysis misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Mouseflow tracking code installed
		$tracking_code = get_option( 'mouseflow_tracking_id', '' );
		if ( empty( $tracking_code ) ) {
			$issues[] = 'Tracking code not installed';
		}

		// Check 2: Funnel steps configured
		$funnel_steps = get_option( 'mouseflow_funnel_steps', array() );
		if ( empty( $funnel_steps ) ) {
			$issues[] = 'Funnel steps not configured';
		}

		// Check 3: Conversion goals defined
		$conversion_goals = get_option( 'mouseflow_conversion_goals', array() );
		if ( empty( $conversion_goals ) ) {
			$issues[] = 'Conversion goals not defined';
		}

		// Check 4: Heatmap tracking enabled
		$heatmaps = get_option( 'mouseflow_heatmaps_enabled', false );
		if ( ! $heatmaps ) {
			$issues[] = 'Heatmap tracking disabled';
		}

		// Check 5: Session recording enabled
		$session_recording = get_option( 'mouseflow_session_recording', false );
		if ( ! $session_recording ) {
			$issues[] = 'Session recording disabled';
		}

		// Check 6: Privacy compliance configured
		$privacy_compliance = get_option( 'mouseflow_privacy_compliance', false );
		if ( ! $privacy_compliance ) {
			$issues[] = 'Privacy compliance not configured';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Mouseflow funnel analysis issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/mouseflow-funnel-analysis',
			);
		}

		return null;
	}
		}
		return null;
	}
}
