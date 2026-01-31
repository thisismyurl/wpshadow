<?php
/**
 * Sg Optimizer Webp Conversion Diagnostic
 *
 * Sg Optimizer Webp Conversion not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.911.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sg Optimizer Webp Conversion Diagnostic Class
 *
 * @since 1.911.0000
 */
class Diagnostic_SgOptimizerWebpConversion extends Diagnostic_Base {

	protected static $slug = 'sg-optimizer-webp-conversion';
	protected static $title = 'Sg Optimizer Webp Conversion';
	protected static $description = 'Sg Optimizer Webp Conversion not optimized';
	protected static $family = 'performance';

	public static function check() {
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/sg-optimizer-webp-conversion',
			);
		}
		
		return null;
	}
}
