<?php
/**
 * Diagnostic: Social Proof Elements
 *
 * Tests whether the site prominently displays trust indicators that increase
 * conversion rates.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4533
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Proof Elements Diagnostic
 *
 * Checks for testimonials, reviews, trust badges, customer counts, and other
 * social proof that increases conversions by 15-25%.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Social_Proof extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'displays-social-proof-elements';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Proof Elements';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site displays trust indicators prominently';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for social proof implementation.
	 *
	 * Detects testimonials, reviews, trust badges, and social proof plugins.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		$social_proof_indicators = 0;

		// Check for testimonial plugins.
		$testimonial_plugins = array(
			'strong-testimonials/strong-testimonials.php',
			'testimonial-rotator/testimonial-rotator.php',
			'easy-testimonials/easy-testimonials.php',
		);

		foreach ( $testimonial_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				++$social_proof_indicators;
			}
		}

		// Check for review systems.
		if ( class_exists( 'WooCommerce' ) ) {
			$reviews_enabled = get_option( 'woocommerce_enable_reviews', 'yes' );
			if ( 'yes' === $reviews_enabled ) {
				++$social_proof_indicators;
			}
		}

		// Check for social proof notification plugins.
		$notification_plugins = array(
			'notification/notification.php',
			'wp-notification-bars/wp-notification-bars.php',
			'fomo/fomo.php',
		);

		foreach ( $notification_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				++$social_proof_indicators;
			}
		}

		// Check for trust badge widgets in sidebars.
		$sidebars = wp_get_sidebars_widgets();
		foreach ( $sidebars as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as $widget ) {
					if ( strpos( $widget, 'text' ) !== false || strpos( $widget, 'image' ) !== false ) {
						++$social_proof_indicators;
						break 2; // Found widgets, exit both loops.
					}
				}
			}
		}

		// Check menus for "Testimonials" or "Reviews" pages.
		$menus = wp_get_nav_menus();
		foreach ( $menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu );
			if ( $menu_items ) {
				foreach ( $menu_items as $item ) {
					if ( preg_match( '/(testimonial|review|trust)/i', $item->title ) ) {
						++$social_proof_indicators;
						break 2;
					}
				}
			}
		}

		// Minimum 2 social proof indicators recommended.
		if ( $social_proof_indicators >= 2 ) {
			return null;
		}

		$threat_level = 40;
		if ( $social_proof_indicators === 0 ) {
			$threat_level = 50;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of indicators found */
				__(
					'Limited social proof implementation (found %d indicators). Social proof increases conversions by 15-25%%. Add testimonials, product reviews, customer counts, trust badges, or case studies to build credibility.',
					'wpshadow'
				),
				$social_proof_indicators
			),
			'severity'     => 'medium',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/social-proof-elements',
		);
	}
}
