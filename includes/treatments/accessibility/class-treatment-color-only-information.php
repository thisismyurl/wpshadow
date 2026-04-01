<?php
/**
 * Color-Only Information Treatment
 *
 * Issue #4929: Information Conveyed by Color Only
 * Pillar: 🌍 Accessibility First
 *
 * Checks if information uses more than just color.
 * Colorblind users (8% males) can't distinguish red/green.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Color_Only_Information Class
 *
 * @since 0.6093.1200
 */
class Treatment_Color_Only_Information extends Treatment_Base {

	protected static $slug = 'color-only-information';
	protected static $title = 'Information Conveyed by Color Only';
	protected static $description = 'Checks if information uses color plus additional indicators';
	protected static $family = 'accessibility';

	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Color_Only_Information' );
	}
}
