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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-pdf-invoices-generation',
			);
		}
		
		return null;
	}
}
