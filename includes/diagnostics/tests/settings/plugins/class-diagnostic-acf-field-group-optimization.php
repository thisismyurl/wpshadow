<?php
/**
 * ACF Field Group Optimization Diagnostic
 *
 * ACF field groups not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.450.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Field Group Optimization Diagnostic Class
 *
 * @since 1.450.0000
 */
class Diagnostic_AcfFieldGroupOptimization extends Diagnostic_Base {

	protected static $slug = 'acf-field-group-optimization';
	protected static $title = 'ACF Field Group Optimization';
	protected static $description = 'ACF field groups not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/acf-field-group-optimization',
			);
		}
		
		return null;
	}
}
