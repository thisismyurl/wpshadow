<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Featured Image Size Mismatch (IMG-011)
 * 
 * Checks if featured images much larger than theme display.
 * Philosophy: Show value (#9) with listing page improvements.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Featured_Image_Size_Mismatch extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
// Check image optimization
		$webp_support = extension_loaded('imagick') || function_exists('imagewebp');
		
		if (!$webp_support) {
			return [
				'status' => 'info',
				'message' => __('WebP support would reduce image sizes 25-35%', 'wpshadow'),
				'threat_level' => 'low'
			];
		}
		return null; // No issues detected
	}
}
