<?php
/**
 * Flatsome Theme Catalog Mode Diagnostic
 *
 * Flatsome Theme Catalog Mode needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1322.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flatsome Theme Catalog Mode Diagnostic Class
 *
 * @since 1.1322.0000
 */
class Diagnostic_FlatsomeThemeCatalogMode extends Diagnostic_Base {

	protected static $slug = 'flatsome-theme-catalog-mode';
	protected static $title = 'Flatsome Theme Catalog Mode';
	protected static $description = 'Flatsome Theme Catalog Mode needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Flatsome theme
		$theme = wp_get_theme();
		if ( 'Flatsome' !== $theme->get( 'Name' ) && 'Flatsome' !== $theme->get_template() ) {
			return null;
		}
		
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Catalog mode enabled
		$catalog_mode = get_theme_mod( 'catalog_mode', 0 );
		if ( ! $catalog_mode ) {
			return null;
		}
		
		// Check 2: Price visibility
		$hide_prices = get_theme_mod( 'catalog_mode_hide_prices', 0 );
		if ( $hide_prices ) {
			$issues[] = __( 'Prices hidden (SEO impact, rich snippets lost)', 'wpshadow' );
		}
		
		// Check 3: Inquiry form availability
		$inquiry_form = get_theme_mod( 'catalog_mode_inquiry_form', '' );
		if ( empty( $inquiry_form ) ) {
			$issues[] = __( 'No inquiry form (lost leads)', 'wpshadow' );
		}
		
		// Check 4: Cart functionality
		$disable_cart = get_theme_mod( 'catalog_mode_disable_cart', 1 );
		if ( $disable_cart ) {
			$issues[] = __( 'Cart disabled (no shopping list)', 'wpshadow' );
		}
		
		// Check 5: Button customization
		$button_text = get_theme_mod( 'catalog_mode_button_text', '' );
		if ( empty( $button_text ) ) {
			$issues[] = __( 'Add to cart button not customized (confusing UX)', 'wpshadow' );
		}
		
		// Check 6: Role-based catalog mode
		$per_role = get_theme_mod( 'catalog_mode_per_role', 0 );
		if ( ! $per_role ) {
			$issues[] = __( 'Catalog mode applies to all users (B2B/B2C flexibility lost)', 'wpshadow' );
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
				/* translators: %s: list of catalog mode issues */
				__( 'Flatsome catalog mode has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/flatsome-theme-catalog-mode',
		);
	}
}
