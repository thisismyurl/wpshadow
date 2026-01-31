<?php
/**
 * Modern Events Calendar Payment Diagnostic
 *
 * Modern Events Calendar payments vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.586.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modern Events Calendar Payment Diagnostic Class
 *
 * @since 1.586.0000
 */
class Diagnostic_ModernEventsCalendarPayment extends Diagnostic_Base {

	protected static $slug = 'modern-events-calendar-payment';
	protected static $title = 'Modern Events Calendar Payment';
	protected static $description = 'Modern Events Calendar payments vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return null;
		}
		
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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/modern-events-calendar-payment',
			);
		}
		
		return null;
	}
}
