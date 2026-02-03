<?php
/**
 * Countdown Timers Strategic Diagnostic
 *
 * Tests whether the site uses countdown timers ethically to create genuine urgency.
 *
 * @since   1.26034.0230
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Countdown Timers Strategic Diagnostic Class
 *
 * Ethical countdown timers can increase conversions by 10-15% when tied to
 * genuine deadlines. Fake urgency damages trust and long-term customer value.
 *
 * @since 1.26034.0230
 */
class Diagnostic_Countdown_Timers_Strategic extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'countdown-timers-strategic';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Countdown Timers Strategic';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses countdown timers ethically to create genuine urgency';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$timer_score = 0;
		$max_score = 6;

		// Check for timer plugins.
		$timer_plugins = self::check_timer_plugins();
		if ( $timer_plugins ) {
			$timer_score++;
		} else {
			$issues[] = __( 'No countdown timer functionality installed', 'wpshadow' );
		}

		// Check for real deadlines/events.
		$real_deadlines = self::check_real_deadlines();
		if ( $real_deadlines ) {
			$timer_score++;
		} else {
			$issues[] = __( 'No genuine time-limited offers or events detected', 'wpshadow' );
		}

		// Check timer placement.
		$strategic_placement = self::check_timer_placement();
		if ( $strategic_placement ) {
			$timer_score++;
		} else {
			$issues[] = __( 'Timers not strategically placed on key conversion pages', 'wpshadow' );
		}

		// Check for transparent messaging.
		$transparency = self::check_transparency();
		if ( $transparency ) {
			$timer_score++;
		} else {
			$issues[] = __( 'Timer messaging lacks clarity about what happens when time expires', 'wpshadow' );
		}

		// Check for timer variety.
		$timer_variety = self::check_timer_variety();
		if ( $timer_variety ) {
			$timer_score++;
		} else {
			$issues[] = __( 'No variety in timer types for different scenarios', 'wpshadow' );
		}

		// Check mobile optimization.
		$mobile_optimized = self::check_mobile_optimization();
		if ( $mobile_optimized ) {
			$timer_score++;
		} else {
			$issues[] = __( 'Timers may not be optimized for mobile viewing', 'wpshadow' );
		}

		// Determine severity based on strategic timer implementation.
		$timer_percentage = ( $timer_score / $max_score ) * 100;

		if ( $timer_percentage < 30 ) {
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $timer_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Timer strategy percentage */
				__( 'Countdown timer strategy at %d%%. ', 'wpshadow' ),
				(int) $timer_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Strategic timers can increase conversions by 10-15%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/countdown-timers-strategic',
			);
		}

		return null;
	}

	/**
	 * Check for timer plugins.
	 *
	 * @since  1.26034.0230
	 * @return bool True if timer plugins exist, false otherwise.
	 */
	private static function check_timer_plugins() {
		$timer_plugins = array(
			'countdown-timer-ultimate/countdown-timer.php',
			'evergreen-countdown-timer/evergreen-countdown-timer.php',
			'wp-countdown-timer/wp-countdown-timer.php',
			'hurrytimer/hurrytimer.php',
		);

		foreach ( $timer_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for timer-related content.
		$query = new \WP_Query(
			array(
				's'              => 'countdown timer deadline expires',
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for real deadlines.
	 *
	 * @since  1.26034.0230
	 * @return bool True if real deadlines exist, false otherwise.
	 */
	private static function check_real_deadlines() {
		// Check for event plugins.
		if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) {
			$events = get_posts(
				array(
					'post_type'      => 'tribe_events',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
					'meta_query'     => array(
						array(
							'key'     => '_EventStartDate',
							'value'   => date( 'Y-m-d H:i:s' ),
							'compare' => '>',
							'type'    => 'DATETIME',
						),
					),
				)
			);
			if ( ! empty( $events ) ) {
				return true;
			}
		}

		// Check for WooCommerce sale end dates.
		if ( class_exists( 'WooCommerce' ) ) {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => '_sale_price_dates_to',
						'value'   => time(),
						'compare' => '>',
					),
				),
			);
			$products = get_posts( $args );
			if ( ! empty( $products ) ) {
				return true;
			}
		}

		// Check for deadline-related content.
		$keywords = array( 'sale ends', 'offer expires', 'limited time', 'until' );
		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page', 'product' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_real_deadlines', false );
	}

	/**
	 * Check timer placement.
	 *
	 * @since  1.26034.0230
	 * @return bool True if strategic placement exists, false otherwise.
	 */
	private static function check_timer_placement() {
		// Check if WooCommerce has product timers.
		if ( class_exists( 'WooCommerce' ) ) {
			// Look for timer shortcodes in product pages.
			$products = get_posts(
				array(
					'post_type'      => 'product',
					'posts_per_page' => 10,
					'post_status'    => 'publish',
				)
			);

			foreach ( $products as $product ) {
				if ( strpos( $product->post_content, 'countdown' ) !== false ||
					 strpos( $product->post_content, 'timer' ) !== false ) {
					return true;
				}
			}
		}

		// Check for timers in key pages.
		$key_pages = array( 'checkout', 'cart', 'pricing', 'special offer' );
		foreach ( $key_pages as $page_title ) {
			$page = get_page_by_title( $page_title );
			if ( $page && ( strpos( $page->post_content, 'countdown' ) !== false ||
						   strpos( $page->post_content, 'timer' ) !== false ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_timer_placement', false );
	}

	/**
	 * Check for transparency.
	 *
	 * @since  1.26034.0230
	 * @return bool True if transparent messaging exists, false otherwise.
	 */
	private static function check_transparency() {
		$keywords = array( 'when timer expires', 'after deadline', 'price returns', 'offer ends' );

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
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_timer_transparency', false );
	}

	/**
	 * Check for timer variety.
	 *
	 * @since  1.26034.0230
	 * @return bool True if variety exists, false otherwise.
	 */
	private static function check_timer_variety() {
		$timer_types = 0;

		// Check for evergreen timers.
		if ( is_plugin_active( 'evergreen-countdown-timer/evergreen-countdown-timer.php' ) ) {
			$timer_types++;
		}

		// Check for fixed deadline timers.
		$query = new \WP_Query(
			array(
				's'              => 'sale ends flash sale',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);
		if ( $query->have_posts() ) {
			$timer_types++;
		}

		// Check for event-based timers.
		if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) {
			$timer_types++;
		}

		return $timer_types >= 2;
	}

	/**
	 * Check mobile optimization.
	 *
	 * @since  1.26034.0230
	 * @return bool True if mobile-optimized, false otherwise.
	 */
	private static function check_mobile_optimization() {
		// Check if theme is responsive.
		$theme = wp_get_theme();
		$theme_tags = $theme->get( 'Tags' );

		if ( is_array( $theme_tags ) && in_array( 'responsive', array_map( 'strtolower', $theme_tags ), true ) ) {
			return true;
		}

		// Check for mobile optimization plugins.
		if ( is_plugin_active( 'jetpack/jetpack.php' ) ||
			 is_plugin_active( 'wp-optimize/wp-optimize.php' ) ) {
			return true;
		}

		// Most modern timer plugins are mobile-responsive by default.
		if ( is_plugin_active( 'hurrytimer/hurrytimer.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_timers_mobile_optimized', true );
	}
}
