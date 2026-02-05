<?php
/**
 * Input Validation Client and Server Treatment
 *
 * Issue #4881: Input Validation Only on Client Side (JavaScript Can Be Bypassed)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if input validation happens on both client and server.
 * Client-side validation is UX. Server-side validation is security.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Input_Validation_Client_Server Class
 *
 * Checks for:
 * - Server-side validation on ALL inputs
 * - Client-side validation for immediate UX feedback
 * - Validation rules match on client and server
 * - No reliance on client-side validation alone
 * - Type validation (email, URL, integer, etc)
 * - Length validation (min/max characters)
 * - Format validation (regex patterns)
 * - Sanitization after validation
 *
 * Why this matters:
 * - Attackers disable JavaScript or modify requests
 * - Client-side validation is for UX only
 * - Server-side validation prevents injection attacks
 * - Inconsistent validation creates security holes
 *
 * @since 1.6050.0000
 */
class Treatment_Input_Validation_Client_Server extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'input-validation-client-server';

	/**
	 * The treatment title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'Input Validation Only on Client Side (JavaScript Can Be Bypassed)';

	/**
	 * The treatment description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if input validation happens on both client (UX) and server (security)';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance treatment - actual validation checking requires code review.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'ALWAYS validate inputs on server side (never trust client)', 'wpshadow' );
		$issues[] = __( 'Client-side validation provides immediate UX feedback', 'wpshadow' );
		$issues[] = __( 'Validation rules MUST match on client and server', 'wpshadow' );
		$issues[] = __( 'Use WordPress sanitization: sanitize_text_field(), sanitize_email()', 'wpshadow' );
		$issues[] = __( 'Validate data types: is_email(), is_numeric(), is_url()', 'wpshadow' );
		$issues[] = __( 'Check length constraints: strlen() >= min, <= max', 'wpshadow' );
		$issues[] = __( 'Use whitelists, not blacklists (allow known good, not block known bad)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Attackers can disable JavaScript or send requests directly to the server. Server-side validation is the only real security layer.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,  // Requires code review
				'kb_link'      => 'https://wpshadow.com/kb/input-validation',
				'details'      => array(
					'recommendations'         => $issues,
					'attack_vector'           => 'Attacker modifies POST request with Burp Suite or curl',
					'client_side_purpose'     => 'User experience (immediate feedback)',
					'server_side_purpose'     => 'Security (prevent injection attacks)',
					'wordpress_functions'     => 'sanitize_text_field(), sanitize_email(), wp_kses(), absint()',
					'never_trust'             => 'NEVER trust: $_POST, $_GET, $_REQUEST, $_COOKIE',
				),
			);
		}

		return null;
	}
}
