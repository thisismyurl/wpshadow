<?php
/**
 * SearchWP Statistics Performance Diagnostic
 *
 * SearchWP statistics slowing searches.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.411.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SearchWP Statistics Performance Diagnostic Class
 *
 * @since 1.411.0000
 */
class Diagnostic_SearchwpStatisticsPerformance extends Diagnostic_Base {

	protected static $slug = 'searchwp-statistics-performance';
	protected static $title = 'SearchWP Statistics Performance';
	protected static $description = 'SearchWP statistics slowing searches';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'SearchWP' ) ) {
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/searchwp-statistics-performance',
			);
		}
		
		return null;
	}
}
