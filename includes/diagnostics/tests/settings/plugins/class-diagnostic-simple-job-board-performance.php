<?php
/**
 * Simple Job Board Performance Diagnostic
 *
 * Simple Job Board queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.545.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple Job Board Performance Diagnostic Class
 *
 * @since 1.545.0000
 */
class Diagnostic_SimpleJobBoardPerformance extends Diagnostic_Base {

	protected static $slug = 'simple-job-board-performance';
	protected static $title = 'Simple Job Board Performance';
	protected static $description = 'Simple Job Board queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'SJB_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/simple-job-board-performance',
			);
		}
		
		return null;
	}
}
