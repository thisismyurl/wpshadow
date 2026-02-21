<?php
/**
 * Phone Number Format Flexibility Treatment
 *
 * Issue #4925: Phone Fields Require US Format
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if phone fields support international formats.
 * Not everyone uses (555) 123-4567 format.
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
 * Treatment_Phone_Number_Format_Flexibility Class
 *
 * @since 1.6050.0000
 */
class Treatment_Phone_Number_Format_Flexibility extends Treatment_Base {

	protected static $slug = 'phone-number-format-flexibility';
	protected static $title = 'Phone Fields Require US Format';
	protected static $description = 'Checks if phone fields support international formats';
	protected static $family = 'compliance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Phone_Number_Format_Flexibility' );
	}
}
