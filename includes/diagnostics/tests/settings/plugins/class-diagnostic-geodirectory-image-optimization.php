<?php
/**
 * GeoDirectory Image Optimization Diagnostic
 *
 * GeoDirectory images not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.555.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Image Optimization Diagnostic Class
 *
 * @since 1.555.0000
 */
class Diagnostic_GeodirectoryImageOptimization extends Diagnostic_Base {

	protected static $slug = 'geodirectory-image-optimization';
	protected static $title = 'GeoDirectory Image Optimization';
	protected static $description = 'GeoDirectory images not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/geodirectory-image-optimization',
			);
		}
		
		return null;
	}
}
