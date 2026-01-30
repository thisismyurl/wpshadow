<?php
/**
 * Event Ticket PDF Generation Diagnostic
 *
 * Event PDF generation slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.595.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Ticket PDF Generation Diagnostic Class
 *
 * @since 1.595.0000
 */
class Diagnostic_EventTicketPdfGeneration extends Diagnostic_Base {

	protected static $slug = 'event-ticket-pdf-generation';
	protected static $title = 'Event Ticket PDF Generation';
	protected static $description = 'Event PDF generation slow';
	protected static $family = 'performance';

	public static function check() {
		// Check for event ticketing plugins
		$has_ticketing = class_exists( 'Tribe__Tickets__Main' ) ||
		                 class_exists( 'Event_Tickets_PDF' ) ||
		                 function_exists( 'tribe_tickets_get_ticket_provider' );
		
		if ( ! $has_ticketing ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: PDF generation enabled
		$pdf_enabled = get_option( 'tribe_tickets_pdf_enabled', false );
		if ( ! $pdf_enabled ) {
			return null;
		}
		
		// Check 2: PDF library available
		if ( ! class_exists( 'TCPDF' ) && ! class_exists( 'Dompdf\Dompdf' ) && ! class_exists( 'mPDF' ) ) {
			$issues[] = __( 'No PDF library available (generation may fail)', 'wpshadow' );
		}
		
		// Check 3: Memory limit
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		if ( $memory_limit < ( 256 * 1024 * 1024 ) ) { // 256MB
			$issues[] = sprintf( __( 'Memory limit: %s (PDF generation may fail)', 'wpshadow' ), size_format( $memory_limit ) );
		}
		
		// Check 4: PDF caching
		$cache_pdfs = get_option( 'tribe_tickets_cache_pdfs', false );
		if ( ! $cache_pdfs ) {
			$issues[] = __( 'PDF caching disabled (regenerated every download)', 'wpshadow' );
		}
		
		// Check 5: Font directory writable
		$font_dir = WP_CONTENT_DIR . '/tcpdf/fonts/';
		if ( ! wp_is_writable( WP_CONTENT_DIR ) ) {
			$issues[] = __( 'Font directory not writable (custom fonts fail)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 58;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 52;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of PDF generation issues */
				__( 'Event ticket PDF generation has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/event-ticket-pdf-generation',
		);
	}
}
