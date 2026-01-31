<?php
/**
 * Pods Framework Template Performance Diagnostic
 *
 * Pods Framework Template Performance issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1055.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pods Framework Template Performance Diagnostic Class
 *
 * @since 1.1055.0000
 */
class Diagnostic_PodsFrameworkTemplatePerformance extends Diagnostic_Base {

	protected static $slug = 'pods-framework-template-performance';
	protected static $title = 'Pods Framework Template Performance';
	protected static $description = 'Pods Framework Template Performance issue detected';
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/pods-framework-template-performance',
			);
		}
		
		return null;
	}
}
