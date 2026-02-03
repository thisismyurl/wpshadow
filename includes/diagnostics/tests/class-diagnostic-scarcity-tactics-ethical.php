<?php
/**
 * Scarcity Tactics Ethical Diagnostic
 *
 * Tests whether the site uses ethical scarcity tactics (limited inventory, time-sensitive offers) to encourage decisions.
 *
 * @since   1.26034.0245
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scarcity Tactics Ethical Diagnostic Class
 *
 * Ethical scarcity increases conversions by 226%, but must be authentic.
 * Fake scarcity damages trust and brand reputation.
 *
 * @since 1.26034.0245
 */
class Diagnostic_Scarcity_Tactics_Ethical extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'scarcity-tactics-ethical';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Scarcity Tactics Ethical';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses ethical scarcity tactics to encourage decisions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0245
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$scarcity_score = 0;
		$max_score = 6;

		// Check for inventory display.
		$inventory = self::check_inventory_display();
		if ( $inventory ) {
			$scarcity_score++;
		} else {
			$issues[] = __( 'No real-time inventory counts shown (low stock alerts)', 'wpshadow' );
		}

		// Check for limited time offers.
		$time_offers = self::check_time_offers();
		if ( $time_offers ) {
			$scarcity_score++;
		} else {
			$issues[] = __( 'No time-limited offers with clear deadlines', 'wpshadow' );
		}

		// Check for sale event scarcity.
		$sale_scarcity = self::check_sale_scarcity();
		if ( $sale_scarcity ) {
			$scarcity_score++;
		} else {
			$issues[] = __( 'Sales lack urgency or deadline messaging', 'wpshadow' );
		}

		// Check for limited edition products.
		$limited_edition = self::check_limited_edition();
		if ( $limited_edition ) {
			$scarcity_score++;
		} else {
			$issues[] = __( 'No limited edition or exclusive products', 'wpshadow' );
		}

		// Check for enrollment limits.
		$enrollment_limits = self::check_enrollment_limits();
		if ( $enrollment_limits ) {
			$scarcity_score++;
		} else {
			$issues[] = __( 'Courses/programs lack enrollment caps or cohort limits', 'wpshadow' );
		}

		// Check for authenticity indicators.
		$authentic = self::check_authenticity();
		if ( $authentic ) {
			$scarcity_score++;
		} else {
			$issues[] = __( 'Scarcity claims lack authenticity indicators (real-time updates)', 'wpshadow' );
		}

		// Determine severity based on scarcity implementation.
		$scarcity_percentage = ( $scarcity_score / $max_score ) * 100;

		if ( $scarcity_percentage < 30 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $scarcity_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Scarcity implementation percentage */
				__( 'Ethical scarcity at %d%%. ', 'wpshadow' ),
				(int) $scarcity_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Authentic scarcity increases conversions 226%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/scarcity-tactics-ethical',
			);
		}

		return null;
	}

	/**
	 * Check inventory display.
	 *
	 * @since  1.26034.0245
	 * @return bool True if inventory displayed, false otherwise.
	 */
	private static function check_inventory_display() {
		// WooCommerce low stock notifications.
		if ( class_exists( 'WooCommerce' ) ) {
			$low_stock_threshold = get_option( 'woocommerce_notify_low_stock_amount', 0 );
			if ( $low_stock_threshold > 0 ) {
				return true;
			}
		}

		// Check for stock display plugins.
		if ( is_plugin_active( 'woocommerce-stock-manager/woocommerce-stock-manager.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_displays_inventory', false );
	}

	/**
	 * Check time offers.
	 *
	 * @since  1.26034.0245
	 * @return bool True if time offers exist, false otherwise.
	 */
	private static function check_time_offers() {
		$keywords = array( 'limited time', 'expires', 'ends soon', 'flash sale', 'today only' );
		$found = 0;

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				$found++;
			}
		}

		return ( $found >= 2 );
	}

	/**
	 * Check sale scarcity.
	 *
	 * @since  1.26034.0245
	 * @return bool True if sales have urgency, false otherwise.
	 */
	private static function check_sale_scarcity() {
		// WooCommerce sale prices.
		if ( class_exists( 'WooCommerce' ) ) {
			$products = get_posts(
				array(
					'post_type'      => 'product',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'     => '_sale_price_dates_to',
							'value'   => time(),
							'compare' => '>',
							'type'    => 'NUMERIC',
						),
					),
				)
			);

			if ( ! empty( $products ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_sale_scarcity', false );
	}

	/**
	 * Check limited edition.
	 *
	 * @since  1.26034.0245
	 * @return bool True if limited products exist, false otherwise.
	 */
	private static function check_limited_edition() {
		$keywords = array( 'limited edition', 'exclusive', 'rare', 'collector' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'product',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check enrollment limits.
	 *
	 * @since  1.26034.0245
	 * @return bool True if enrollment limits exist, false otherwise.
	 */
	private static function check_enrollment_limits() {
		// LMS plugins can set enrollment caps.
		if ( is_plugin_active( 'learndash/learndash.php' ) ||
			 is_plugin_active( 'lifterlms/lifterlms.php' ) ) {
			return true;
		}

		// Check for enrollment content.
		$query = new \WP_Query(
			array(
				's'              => 'enrollment limited spots remaining',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check authenticity.
	 *
	 * @since  1.26034.0245
	 * @return bool True if scarcity is authentic, false otherwise.
	 */
	private static function check_authenticity() {
		// Real-time stock tracking.
		if ( class_exists( 'WooCommerce' ) ) {
			$manage_stock = get_option( 'woocommerce_manage_stock', 'no' );
			if ( 'yes' === $manage_stock ) {
				return true;
			}
		}

		// Timer plugins with real deadlines.
		if ( is_plugin_active( 'countdown-builder/init.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_scarcity_authentic', false );
	}
}
