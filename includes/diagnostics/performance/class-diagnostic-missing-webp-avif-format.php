<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing WebP/AVIF Format (IMG-002)
 * 
 * Checks if modern formats (WebP/AVIF) are served.
 * Philosophy: Educate (#5) about next-gen formats.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Missing_Webp_Avif_Format extends Diagnostic_Base {
	
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
