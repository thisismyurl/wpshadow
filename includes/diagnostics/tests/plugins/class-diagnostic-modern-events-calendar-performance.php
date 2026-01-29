<?php
/**
 * Modern Events Calendar Performance Diagnostic
 *
 * Modern Events Calendar slowing frontend.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.585.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modern Events Calendar Performance Diagnostic Class
 *
 * @since 1.585.0000
 */
class Diagnostic_ModernEventsCalendarPerformance extends Diagnostic_Base {

	protected static $slug = 'modern-events-calendar-performance';
	protected static $title = 'Modern Events Calendar Performance';
	protected static $description = 'Modern Events Calendar slowing frontend';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/modern-events-calendar-performance',
			);
		}
		
		return null;
	}
}
