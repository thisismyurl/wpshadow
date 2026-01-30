<?php
/**
 * Statcounter Invisible Tracker Diagnostic
 *
 * Statcounter Invisible Tracker misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1360.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Statcounter Invisible Tracker Diagnostic Class
 *
 * @since 1.1360.0000
 */
class Diagnostic_StatcounterInvisibleTracker extends Diagnostic_Base {

	protected static $slug = 'statcounter-invisible-tracker';
	protected static $title = 'Statcounter Invisible Tracker';
	protected static $description = 'Statcounter Invisible Tracker misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! get_option( 'statcounter_project_id', '' ) && ! get_option( 'statcounter_security_code', '' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Project ID configured
		$project_id = get_option( 'statcounter_project_id', '' );
		if ( empty( $project_id ) ) {
			$issues[] = 'StatCounter project ID not configured';
		}
		
		// Check 2: Security code configured
		$security_code = get_option( 'statcounter_security_code', '' );
		if ( empty( $security_code ) ) {
			$issues[] = 'StatCounter security code not configured';
		}
		
		// Check 3: Invisible tracking enabled
		$invisible = get_option( 'statcounter_invisible_tracking', 0 );
		if ( ! $invisible ) {
			$issues[] = 'Invisible tracking not enabled';
		}
		
		// Check 4: Async loading enabled
		$async = get_option( 'statcounter_async_loading', 0 );
		if ( ! $async ) {
			$issues[] = 'Async tracking script loading not enabled';
		}
		
		// Check 5: Do Not Track respect
		$dnt = get_option( 'statcounter_respect_dnt', 0 );
		if ( ! $dnt ) {
			$issues[] = 'Do Not Track respect not enabled';
		}
		
		// Check 6: Consent integration
		$consent_integration = get_option( 'statcounter_consent_integration', 0 );
		if ( ! $consent_integration ) {
			$issues[] = 'Consent integration not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d StatCounter tracking issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/statcounter-invisible-tracker',
			);
		}
		
		return null;
	}
}
