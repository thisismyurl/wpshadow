<?php
/**
 * Diagnostic: Countdown Timers Strategic
 *
 * Tests whether the site uses countdown timers ethically to create genuine
 * urgency without manipulation.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4540
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
 * Countdown Timers Strategic Diagnostic
 *
 * Checks for ethical countdown timer usage tied to real deadlines.
 * Timers create urgency but must be legitimate to maintain trust.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Countdown_Timers extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-countdown-timers-strategically';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Countdown Timers Strategic';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses countdown timers ethically for genuine urgency';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for countdown timer implementation.
	 *
	 * Detects timer plugins and validates ethical usage patterns.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array with usage assessment, null if optimal.
	 */
	public static function check() {
		// Check for countdown timer plugins.
		$timer_plugins = array(
			'countdown-timer-ultimate/countdown-timer-ultimate.php' => 'Countdown Timer Ultimate',
			'elementor/elementor.php'                                => 'Elementor',
			'thrive-visual-editor/thrive-visual-editor.php'          => 'Thrive Architect',
			'beaver-builder-lite-version/fl-builder.php'             => 'Beaver Builder',
		);

		$has_timer = false;
		foreach ( $timer_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_timer = true;
				break;
			}
		}

		// Check content for timer shortcodes.
		if ( ! $has_timer ) {
			$posts = get_posts(
				array(
					'post_type'      => array( 'page', 'product' ),
					'posts_per_page' => 20,
					'post_status'    => 'publish',
				)
			);

			foreach ( $posts as $post ) {
				if ( preg_match( '/\[(countdown|timer|urgency)/i', $post->post_content ) ) {
					$has_timer = true;
					break;
				}
			}
		}

		if ( ! $has_timer ) {
			// No timers - recommend if appropriate.
			if ( class_exists( 'WooCommerce' ) ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __(
						'No countdown timers detected. Strategic use of countdown timers for real promotions (flash sales, limited offers) increases conversions by 9%. Important: Only use with genuine deadlines - fake urgency damages trust. Consider timers for actual time-limited promotions.',
						'wpshadow'
					),
					'severity'     => 'low',
					'threat_level' => 28,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/countdown-timers',
				);
			}
			return null; // Not needed for non-commerce sites.
		}

		// Has timers - validate ethical usage.
		$has_stock_management = false;
		if ( class_exists( 'WooCommerce' ) ) {
			$has_stock_management = get_option( 'woocommerce_manage_stock' ) === 'yes';
		}

		// Check for evergreen timer patterns (warning sign).
		$has_evergreen = false;
		$posts         = get_posts(
			array(
				'post_type'      => array( 'page', 'product' ),
				'posts_per_page' => 50,
				'post_status'    => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			if ( preg_match( '/(expires?|ends?) (today|tonight|soon)/i', $post->post_content ) ) {
				$has_evergreen = true;
				break;
			}
		}

		if ( $has_evergreen && ! $has_stock_management ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __(
					'Countdown timers detected but may be misleading. Timers showing "expires tonight" that reset daily damage credibility. Ensure timers reflect real deadlines (sale end dates, stock limits, registration cutoffs). Fake urgency increases short-term conversions but destroys long-term trust.',
					'wpshadow'
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ethical-countdown-timers',
			);
		}

		return null; // Timers present, no red flags.
	}
}
