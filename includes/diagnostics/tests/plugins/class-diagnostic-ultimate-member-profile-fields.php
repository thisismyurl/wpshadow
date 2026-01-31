<?php
/**
 * Ultimate Member Profile Fields Diagnostic
 *
 * Ultimate Member fields not sanitized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.524.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ultimate Member Profile Fields Diagnostic Class
 *
 * @since 1.524.0000
 */
class Diagnostic_UltimateMemberProfileFields extends Diagnostic_Base {

	protected static $slug = 'ultimate-member-profile-fields';
	protected static $title = 'Ultimate Member Profile Fields';
	protected static $description = 'Ultimate Member fields not sanitized';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'ultimatemember_version' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify field sanitization
		$field_sanitization = get_option( 'um_field_sanitization', false );
		if ( ! $field_sanitization ) {
			$issues[] = __( 'Profile field sanitization not enabled', 'wpshadow' );
		}

		// Check 2: Check profile validation
		$profile_validation = get_option( 'um_profile_validation', false );
		if ( ! $profile_validation ) {
			$issues[] = __( 'Profile field validation not configured', 'wpshadow' );
		}

		// Check 3: Verify XSS protection
		$xss_protection = get_option( 'um_xss_protection', false );
		if ( ! $xss_protection ) {
			$issues[] = __( 'XSS protection not enabled for profile fields', 'wpshadow' );
		}

		// Check 4: Check HTML/script filtering
		$html_filtering = get_option( 'um_html_filtering', false );
		if ( ! $html_filtering ) {
			$issues[] = __( 'HTML and script filtering not enabled', 'wpshadow' );
		}

		// Check 5: Verify SSL for profile updates
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for secure profile updates', 'wpshadow' );
		}

		// Check 6: Check nonce verification
		$nonce_verification = get_option( 'um_nonce_verification', false );
		if ( ! $nonce_verification ) {
			$issues[] = __( 'Nonce verification not enabled for profile forms', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 60 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Ultimate Member profile field security issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/ultimate-member-profile-fields',
			);
		}

		return null;
	}
}

	}
}
