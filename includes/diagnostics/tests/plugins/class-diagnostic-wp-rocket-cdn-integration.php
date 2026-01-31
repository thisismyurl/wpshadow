<?php
/**
 * WP Rocket CDN Integration Diagnostic
 *
 * WP Rocket CDN not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.441.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Rocket CDN Integration Diagnostic Class
 *
 * @since 1.441.0000
 */
class Diagnostic_WpRocketCdnIntegration extends Diagnostic_Base {

	protected static $slug = 'wp-rocket-cdn-integration';
	protected static $title = 'WP Rocket CDN Integration';
	protected static $description = 'WP Rocket CDN not configured';
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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-rocket-cdn-integration',
			);
		}
		
		return null;
	}
}
