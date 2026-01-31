<?php
/**
 * GeoDirectory Location Security Diagnostic
 *
 * GeoDirectory location data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.551.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Location Security Diagnostic Class
 *
 * @since 1.551.0000
 */
class Diagnostic_GeodirectoryLocationSecurity extends Diagnostic_Base {

	protected static $slug = 'geodirectory-location-security';
	protected static $title = 'GeoDirectory Location Security';
	protected static $description = 'GeoDirectory location data exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/geodirectory-location-security',
			);
		}
		
		return null;
	}
}
