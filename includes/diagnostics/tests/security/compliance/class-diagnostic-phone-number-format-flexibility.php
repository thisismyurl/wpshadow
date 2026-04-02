<?php
/**
 * Phone Number Format Flexibility Diagnostic
 *
 * Issue #4925: Phone Fields Require US Format
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if phone fields support international formats.
 * Not everyone uses (555) 123-4567 format.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Phone_Number_Format_Flexibility Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Phone_Number_Format_Flexibility extends Diagnostic_Base {

	protected static $slug = 'phone-number-format-flexibility';
	protected static $title = 'Phone Fields Require US Format';
	protected static $description = 'Checks if phone fields support international formats';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Accept international format with country code: +1 555 123 4567', 'wpshadow' );
		$issues[] = __( 'Don\'t enforce specific formatting (dashes, parentheses)', 'wpshadow' );
		$issues[] = __( 'Allow varying lengths: 7 digits (local) to 15 digits (international)', 'wpshadow' );
		$issues[] = __( 'Provide country code dropdown with flags', 'wpshadow' );
		$issues[] = __( 'Format display per country after validation', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Phone formats vary globally. Requiring (555) 123-4567 rejects international numbers. Accept flexible input and validate for digits only.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/phone-formats',
				'details'      => array(
					'recommendations'         => $issues,
					'us_format'               => '(555) 123-4567 or 555-123-4567',
					'uk_format'               => '020 7123 4567',
					'international'           => '+44 20 7123 4567',
					'validation'              => 'Strip formatting, validate digit count per country',
				),
			);
		}

		return null;
	}
}
