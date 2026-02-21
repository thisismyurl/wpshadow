<?php
/**
 * Theme Mobile Menu Treatment
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1600
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Theme_Mobile_Menu extends Treatment_Base {
	protected static $slug = 'theme-mobile-menu';
	protected static $title = 'Theme Mobile Menu';
	protected static $description = 'Detects mobile menu functionality issues';
	protected static $family = 'functionality';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Mobile_Menu' );
	}
}
