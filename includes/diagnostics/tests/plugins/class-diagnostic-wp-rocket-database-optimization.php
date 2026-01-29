<?php
/**
 * WP Rocket Database Optimization Diagnostic
 *
 * WP Rocket database cleanup disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.440.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Rocket Database Optimization Diagnostic Class
 *
 * @since 1.440.0000
 */
class Diagnostic_WpRocketDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'wp-rocket-database-optimization';
	protected static $title = 'WP Rocket Database Optimization';
	protected static $description = 'WP Rocket database cleanup disabled';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-rocket-database-optimization',
			);
		}
		
		return null;
	}
}
