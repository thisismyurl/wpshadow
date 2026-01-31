<?php
/**
 * Accessibe Remediation Validation Diagnostic
 *
 * Accessibe Remediation Validation not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1104.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibe Remediation Validation Diagnostic Class
 *
 * @since 1.1104.0000
 */
class Diagnostic_AccessibeRemediationValidation extends Diagnostic_Base {

	protected static $slug = 'accessibe-remediation-validation';
	protected static $title = 'Accessibe Remediation Validation';
	protected static $description = 'Accessibe Remediation Validation not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// Check for AccessiBe widget
		$has_accessibe = get_option( 'accessibe_widget_id', '' ) !== '' ||
		                 defined( 'ACCESSIBE_WIDGET_ID' ) ||
		                 get_option( 'accessibe_enabled', 'no' ) === 'yes';
		
		if ( ! $has_accessibe ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Widget ID configured
		$widget_id = get_option( 'accessibe_widget_id', '' );
		if ( empty( $widget_id ) && ! defined( 'ACCESSIBE_WIDGET_ID' ) ) {
			$issues[] = __( 'Widget ID missing (script not loaded)', 'wpshadow' );
		}
		
		// Check 2: Profile configuration
		$profile = get_option( 'accessibe_profile', 'default' );
		if ( 'default' === $profile ) {
			$issues[] = __( 'Default profile (not customized for site)', 'wpshadow' );
		}
		
		// Check 3: WCAG compliance level
		$wcag_level = get_option( 'accessibe_wcag_level', 'AA' );
		if ( 'A' === $wcag_level ) {
			$issues[] = __( 'WCAG Level A (AA recommended)', 'wpshadow' );
		}
		
		// Check 4: Testing mode
		$testing_mode = get_option( 'accessibe_testing_mode', 'no' );
		if ( 'yes' === $testing_mode ) {
			$issues[] = __( 'Testing mode active (not enforcing fixes)', 'wpshadow' );
		}
		
		// Check 5: Performance impact
		$defer_script = get_option( 'accessibe_defer_script', 'no' );
		if ( 'no' === $defer_script ) {
			$issues[] = __( 'Script not deferred (blocking page load)', 'wpshadow' );
		}
		
		// Check 6: Manual overrides
		$allow_overrides = get_option( 'accessibe_allow_overrides', 'yes' );
		if ( 'yes' === $allow_overrides ) {
			$issues[] = __( 'Manual overrides allowed (inconsistent fixes)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of accessibility issues */
				__( 'AccessiBe has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/accessibe-remediation-validation',
		);
	}
}
