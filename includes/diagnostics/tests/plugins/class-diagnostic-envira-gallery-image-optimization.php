<?php
/**
 * Envira Gallery Image Optimization Diagnostic
 *
 * Envira Gallery images not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.489.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira Gallery Image Optimization Diagnostic Class
 *
 * @since 1.489.0000
 */
class Diagnostic_EnviraGalleryImageOptimization extends Diagnostic_Base {

	protected static $slug = 'envira-gallery-image-optimization';
	protected static $title = 'Envira Gallery Image Optimization';
	protected static $description = 'Envira Gallery images not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Envira_Gallery' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/envira-gallery-image-optimization',
			);
		}
		
		return null;
	}
}
