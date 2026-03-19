<?php
/**
 * Picture Element Responsive Images Treatment
 *
 * Issue #4977: Images Not Responsive (Not Optimized)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if images use responsive picture element.
 * Serving same size to phones and desktops wastes bandwidth.
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
 * Treatment_Picture_Element_Responsive_Images Class
 *
 * @since 1.6093.1200
 */
class Treatment_Picture_Element_Responsive_Images extends Treatment_Base {

	protected static $slug = 'picture-element-responsive-images';
	protected static $title = 'Images Not Responsive (Not Optimized)';
	protected static $description = 'Checks if images use responsive picture element';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Picture_Element_Responsive_Images' );
	}
}
