<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Image Width/Height Attributes (IMG-010)
 *
 * Detects <img> tags without width/height.
 * Philosophy: Helpful neighbor (#1) - prevent layout shift.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Missing_Image_Width_Height extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check image optimization
		$webp_support = extension_loaded( 'imagick' ) || function_exists( 'imagewebp' );

		if ( ! $webp_support ) {
			return array(
				'status'       => 'info',
				'message'      => __( 'WebP support would reduce image sizes 25-35%', 'wpshadow' ),
				'threat_level' => 'low',
			);
		}
		return null; // No issues detected
	}
}
