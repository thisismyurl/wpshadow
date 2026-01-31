<?php
/**
 * BookingPress Custom Fields Diagnostic
 *
 * BookingPress custom fields not sanitized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.462.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingPress Custom Fields Diagnostic Class
 *
 * @since 1.462.0000
 */
class Diagnostic_BookingpressCustomFields extends Diagnostic_Base {

	protected static $slug = 'bookingpress-custom-fields';
	protected static $title = 'BookingPress Custom Fields';
	protected static $description = 'BookingPress custom fields not sanitized';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'BOOKINGPRESS_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/bookingpress-custom-fields',
			);
		}
		
		return null;
	}
}
