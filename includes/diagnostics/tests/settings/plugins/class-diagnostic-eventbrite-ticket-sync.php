<?php
/**
 * Eventbrite Ticket Sync Diagnostic
 *
 * Eventbrite sync causing conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.581.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Eventbrite Ticket Sync Diagnostic Class
 *
 * @since 1.581.0000
 */
class Diagnostic_EventbriteTicketSync extends Diagnostic_Base {

	protected static $slug = 'eventbrite-ticket-sync';
	protected static $title = 'Eventbrite Ticket Sync';
	protected static $description = 'Eventbrite sync causing conflicts';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Eventbrite_API' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/eventbrite-ticket-sync',
			);
		}
		
		return null;
	}
}
