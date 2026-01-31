<?php
/**
 * Flywheel Cache Management Diagnostic
 *
 * Flywheel Cache Management needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1005.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flywheel Cache Management Diagnostic Class
 *
 * @since 1.1005.0000
 */
class Diagnostic_FlywheelCacheManagement extends Diagnostic_Base {

	protected static $slug = 'flywheel-cache-management';
	protected static $title = 'Flywheel Cache Management';
	protected static $description = 'Flywheel Cache Management needs attention';
	protected static $family = 'performance';

	public static function check() {
		
		$issues = array();
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
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
				'kb_link'     => 'https://wpshadow.com/kb/flywheel-cache-management',
			);
		}
		
		return null;
	}
}
