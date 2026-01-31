<?php
/**
 * Ninja Tables Custom CSS Diagnostic
 *
 * Ninja Tables CSS not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.482.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables Custom CSS Diagnostic Class
 *
 * @since 1.482.0000
 */
class Diagnostic_NinjaTablesCustomCss extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-custom-css';
	protected static $title = 'Ninja Tables Custom CSS';
	protected static $description = 'Ninja Tables CSS not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'NINJA_TABLES_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: CSS validation enabled
		$css_validation = get_option( 'ninjatables_css_validation', 0 );
		if ( ! $css_validation ) {
			$issues[] = 'CSS validation not enabled';
		}

		// Check 2: Sanitization enabled
		$sanitize = get_option( 'ninjatables_css_sanitization', 0 );
		if ( ! $sanitize ) {
			$issues[] = 'CSS sanitization not enabled';
		}

		// Check 3: Security filtering
		$security = get_option( 'ninjatables_css_security_filtering', 0 );
		if ( ! $security ) {
			$issues[] = 'Security filtering not enabled';
		}

		// Check 4: CSS scope limiting
		$scope = get_option( 'ninjatables_css_scope_limiting', 0 );
		if ( ! $scope ) {
			$issues[] = 'CSS scope limiting not enabled';
		}

		// Check 5: Custom CSS storage
		$storage = get_option( 'ninjatables_custom_css_storage', '' );
		if ( empty( $storage ) ) {
			$issues[] = 'Custom CSS storage not configured';
		}

		// Check 6: CSS injection prevention
		$injection = get_option( 'ninjatables_injection_prevention', 0 );
		if ( ! $injection ) {
			$issues[] = 'CSS injection prevention not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d CSS security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-tables-custom-css',
			);
		}

		return null;
	}
}
