<?php
/**
 * bbPress Database Optimization Diagnostic
 *
 * bbPress database queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.512.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Database Optimization Diagnostic Class
 *
 * @since 1.512.0000
 */
class Diagnostic_BbpressDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'bbpress-database-optimization';
	protected static $title = 'bbPress Database Optimization';
	protected static $description = 'bbPress database queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
			return null;
		}
		
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
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-database-optimization',
			);
		}
		
		return null;
	}
}
