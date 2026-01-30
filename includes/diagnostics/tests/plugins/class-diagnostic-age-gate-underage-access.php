<?php
/**
 * Age Gate Underage Access Diagnostic
 *
 * Age Gate Underage Access not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1123.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Age Gate Underage Access Diagnostic Class
 *
 * @since 1.1123.0000
 */
class Diagnostic_AgeGateUnderageAccess extends Diagnostic_Base {

	protected static $slug = 'age-gate-underage-access';
	protected static $title = 'Age Gate Underage Access';
	protected static $description = 'Age Gate Underage Access not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Age_Gate' ) && ! defined( 'AGE_GATE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Minimum age requirement.
		$min_age = get_option( 'age_gate_minimum_age', 0 );
		if ( $min_age < 13 ) {
			$issues[] = "minimum age set to {$min_age} (consider COPPA compliance - 13+)";
		}
		
		// Check 2: Bypass parameter in URL.
		$allow_bypass = get_option( 'age_gate_allow_bypass', '0' );
		if ( '1' === $allow_bypass ) {
			$issues[] = 'URL bypass parameter enabled (age verification can be skipped)';
		}
		
		// Check 3: Remember me functionality.
		$remember_verification = get_option( 'age_gate_remember', '1' );
		if ( '0' === $remember_verification ) {
			$issues[] = 'remember me disabled (users must verify on every visit)';
		}
		
		// Check 4: Form validation strength.
		$validation_method = get_option( 'age_gate_validation', 'simple' );
		if ( 'simple' === $validation_method ) {
			$issues[] = 'simple validation used (easily bypassed by entering false data)';
		}
		
		// Check 5: Age verification on restricted content.
		global $wpdb;
		$restricted_content = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
				'_age_gate_required',
				'1'
			)
		);
		$gate_enabled = get_option( 'age_gate_enabled', '0' );
		if ( $restricted_content > 0 && '0' === $gate_enabled ) {
			$issues[] = "{$restricted_content} posts marked age-restricted but gate is disabled";
		}
		
		// Check 6: JavaScript-only validation.
		$js_validation = get_option( 'age_gate_js_validation', '1' );
		if ( '1' === $js_validation && ! has_filter( 'age_gate_server_validation' ) ) {
			$issues[] = 'JavaScript-only validation (easily bypassed by disabling JS)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Age Gate underage access issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/age-gate-underage-access',
			);
		}
		
		return null;
	}
}
