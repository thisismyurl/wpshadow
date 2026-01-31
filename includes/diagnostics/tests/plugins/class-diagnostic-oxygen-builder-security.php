<?php
/**
 * Oxygen Builder Security Diagnostic
 *
 * Oxygen Builder Security issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.812.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oxygen Builder Security Diagnostic Class
 *
 * @since 1.812.0000
 */
class Diagnostic_OxygenBuilderSecurity extends Diagnostic_Base {

	protected static $slug = 'oxygen-builder-security';
	protected static $title = 'Oxygen Builder Security';
	protected static $description = 'Oxygen Builder Security issues found';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		// Check 1: Frontend editing security
		$frontend = get_option( 'oxygen_frontend_editing_secured', 0 );
		if ( ! $frontend ) {
			$issues[] = 'Frontend editing not properly secured';
		}

		// Check 2: User role restrictions
		$roles = get_option( 'oxygen_user_role_restrictions_enabled', 0 );
		if ( ! $roles ) {
			$issues[] = 'User role restrictions not enabled';
		}

		// Check 3: Code execution restrictions
		$code_exec = get_option( 'oxygen_code_execution_restricted', 0 );
		if ( ! $code_exec ) {
			$issues[] = 'Code execution not properly restricted';
		}

		// Check 4: File upload security
		$uploads = get_option( 'oxygen_file_upload_security_enabled', 0 );
		if ( ! $uploads ) {
			$issues[] = 'File upload security not configured';
		}

		// Check 5: Nonce verification
		$nonce = get_option( 'oxygen_nonce_verification_enabled', 0 );
		if ( ! $nonce ) {
			$issues[] = 'Nonce verification not enabled';
		}

		// Check 6: Element access control
		$element_access = get_option( 'oxygen_element_access_control_enabled', 0 );
		if ( ! $element_access ) {
			$issues[] = 'Element access control not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/oxygen-builder-security',
			);
		}

		return null;
	}
}
