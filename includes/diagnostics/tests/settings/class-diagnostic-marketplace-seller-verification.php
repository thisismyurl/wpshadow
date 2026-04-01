<?php
/**
 * Marketplace Seller Verification and Vetting Diagnostic
 *
 * Checks if marketplace/multi-vendor sites implement proper seller verification,
 * vetting processes, and seller identity protection measures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Marketplace Seller Verification Diagnostic Class
 *
 * Verifies marketplace sites implement seller verification and vetting.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Marketplace_Seller_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'marketplace-seller-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Marketplace Seller Verification and Vetting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies marketplace sites implement proper seller verification and vetting processes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for marketplace/multi-vendor plugins.
		$marketplace_plugins = array(
			'dokan',
			'wcfm',
			'wc-vendors',
			'yith-multi-vendor',
			'marketpress',
		);

		$has_marketplace = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $marketplace_plugins as $mk_plugin ) {
				if ( stripos( $plugin, $mk_plugin ) !== false ) {
					$has_marketplace = true;
					break 2;
				}
			}
		}

		if ( ! $has_marketplace ) {
			return null; // Not a marketplace.
		}

		$issues = array();

		// Check for KYC/verification plugins.
		$has_kyc = false;
		$kyc_plugins = array(
			'kyc',
			'identity-verification',
			'vendor-verification',
			'seller-verification',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $kyc_plugins as $kyc_plugin ) {
				if ( stripos( $plugin, $kyc_plugin ) !== false ) {
					$has_kyc = true;
					break 2;
				}
			}
		}

		if ( ! $has_kyc ) {
			$issues[] = __( 'No seller verification/KYC plugin detected', 'wpshadow' );
		}

		// Check for seller review/moderation.
		$has_moderation = false;
		$mod_plugins = array(
			'seller-review',
			'vendor-review',
			'marketplace-moderation',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $mod_plugins as $mod_plugin ) {
				if ( stripos( $plugin, $mod_plugin ) !== false ) {
					$has_moderation = true;
					break 2;
				}
			}
		}

		if ( ! $has_moderation ) {
			$issues[] = __( 'No seller review/moderation system found', 'wpshadow' );
		}

		// Check for terms/agreement pages.
		$pages = get_pages();
		$has_seller_terms = false;

		foreach ( $pages as $page ) {
			if ( stripos( $page->post_title, 'seller terms' ) !== false ||
				stripos( $page->post_title, 'vendor agreement' ) !== false ||
				stripos( $page->post_content, 'seller agreement' ) !== false ) {
				$has_seller_terms = true;
				break;
			}
		}

		if ( ! $has_seller_terms ) {
			$issues[] = __( 'No seller terms/agreement page found', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Marketplace verification concerns: %s. Multi-vendor marketplaces should implement seller verification, KYC processes, and clear terms of service.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/marketplace-seller-verification?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
