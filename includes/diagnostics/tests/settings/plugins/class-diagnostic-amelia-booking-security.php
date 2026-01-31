<?php
/**
 * Amelia Booking Security Diagnostic
 *
 * Amelia booking data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.464.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Booking Security Diagnostic Class
 *
 * @since 1.464.0000
 */
class Diagnostic_AmeliaBookingSecurity extends Diagnostic_Base {

	protected static $slug = 'amelia-booking-security';
	protected static $title = 'Amelia Booking Security';
	protected static $description = 'Amelia booking data exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/amelia-booking-security',
			);
		}
		
		return null;
	}
}
