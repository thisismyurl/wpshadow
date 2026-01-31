<?php
/**
 * WP Rocket Cache Lifespan Diagnostic
 *
 * WP Rocket cache lifespan too short.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.438.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Rocket Cache Lifespan Diagnostic Class
 *
 * @since 1.438.0000
 */
class Diagnostic_WpRocketCacheLifespan extends Diagnostic_Base {

	protected static $slug = 'wp-rocket-cache-lifespan';
	protected static $title = 'WP Rocket Cache Lifespan';
	protected static $description = 'WP Rocket cache lifespan too short';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-rocket-cache-lifespan',
			);
		}
		
		return null;
	}
}
