<?php
/**
 * Event Tickets Security Diagnostic
 *
 * Event tickets system insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.568.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Tickets Security Diagnostic Class
 *
 * @since 1.568.0000
 */
class Diagnostic_EventTicketsSecurity extends Diagnostic_Base {

	protected static $slug = 'event-tickets-security';
	protected static $title = 'Event Tickets Security';
	protected static $description = 'Event tickets system insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Tickets__Main' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Ticket validation
		$validation = get_option( 'tribe_ticket_validation_enabled', 0 );
		if ( ! $validation ) {
			$issues[] = 'Ticket validation not enabled';
		}

		// Check 2: Barcode security
		$barcode = get_option( 'tribe_barcode_encryption_enabled', 0 );
		if ( ! $barcode ) {
			$issues[] = 'Barcode encryption not enabled';
		}

		// Check 3: Ticket holder data protection
		$data_protection = get_option( 'tribe_ticket_holder_data_protection', 0 );
		if ( ! $data_protection ) {
			$issues[] = 'Ticket holder data not protected';
		}

		// Check 4: Fraud detection
		$fraud = get_option( 'tribe_ticket_fraud_detection', 0 );
		if ( ! $fraud ) {
			$issues[] = 'Fraud detection not enabled';
		}

		// Check 5: Check-in logging
		$logging = get_option( 'tribe_checkin_logging_enabled', 0 );
		if ( ! $logging ) {
			$issues[] = 'Check-in logging not enabled';
		}

		// Check 6: Access restriction
		$access = get_option( 'tribe_ticket_access_restriction', 0 );
		if ( ! $access ) {
			$issues[] = 'Ticket access restriction not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 60;
			$threat_multiplier = 6;
			$max_threat = 90;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d ticket security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/event-tickets-security',
			);
		}

		return null;
	}
}
