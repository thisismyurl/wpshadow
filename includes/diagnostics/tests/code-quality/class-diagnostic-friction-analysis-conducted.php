<?php
/**
 * Friction Analysis Conducted Diagnostic
 *
 * Tests whether the site has identified and removed friction points in conversion flows.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Friction Analysis Conducted Diagnostic Class
 *
 * Reducing friction improves conversions by 30-50%. Analyze forms, checkout,
 * navigation, load times, and trust signals regularly.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Friction_Analysis_Conducted extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'friction-analysis-conducted';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Friction Analysis Conducted';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site has identified and removed friction points in conversion flows';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues         = array();
		$friction_score = 0;
		$max_score      = 7;

		// Check form length.
		$form_friction = self::check_form_friction();
		if ( $form_friction ) {
			++$friction_score;
		} else {
			$issues[] = __( 'Forms may have too many fields causing friction', 'wpshadow' );
		}

		// Check checkout steps.
		$checkout_friction = self::check_checkout_friction();
		if ( $checkout_friction ) {
			++$friction_score;
		} else {
			$issues[] = __( 'Checkout process may have unnecessary steps', 'wpshadow' );
		}

		// Check site speed.
		$speed_friction = self::check_speed_friction();
		if ( $speed_friction ) {
			++$friction_score;
		} else {
			$issues[] = __( 'Slow page speed creates friction (no caching detected)', 'wpshadow' );
		}

		// Check trust signals.
		$trust_friction = self::check_trust_friction();
		if ( $trust_friction ) {
			++$friction_score;
		} else {
			$issues[] = __( 'Missing trust signals (security badges, testimonials)', 'wpshadow' );
		}

		// Check navigation clarity.
		$navigation_friction = self::check_navigation_friction();
		if ( $navigation_friction ) {
			++$friction_score;
		} else {
			$issues[] = __( 'Complex navigation may confuse users', 'wpshadow' );
		}

		// Check required account creation.
		$account_friction = self::check_account_friction();
		if ( $account_friction ) {
			++$friction_score;
		} else {
			$issues[] = __( 'Forced account creation adds friction (consider guest checkout)', 'wpshadow' );
		}

		// Check mobile experience.
		$mobile_friction = self::check_mobile_friction();
		if ( $mobile_friction ) {
			++$friction_score;
		} else {
			$issues[] = __( 'Mobile experience may have friction points', 'wpshadow' );
		}

		// Determine severity based on friction reduction.
		$friction_percentage = ( $friction_score / $max_score ) * 100;

		if ( $friction_percentage < 40 ) {
			$severity     = 'medium';
			$threat_level = 35;
		} elseif ( $friction_percentage < 70 ) {
			$severity     = 'low';
			$threat_level = 25;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Friction reduction percentage */
				__( 'Friction reduction at %d%%. ', 'wpshadow' ),
				(int) $friction_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Removing friction improves conversions 30-50%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/friction-analysis-conducted',
			);
		}

		return null;
	}

	/**
	 * Check form friction.
	 *
	 * @since 1.6093.1200
	 * @return bool True if forms are optimized, false otherwise.
	 */
	private static function check_form_friction() {
		// Check for form optimization plugins.
		if ( is_plugin_active( 'wpforms-lite/wpforms.php' ) ||
			is_plugin_active( 'formidable/formidable.php' ) ) {
			return true; // Assume properly configured.
		}

		return apply_filters( 'wpshadow_forms_optimized', false );
	}

	/**
	 * Check checkout friction.
	 *
	 * @since 1.6093.1200
	 * @return bool True if checkout is optimized, false otherwise.
	 */
	private static function check_checkout_friction() {
		if ( class_exists( 'WooCommerce' ) ) {
			// Check for one-page/multi-step checkout.
			if ( is_plugin_active( 'woocommerce-one-page-checkout/woocommerce-one-page-checkout.php' ) ||
				is_plugin_active( 'woocommerce-multistep-checkout/woocommerce-multistep-checkout.php' ) ) {
				return true;
			}

			// Check if guest checkout is enabled.
			$guest_checkout = get_option( 'woocommerce_enable_guest_checkout', 'no' );
			return ( 'yes' === $guest_checkout );
		}

		return apply_filters( 'wpshadow_checkout_optimized', true );
	}

	/**
	 * Check speed friction.
	 *
	 * @since 1.6093.1200
	 * @return bool True if speed is optimized, false otherwise.
	 */
	private static function check_speed_friction() {
		// Check for caching plugins.
		$caching_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'cache-enabler/cache-enabler.php',
		);

		foreach ( $caching_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check trust friction.
	 *
	 * @since 1.6093.1200
	 * @return bool True if trust signals exist, false otherwise.
	 */
	private static function check_trust_friction() {
		$trust_keywords = array( 'secure', 'ssl', 'guarantee', 'testimonial', 'review' );
		$trust_found    = 0;

		foreach ( $trust_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				++$trust_found;
			}
		}

		return ( $trust_found >= 3 );
	}

	/**
	 * Check navigation friction.
	 *
	 * @since 1.6093.1200
	 * @return bool True if navigation is clear, false otherwise.
	 */
	private static function check_navigation_friction() {
		// Check menu size.
		$menus = get_nav_menu_locations();
		if ( ! empty( $menus ) ) {
			foreach ( $menus as $location => $menu_id ) {
				if ( $menu_id ) {
					$menu_items = wp_get_nav_menu_items( $menu_id );
					// Too many menu items = friction.
					if ( count( $menu_items ) <= 7 ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check account friction.
	 *
	 * @since 1.6093.1200
	 * @return bool True if guest checkout allowed, false otherwise.
	 */
	private static function check_account_friction() {
		if ( class_exists( 'WooCommerce' ) ) {
			$guest_checkout = get_option( 'woocommerce_enable_guest_checkout', 'no' );
			return ( 'yes' === $guest_checkout );
		}

		return apply_filters( 'wpshadow_allows_guest_actions', true );
	}

	/**
	 * Check mobile friction.
	 *
	 * @since 1.6093.1200
	 * @return bool True if mobile optimized, false otherwise.
	 */
	private static function check_mobile_friction() {
		// Check for AMP or mobile optimization.
		if ( is_plugin_active( 'amp/amp.php' ) ||
			wp_is_mobile() ) {
			return true;
		}

		// Check if theme is responsive.
		return current_theme_supports( 'responsive-embeds' );
	}
}
