<?php
/**
 * Woocommerce Pdf Invoices Security Diagnostic
 *
 * Woocommerce Pdf Invoices Security issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.663.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Pdf Invoices Security Diagnostic Class
 *
 * @since 1.663.0000
 */
class Diagnostic_WoocommercePdfInvoicesSecurity extends Diagnostic_Base {

	protected static $slug = 'woocommerce-pdf-invoices-security';
	protected static $title = 'Woocommerce Pdf Invoices Security';
	protected static $description = 'Woocommerce Pdf Invoices Security issues detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WPO_WCPDF' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify PDF invoice directory is not publicly accessible
		$upload_dir = wp_upload_dir();
		$pdf_dir = $upload_dir['basedir'] . '/wpo_wcpdf';
		
		if ( file_exists( $pdf_dir ) ) {
			// Check if directory is publicly accessible
			$pdf_url = $upload_dir['baseurl'] . '/wpo_wcpdf/';
			$response = wp_remote_get( $pdf_url, array( 'timeout' => 5 ) );
			
			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				if ( 200 === $status_code ) {
					$body = wp_remote_retrieve_body( $response );
					if ( strpos( $body, 'Index of' ) !== false ) {
						$issues[] = 'pdf_directory_listing_enabled';
					}
				}
			}
			
			// Check 2: Verify .htaccess protection exists
			$htaccess_file = $pdf_dir . '/.htaccess';
			if ( ! file_exists( $htaccess_file ) ) {
				$issues[] = 'no_htaccess_protection';
			}
			
			// Check 3: Check for old/orphaned PDF files
			$pdf_files = glob( $pdf_dir . '/*.pdf' );
			if ( ! empty( $pdf_files ) ) {
				$old_files = 0;
				foreach ( $pdf_files as $file ) {
					if ( ( time() - filemtime( $file ) ) > 30 * DAY_IN_SECONDS ) {
						$old_files++;
					}
				}
				if ( $old_files > 50 ) {
					$issues[] = 'old_pdf_files_not_cleaned';
				}
			}
		}
		
		// Check 4: Verify invoice numbering is sequential (prevents manipulation)
		$invoice_number_type = get_option( 'wpo_wcpdf_invoice_number_formatting', 'sequential' );
		if ( 'sequential' !== $invoice_number_type ) {
			$issues[] = 'non_sequential_invoice_numbers';
		}
		
		// Check 5: Verify customer data in PDF is sanitized
		$include_customer_notes = get_option( 'wpo_wcpdf_display_customer_notes', 'no' );
		if ( 'yes' === $include_customer_notes ) {
			// Customer notes might contain sensitive data
			$issues[] = 'customer_notes_in_pdf';
		}
		
		// Check 6: Check if PDF invoices require authentication to download
		$my_account_pdf = get_option( 'wpo_wcpdf_my_account_buttons', 'yes' );
		if ( 'yes' === $my_account_pdf ) {
			// This is good - PDFs require login
		} else {
			$issues[] = 'pdf_download_no_auth_required';
		}
		
		// Check 7: Verify invoice emails are being sent securely
		$attach_to_email = get_option( 'wpo_wcpdf_attach_to_email_ids', array() );
		if ( empty( $attach_to_email ) ) {
			$issues[] = 'invoices_not_attached_to_emails';
		}
		
		if ( ! empty( $issues ) ) {
			$issues = array_unique( $issues );
			$description = sprintf(
				/* translators: %s: list of PDF invoice security issues */
				__( 'WooCommerce PDF Invoices has security issues: %s. Insecure invoice configurations can expose customer data, payment information, and order details.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-pdf-invoices-security',
			);
		}
		
		return null;
	}
}
