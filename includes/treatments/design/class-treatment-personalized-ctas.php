<?php
/**
 * Personalized CTAs Treatment
 *
 * Tests whether the site personalizes calls-to-action based on user context to outperform generic CTAs.
 *
 * @since   1.6034.0230
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personalized CTAs Treatment Class
 *
 * Personalized CTAs can outperform generic CTAs by 202%. Tailoring messages
 * to user behavior, source, or stage in the journey dramatically improves conversion.
 *
 * @since 1.6034.0230
 */
class Treatment_Personalized_Ctas extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'personalized-ctas';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Personalized CTAs';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site personalizes calls-to-action based on user context to outperform generic CTAs';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Personalized_Ctas' );
	}

	/**
	 * Check for personalization plugins.
	 *
	 * @since  1.6034.0230
	 * @return bool True if plugins exist, false otherwise.
	 */
	private static function check_personalization_plugins() {
		$plugins = array(
			'if-so/if-so.php',
			'wp-optimize/wp-optimize.php',
			'nelio-content/nelio-content.php',
		);

		foreach ( $plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_personalization', false );
	}

	/**
	 * Check for dynamic content.
	 *
	 * @since  1.6034.0230
	 * @return bool True if dynamic content exists, false otherwise.
	 */
	private static function check_dynamic_content() {
		if ( is_plugin_active( 'if-so/if-so.php' ) ) {
			return true;
		}

		$query = new \WP_Query(
			array(
				's'              => 'dynamic content personalized',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for member-specific CTAs.
	 *
	 * @since  1.6034.0230
	 * @return bool True if member CTAs exist, false otherwise.
	 */
	private static function check_member_ctas() {
		// Check for membership plugins that support this.
		if ( is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) ||
			 is_plugin_active( 'memberpress/memberpress.php' ) ) {
			return true;
		}

		// Check for shortcodes that show different content to members.
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		foreach ( $pages as $page ) {
			if ( has_shortcode( $page->post_content, 'if' ) ||
				 strpos( $page->post_content, 'is_user_logged_in' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_member_ctas', false );
	}

	/**
	 * Check for location personalization.
	 *
	 * @since  1.6034.0230
	 * @return bool True if location-based exists, false otherwise.
	 */
	private static function check_location_personalization() {
		// Check for geolocation plugins.
		if ( is_plugin_active( 'geoip-detect/geoip-detect.php' ) ||
			 is_plugin_active( 'cloudflare/cloudflare.php' ) ) {
			return true;
		}

		$query = new \WP_Query(
			array(
				's'              => 'geographic geolocation country',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for source personalization.
	 *
	 * @since  1.6034.0230
	 * @return bool True if source-based exists, false otherwise.
	 */
	private static function check_source_personalization() {
		// If-So plugin supports this.
		if ( is_plugin_active( 'if-so/if-so.php' ) ) {
			return true;
		}

		// Check for UTM or referrer-based content.
		$query = new \WP_Query(
			array(
				's'              => 'utm source referrer',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for behavior triggers.
	 *
	 * @since  1.6034.0230
	 * @return bool True if behavior triggers exist, false otherwise.
	 */
	private static function check_behavior_triggers() {
		// Check for popup/modal plugins with triggers.
		if ( is_plugin_active( 'popup-maker/popup-maker.php' ) ||
			 is_plugin_active( 'elementor/elementor.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_behavior_triggers', false );
	}
}
