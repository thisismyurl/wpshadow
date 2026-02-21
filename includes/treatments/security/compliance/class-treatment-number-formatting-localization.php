<?php
/**
 * Number Formatting Localization Treatment
 *
 * Issue #4923: Numbers Not Locale-Formatted
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if numbers respect locale formatting.
 * 1,000.50 vs 1.000,50 confuses international users.
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
 * Treatment_Number_Formatting_Localization Class
 *
 * @since 1.6050.0000
 */
class Treatment_Number_Formatting_Localization extends Treatment_Base {

	protected static $slug = 'number-formatting-localization';
	protected static $title = 'Numbers Not Locale-Formatted';
	protected static $description = 'Checks if numbers use locale-appropriate formatting';
	protected static $family = 'compliance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Number_Formatting_Localization' );
	}
}
