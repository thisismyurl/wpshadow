<?php
/**
 * Woocommerce Pdf Invoices Templates Diagnostic
 *
 * Woocommerce Pdf Invoices Templates issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.664.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Pdf Invoices Templates Diagnostic Class
 *
 * @since 1.664.0000
 */
class Diagnostic_WoocommercePdfInvoicesTemplates extends Diagnostic_Base {

	protected static $slug = 'woocommerce-pdf-invoices-templates';
	protected static $title = 'Woocommerce Pdf Invoices Templates';
	protected static $description = 'Woocommerce Pdf Invoices Templates issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check for PDF invoice plugins
		$has_pdf_invoices = class_exists( 'WC_Order_Factory' ) && (
			defined( 'WC_PDF_INVOICES_VERSION' ) ||
			class_exists( 'WPO_WCPDF' ) ||
			class_exists( 'BEWPI' )
		);
		
		if ( ! $has_pdf_invoices ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Custom template exists
		$template_dir = get_stylesheet_directory() . '/woocommerce-pdf-invoices/';
		if ( ! is_dir( $template_dir ) ) {
			$issues[] = __( 'Using default templates (not customized for brand)', 'wpshadow' );
		}
		
		// Check 2: Invoice numbering
		$invoice_number_format = get_option( 'wpo_wcpdf_invoice_number_formatting', '' );
		if ( empty( $invoice_number_format ) ) {
			$issues[] = __( 'Sequential numbering (gaps expose order volume)', 'wpshadow' );
		}
		
		// Check 3: Invoice storage location
		$upload_dir = wp_upload_dir();
		$invoice_path = $upload_dir['basedir'] . '/wpo_wcpdf/';
		
		if ( is_dir( $invoice_path ) ) {
			// Check if directory is protected
			$htaccess = $invoice_path . '.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				$issues[] = __( 'Invoice directory not protected (public access)', 'wpshadow' );
			}
		}
		
		// Check 4: Company logo
		$logo = get_option( 'wpo_wcpdf_header_logo', '' );
		if ( empty( $logo ) ) {
			$issues[] = __( 'No company logo (unprofessional invoices)', 'wpshadow' );
		}
		
		// Check 5: Tax calculations display
		$display_tax = get_option( 'wpo_wcpdf_display_tax', 'yes' );
		if ( 'no' === $display_tax ) {
			$issues[] = __( 'Tax details hidden (compliance issue)', 'wpshadow' );
		}
		
		// Check 6: Automatic invoice generation
		$auto_generate = get_option( 'wpo_wcpdf_attach_to_email', array() );
		if ( empty( $auto_generate ) ) {
			$issues[] = __( 'Manual invoice generation (customer inconvenience)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of PDF invoice issues */
				__( 'WooCommerce PDF invoices have %d template issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-pdf-invoices-templates',
		);
	}
}
