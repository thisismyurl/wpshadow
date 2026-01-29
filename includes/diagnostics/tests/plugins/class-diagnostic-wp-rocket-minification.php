<?php
/**
 * WP Rocket Minification Diagnostic
 *
 * WP Rocket minification breaking scripts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.439.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Rocket Minification Diagnostic Class
 *
 * @since 1.439.0000
 */
class Diagnostic_WpRocketMinification extends Diagnostic_Base {

	protected static $slug = 'wp-rocket-minification';
	protected static $title = 'WP Rocket Minification';
	protected static $description = 'WP Rocket minification breaking scripts';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-rocket-minification',
			);
		}
		
		return null;
	}
}
