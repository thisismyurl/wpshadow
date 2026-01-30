<?php
/**
 * Woocommerce Pdf Invoices Generation Diagnostic
 *
 * Woocommerce Pdf Invoices Generation issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.662.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Pdf Invoices Generation Diagnostic Class
 *
 * @since 1.662.0000
 */
class Diagnostic_WoocommercePdfInvoicesGeneration extends Diagnostic_Base {

	protected static $slug = 'woocommerce-pdf-invoices-generation';
	protected static $title = 'Woocommerce Pdf Invoices Generation';
	protected static $description = 'Woocommerce Pdf Invoices Generation issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: PDF generation enabled
		$pdf_enabled = get_option( 'wc_pdf_invoices_enabled', 0 );
		if ( ! $pdf_enabled ) {
			$issues[] = 'PDF invoice generation not enabled';
		}
		
		// Check 2: Invoice numbering configured
		$numbering = get_option( 'wc_pdf_invoices_numbering', '' );
		if ( empty( $numbering ) ) {
			$issues[] = 'Invoice numbering not configured';
		}
		
		// Check 3: Storage location set
		$storage = get_option( 'wc_pdf_invoices_storage_location', '' );
		if ( empty( $storage ) ) {
			$issues[] = 'PDF storage location not set';
		}
		
		// Check 4: Auto-generation on order
		$auto_gen = get_option( 'wc_pdf_invoices_auto_generate', 0 );
		if ( ! $auto_gen ) {
			$issues[] = 'Automatic PDF generation on order not enabled';
		}
		
		// Check 5: Email attachment enabled
		$email_attach = get_option( 'wc_pdf_invoices_email_attach', 0 );
		if ( ! $email_attach ) {
			$issues[] = 'PDF email attachment not enabled';
		}
		
		// Check 6: Batch processing configured
		$batch_processing = get_option( 'wc_pdf_invoices_batch_processing', 0 );
		if ( ! $batch_processing ) {
			$issues[] = 'Batch processing not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d PDF invoice generation issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-pdf-invoices-generation',
			);
		}
		
		return null;
	}
}
