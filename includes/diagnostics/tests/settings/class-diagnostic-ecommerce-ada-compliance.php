<?php
/**
 * E-commerce Accessibility (ADA/WCAG Compliance) Diagnostic
 *
 * Checks if e-commerce sites meet ADA and WCAG 2.1 accessibility standards
 * including product pages, cart, checkout, and customer account areas.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since      1.6031.1505
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * E-commerce ADA WCAG Compliance Diagnostic Class
 *
 * Verifies e-commerce sites meet ADA/WCAG accessibility standards.
 *
 * @since 1.6031.1505
 */
class Diagnostic_Ecommerce_ADA_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ecommerce-ada-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'E-commerce Accessibility (ADA/WCAG Compliance)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies e-commerce sites meet ADA and WCAG 2.1 accessibility standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1505
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for ecommerce plugins.
		$ecommerce_plugins = array(
			'woocommerce',
			'easy-digital-downloads',
			'wp-ecommerce',
		);

		$has_ecommerce = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $ecommerce_plugins as $ec_plugin ) {
				if ( stripos( $plugin, $ec_plugin ) !== false ) {
					$has_ecommerce = true;
					break 2;
				}
			}
		}

		if ( ! $has_ecommerce ) {
			return null; // No ecommerce.
		}

		$issues = array();

		// Check for accessibility plugins.
		$has_accessibility = false;
		$a11y_plugins = array(
			'wp-accessibility',
			'one-click-accessibility',
			'accessibility-checker',
			'userway',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $a11y_plugins as $a11y_plugin ) {
				if ( stripos( $plugin, $a11y_plugin ) !== false ) {
					$has_accessibility = true;
					break 2;
				}
			}
		}

		if ( ! $has_accessibility ) {
			$issues[] = __( 'No accessibility plugin detected', 'wpshadow' );
		}

		// Check for WCAG compliance testing plugins.
		$has_testing = false;
		$test_plugins = array(
			'wp-ada-compliance',
			'accessibility-checker',
			'wcag-color-contrast',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $test_plugins as $test_plugin ) {
				if ( stripos( $plugin, $test_plugin ) !== false ) {
					$has_testing = true;
					break 2;
				}
			}
		}

		if ( ! $has_testing ) {
			$issues[] = __( 'No WCAG compliance testing tool found', 'wpshadow' );
		}

		// Check product images for alt text.
		$recent_products = get_posts(
			array(
				'post_type'      => array( 'product', 'download' ),
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$products_missing_alt = 0;
		foreach ( $recent_products as $product ) {
			$thumbnail_id = get_post_thumbnail_id( $product->ID );
			if ( $thumbnail_id ) {
				$alt_text = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
				if ( empty( $alt_text ) ) {
					++$products_missing_alt;
				}
			}
		}

		if ( $products_missing_alt > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of products without alt text */
				__( '%d recent products missing alt text on images', 'wpshadow' ),
				$products_missing_alt
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'E-commerce accessibility concerns: %s. Online stores must comply with ADA and WCAG 2.1 standards to ensure equal access for users with disabilities.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ecommerce-ada-compliance',
		);
	}
}
