<?php
/**
 * Address Format Flexibility Treatment
 *
 * Issue #4924: Address Fields Assume US Format
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if address forms support international formats.
 * Not everyone has states and ZIP codes.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Address_Format_Flexibility Class
 *
 * @since 1.6093.1200
 */
class Treatment_Address_Format_Flexibility extends Treatment_Base {

	protected static $slug = 'address-format-flexibility';
	protected static $title = 'Address Fields Assume US Format';
	protected static $description = 'Checks if address forms support international formats';
	protected static $family = 'compliance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Address_Format_Flexibility' );
	}
}
