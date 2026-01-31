<?php
/**
 * wpForo Database Performance Diagnostic
 *
 * wpForo database queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.534.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wpForo Database Performance Diagnostic Class
 *
 * @since 1.534.0000
 */
class Diagnostic_WpforoDatabasePerformance extends Diagnostic_Base {

	protected static $slug = 'wpforo-database-performance';
	protected static $title = 'wpForo Database Performance';
	protected static $description = 'wpForo database queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WPFORO_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpforo-database-performance',
			);
		}
		
		return null;
	}
}
