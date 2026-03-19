<?php
/**
 * Wishlist Functionality Diagnostic
 *
 * Tests whether the site provides wishlist functionality that drives future conversions.
 * Wishlists increase return visits and enable marketing to high-intent customers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Offers_Wishlist_Functionality Class
 *
 * Diagnostic #10: Wishlist Functionality from Specialized & Emerging Success Habits.
 * Checks if the site provides wishlist/favorites features.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Offers_Wishlist_Functionality extends Diagnostic_Base {

	protected static $slug = 'offers-wishlist-functionality';
	protected static $title = 'Wishlist Functionality';
	protected static $description = 'Tests whether the site provides wishlist functionality that drives future conversions';
	protected static $family = 'ecommerce-optimization';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check WooCommerce active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check wishlist plugins.
		$wishlist_plugins = array(
			'yith-woocommerce-wishlist/init.php',
			'ti-woocommerce-wishlist/ti-woocommerce-wishlist.php',
			'woocommerce-wishlists/woocommerce-wishlists.php',
		);

		$has_wishlist_plugin = false;
		foreach ( $wishlist_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_wishlist_plugin = true;
				$score += 2;
				$score_details[] = __( '✓ Wishlist plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_wishlist_plugin ) {
			$score_details[]   = __( '✗ No wishlist plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install YITH WooCommerce Wishlist or TI Wishlist plugin', 'wpshadow' );
		}

		// Check wishlist page.
		$wishlist_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'wishlist favorites saved',
			)
		);

		if ( ! empty( $wishlist_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Wishlist/favorites page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No wishlist page found', 'wpshadow' );
			$recommendations[] = __( 'Create a wishlist page where customers can save items for later', 'wpshadow' );
		}

		// Check wishlist button/icon visibility.
		global $wp_scripts;
		$has_wishlist_scripts = false;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( stripos( $handle, 'wishlist' ) !== false || stripos( $handle, 'favorite' ) !== false ) {
					$has_wishlist_scripts = true;
					break;
				}
			}
		}

		if ( $has_wishlist_scripts || $has_wishlist_plugin ) {
			++$score;
			$score_details[] = __( '✓ Wishlist interface detected', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No wishlist interface found', 'wpshadow' );
			$recommendations[] = __( 'Add prominent "Add to Wishlist" buttons on product pages', 'wpshadow' );
		}

		// Check wishlist sharing/gift registry features.
		$sharing_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'share wishlist registry gift list',
			)
		);

		if ( ! empty( $sharing_content ) ) {
			++$score;
			$score_details[] = __( '✓ Wishlist sharing features present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No wishlist sharing capability', 'wpshadow' );
			$recommendations[] = __( 'Enable wishlist sharing for gift registries and social shopping', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 20;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Wishlist functionality score: %d%%. Wishlists increase return visits by 45%% and enable targeted remarketing to high-intent shoppers. 40%% of wishlist items convert within 30 days, and wishlist users have 3x higher lifetime value.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/wishlist-functionality',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Wishlists capture purchase intent and provide valuable data for personalized marketing campaigns.', 'wpshadow' ),
		);
	}
}
