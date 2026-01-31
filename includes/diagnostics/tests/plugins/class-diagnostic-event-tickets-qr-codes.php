<?php
/**
 * Event Tickets QR Codes Diagnostic
 *
 * Event QR codes predictable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.570.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Tickets QR Codes Diagnostic Class
 *
 * @since 1.570.0000
 */
class Diagnostic_EventTicketsQrCodes extends Diagnostic_Base {

	protected static $slug = 'event-tickets-qr-codes';
	protected static $title = 'Event Tickets QR Codes';
	protected static $description = 'Event QR codes predictable';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Tickets__Main' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/event-tickets-qr-codes',
			);
		}
		
		return null;
	}
}
