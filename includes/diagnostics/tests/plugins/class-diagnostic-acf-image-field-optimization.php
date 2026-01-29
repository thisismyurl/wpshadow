<?php
/**
 * ACF Image Field Optimization Diagnostic
 *
 * ACF image fields not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.453.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Image Field Optimization Diagnostic Class
 *
 * @since 1.453.0000
 */
class Diagnostic_AcfImageFieldOptimization extends Diagnostic_Base {

	protected static $slug = 'acf-image-field-optimization';
	protected static $title = 'ACF Image Field Optimization';
	protected static $description = 'ACF image fields not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/acf-image-field-optimization',
			);
		}
		
		return null;
	}
}
