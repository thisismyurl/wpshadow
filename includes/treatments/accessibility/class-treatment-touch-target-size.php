<?php
/**
 * Touch Target Size Treatment
 *
 * Issue #4942: Touch Targets Too Small (<44px)
 * Pillar: 🌍 Accessibility First
 *
 * Checks if interactive elements meet minimum touch size.
 * Small buttons are difficult for mobile and motor-impaired users.
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
 * Treatment_Touch_Target_Size Class
 *
 * @since 1.6050.0000
 */
class Treatment_Touch_Target_Size extends Treatment_Base {

	protected static $slug = 'touch-target-size';
	protected static $title = 'Touch Targets Too Small (<44px)';
	protected static $description = 'Checks if buttons and links are large enough for touch';
	protected static $family = 'accessibility';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Touch_Target_Size' );
	}
}
