<?php
/**
 * Next Generation Image Format Treatment
 *
 * Issue #4978: Images Not in Modern Formats (WEBP)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if modern image formats (WebP) are used.
 * WebP is 25% smaller than JPEG with same quality.
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
 * Treatment_Next_Generation_Image_Format Class
 *
 * @since 0.6093.1200
 */
class Treatment_Next_Generation_Image_Format extends Treatment_Base {

	protected static $slug = 'next-generation-image-format';
	protected static $title = 'Images Not in Modern Formats (WEBP)';
	protected static $description = 'Checks if modern image formats like WebP are used';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Next_Generation_Image_Format' );
	}
}
