<?php
/**
 * Diagnostic: Fragment Caching Implementation
 *
 * Checks if dynamic page fragments are cached separately.
 * Full page caching breaks with user-specific content.
 * Fragment caching enables 90% caching with 10% dynamic.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.26028.1854
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Fragment_Caching_Implementation
 *
 * Tests fragment caching for dynamic content.
 *
 * @since 1.26028.1854
 */
class Diagnostic_Fragment_Caching_Implementation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'fragment-caching-implementation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Fragment Caching Implementation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if dynamic page fragments are cached separately';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Check fragment caching implementation.
	 *
	 * @since  1.26028.1854
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if site has personalized content.
		$has_personalized_content = self::detect_personalized_content();

		// If no personalized content, fragment caching is not needed.
		if ( ! $has_personalized_content ) {
			return null;
		}

		// Check if fragment caching is implemented.
		$has_fragment_caching = self::detect_fragment_caching();

		// Check for ESI (Edge Side Includes) support.
		$has_esi_support = self::check_esi_support();

		// If personalized content without fragment caching, flag as issue.
		if ( ! $has_fragment_caching && ! $has_esi_support ) {
			$severity = 'medium';
			$threat_level = 55;

			// E-commerce sites need this more urgently.
			if ( class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ) ) {
				$severity = 'high';
				$threat_level = 65;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site has personalized content but no fragment caching. This forces full page cache bypass for logged-in users. Implement fragment/partial caching to cache 90% of page with 10% dynamic content.', 'wpshadow' ),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/fragment-caching-implementation',
				'meta'         => array(
					'has_personalized_content' => $has_personalized_content,
					'has_fragment_caching'     => $has_fragment_caching,
					'has_esi_support'          => $has_esi_support,
					'is_ecommerce'             => class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ),
					'recommendation'           => 'Implement fragment caching or ESI',
				),
			);
		}

		// Fragment caching is not needed or already implemented.
		return null;
	}

	/**
	 * Detect if site has personalized content.
	 *
	 * @since  1.26028.1854
	 * @return bool True if personalized content detected, false otherwise.
	 */
	private static function detect_personalized_content() {
		// Check for e-commerce (cart, wishlist, account).
		if ( class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ) ) {
			return true;
		}

		// Check for membership plugins.
		if ( class_exists( 'MeprOptions' ) || // MemberPress.
			function_exists( 'pmpro_getMembershipLevelForUser' ) || // Paid Memberships Pro.
			class_exists( 'WLM3_Member_Methods' ) // WishList Member.
		) {
			return true;
		}

		// Check for forum plugins.
		if ( class_exists( 'bbPress' ) || function_exists( 'bp_is_active' ) ) { // BuddyPress.
			return true;
		}

		// Check for learning management systems.
		if ( class_exists( 'LLMS' ) || // LifterLMS.
			class_exists( 'LearnDash_Settings_Section' ) || // LearnDash.
			function_exists( 'tutor' ) // Tutor LMS.
		) {
			return true;
		}

		// Check for social plugins with user-specific content.
		if ( function_exists( 'bp_is_active' ) || // BuddyPress.
			class_exists( 'Better_Messages' )
		) {
			return true;
		}

		// Check if user roles/capabilities are extensively used.
		$user_count = count_users();
		$total_users = isset( $user_count['total_users'] ) ? $user_count['total_users'] : 0;

		// Sites with many users likely have personalization.
		if ( $total_users > 100 ) {
			return true;
		}

		return false;
	}

	/**
	 * Detect if fragment caching is implemented.
	 *
	 * @since  1.26028.1854
	 * @return bool True if fragment caching detected, false otherwise.
	 */
	private static function detect_fragment_caching() {
		// Check for caching plugins with fragment support.
		$fragment_plugins = array(
			'wp-rocket/wp-rocket.php',              // Has user cache.
			'w3-total-cache/w3-total-cache.php',    // Has fragment cache.
			'comet-cache/comet-cache.php',          // Has fragment cache.
		);

		foreach ( $fragment_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				// Note: This doesn't verify it's configured, just that plugin exists.
				return true;
			}
		}

		// Check for Varnish with ESI.
		if ( function_exists( 'varnish_purge_all' ) ) {
			// Varnish can support ESI.
			return true;
		}

		return false;
	}

	/**
	 * Check for ESI (Edge Side Includes) support.
	 *
	 * @since  1.26028.1854
	 * @return bool True if ESI support detected, false otherwise.
	 */
	private static function check_esi_support() {
		// Check for Cloudflare Workers.
		if ( isset( $_SERVER['HTTP_CF_WORKER'] ) ) {
			return true;
		}

		// Check for Varnish (supports ESI).
		if ( function_exists( 'varnish_purge_all' ) ) {
			return true;
		}

		// Check for Fastly (supports ESI).
		if ( isset( $_SERVER['HTTP_FASTLY_FF'] ) ) {
			return true;
		}

		// Check for Akamai (supports ESI).
		if ( isset( $_SERVER['HTTP_AKAMAI_EDGE_CACHE'] ) ) {
			return true;
		}

		return false;
	}
}
