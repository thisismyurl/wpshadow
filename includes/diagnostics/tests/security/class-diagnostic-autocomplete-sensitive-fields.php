<?php
/**
 * Autocomplete Sensitive Fields Diagnostic
 *
 * Issue #4953: Autocomplete Enabled on Sensitive Fields
 * Pillar: 🛡️ Safe by Default / #10: Beyond Pure (Privacy)
 *
 * Checks if sensitive form fields disable autocomplete.
 * Browser autofill exposes data on shared computers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Autocomplete_Sensitive_Fields Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Autocomplete_Sensitive_Fields extends Diagnostic_Base {

	protected static $slug = 'autocomplete-sensitive-fields';
	protected static $title = 'Autocomplete Enabled on Sensitive Fields';
	protected static $description = 'Checks if sensitive fields disable browser autocomplete';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Disable autocomplete on password fields: autocomplete="new-password"', 'wpshadow' );
		$issues[] = __( 'Disable autocomplete on credit card fields', 'wpshadow' );
		$issues[] = __( 'Disable autocomplete on SSN/tax ID fields', 'wpshadow' );
		$issues[] = __( 'Disable autocomplete on security questions', 'wpshadow' );
		$issues[] = __( 'Keep autocomplete ON for name/email (usability)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Autocomplete stores sensitive data in browser. On shared or public computers, this exposes passwords and payment information to other users.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/autocomplete-security',
				'details'      => array(
					'recommendations'         => $issues,
					'correct_usage'           => '<input type="password" autocomplete="new-password">',
					'balance'                 => 'Security vs usability - disable only for sensitive fields',
					'commandment'             => 'Commandment #10: Beyond Pure (Privacy First)',
				),
			);
		}

		return null;
	}
}
