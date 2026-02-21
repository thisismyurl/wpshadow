<?php
/**
 * Timezone Handling Treatment
 *
 * Issue #4921: Hardcoded UTC Times (No User Timezone)
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if times respect user timezone settings.
 * Displaying UTC to users is confusing.
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
 * Treatment_Timezone_Handling Class
 *
 * @since 1.6050.0000
 */
class Treatment_Timezone_Handling extends Treatment_Base {

	protected static $slug = 'timezone-handling';
	protected static $title = 'Hardcoded UTC Times (No User Timezone)';
	protected static $description = 'Checks if times are displayed in user\'s timezone';
	protected static $family = 'compliance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Timezone_Handling' );
	}
}
