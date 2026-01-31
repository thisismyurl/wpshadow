<?php
/**
 * The Events Calendar Ticketing Diagnostic
 *
 * Event ticketing not properly secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.271.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Events Calendar Ticketing Diagnostic Class
 *
 * @since 1.271.0000
 */
class Diagnostic_EventsCalendarTicketSecurity extends Diagnostic_Base {

	protected static $slug = 'events-calendar-ticket-security';
	protected static $title = 'The Events Calendar Ticketing';
	protected static $description = 'Event ticketing not properly secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify ticket QR codes are enabled for validation
		$qr_enabled = get_option( 'tribe_tickets_qr_enabled', false );
		if ( ! $qr_enabled && class_exists( 'Tribe__Tickets__Main' ) ) {
			$issues[] = 'QR code validation not enabled for tickets';
		}

		// Check 2: Check if tickets require SSL for checkout
		if ( ! is_ssl() && class_exists( 'Tribe__Tickets__Commerce__PayPal__Main' ) ) {
			$issues[] = 'SSL not configured for ticket purchases';
		}

		// Check 3: Verify ticket PDF security
		$pdf_security = get_option( 'tribe_tickets_pdf_security', false );
		if ( ! $pdf_security && class_exists( 'Tribe__Tickets_Plus__Main' ) ) {
			$issues[] = 'PDF ticket security not enabled';
		}

		// Check 4: Check for ticket duplicate prevention
		$prevent_duplicate = get_option( 'tribe_tickets_prevent_duplicate_purchases', false );
		if ( ! $prevent_duplicate ) {
			$issues[] = 'Duplicate ticket purchase prevention not enabled';
		}

		// Check 5: Verify attendee information collection
		$collect_info = get_option( 'tribe_tickets_collect_attendee_info', false );
		if ( ! $collect_info ) {
			$issues[] = 'Attendee information collection not required';
		}

		// Check 6: Check for ticket stock management
		$args = array(
			'post_type'      => 'tribe_events',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'     => '_tribe_ticket_capacity',
					'compare' => 'EXISTS',
				),
			),
		);
		$ticket_events = get_posts( $args );
		if ( ! empty( $ticket_events ) ) {
			$stock_enabled = get_option( 'tribe_tickets_stock_enabled', false );
			if ( ! $stock_enabled ) {
				$issues[] = 'Ticket stock management not properly configured';
			}
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Events Calendar ticket security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-ticket-security',
			);
		}

		return null;
	}
}
