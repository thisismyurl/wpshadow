<?php
/**
 * Solid Security Password Requirements Diagnostic
 *
 * Solid Security Password Requirements misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.884.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Solid Security Password Requirements Diagnostic Class
 *
 * @since 1.884.0000
 */
class Diagnostic_SolidSecurityPasswordRequirements extends Diagnostic_Base {

	protected static $slug = 'solid-security-password-requirements';
	protected static $title = 'Solid Security Password Requirements';
	protected static $description = 'Solid Security Password Requirements misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'itsec_load_textdomain' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Password length requirement
		$min_length = absint( get_option( 'itsec_password_min_length', 0 ) );
		if ( $min_length < 8 ) {
			$issues[] = 'Password minimum length below 8 characters';
		}

		// Check 2: Uppercase letter requirement
		$require_upper = get_option( 'itsec_password_require_uppercase', 0 );
		if ( ! $require_upper ) {
			$issues[] = 'Uppercase letter requirement not enabled';
		}

		// Check 3: Lowercase letter requirement
		$require_lower = get_option( 'itsec_password_require_lowercase', 0 );
		if ( ! $require_lower ) {
			$issues[] = 'Lowercase letter requirement not enabled';
		}

		// Check 4: Number requirement
		$require_number = get_option( 'itsec_password_require_number', 0 );
		if ( ! $require_number ) {
			$issues[] = 'Number requirement not enabled';
		}

		// Check 5: Special character requirement
		$require_special = get_option( 'itsec_password_require_special_char', 0 );
		if ( ! $require_special ) {
			$issues[] = 'Special character requirement not enabled';
		}

		// Check 6: Password expiration
		$expiration_days = absint( get_option( 'itsec_password_expiration_days', 0 ) );
		if ( $expiration_days <= 0 ) {
			$issues[] = 'Password expiration not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 55;
			$threat_multiplier = 6;
			$max_threat = 85;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d password requirement issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/solid-security-password-requirements',
			);
		}

		return null;
	}
}
