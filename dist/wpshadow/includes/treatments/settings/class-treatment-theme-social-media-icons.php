<?php
/**
 * Theme Social Media Icons Treatment
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

class Treatment_Theme_Social_Media_Icons extends Treatment_Base {
	protected static $slug = 'theme-social-media-icons';
	protected static $title = 'Theme Social Media Icons';
	protected static $description = 'Checks if social media icons load properly';
	protected static $family = 'functionality';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Social_Media_Icons' );
	}
}
