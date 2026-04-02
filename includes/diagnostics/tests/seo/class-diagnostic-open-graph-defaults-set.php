<?php
/**
 * Open Graph Defaults Set Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 64.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Open Graph Defaults Set Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Open_Graph_Defaults_Set extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'open-graph-defaults-set';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Open Graph Defaults Set';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether Open Graph meta tags are being output so links shared on social media display with proper titles, descriptions, and preview images.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check wp_head output for og:* tags.
	 *
	 * TODO Fix Plan:
	 * Fix by setting OG defaults/site image/profile.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		$has_yoast    = in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true )
		             || in_array( 'wordpress-seo-premium/wp-seo-premium.php', $active_plugins, true );
		$has_rankmath = in_array( 'seo-by-rank-math/rank-math.php', $active_plugins, true )
		             || in_array( 'seo-by-rank-math-pro/rank-math-pro.php', $active_plugins, true );
		$has_aioseo   = in_array( 'all-in-one-seo-pack/all_in_one_seo_pack.php', $active_plugins, true )
		             || in_array( 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php', $active_plugins, true );
		$has_seopress = in_array( 'wp-seopress/seopress.php', $active_plugins, true )
		             || in_array( 'wp-seopress-pro/seopress-pro.php', $active_plugins, true );

		if ( $has_yoast ) {
			$social  = get_option( 'wpseo_social', array() );
			$og_on   = ! empty( $social['opengraph'] );
			$og_img  = ! empty( $social['og_default_image'] );

			if ( ! $og_on ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Open Graph is disabled in Yoast SEO. Without Open Graph tags, shared links on Facebook, LinkedIn, and other platforms will not display a title, description, or image. Enable Open Graph in Yoast SEO → Social → Facebook.', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 40,
					'auto_fixable' => true,
					'kb_link'      => 'https://wpshadow.com/kb/open-graph-defaults',
					'details'      => array( 'opengraph_enabled' => false, 'default_image_set' => false ),
				);
			}

			if ( ! $og_img ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Open Graph is enabled in Yoast SEO but no default fallback image has been set. Posts and pages without a featured image will share without a social image, appearing as link-only previews. Set a default image in Yoast SEO → Social → Facebook → Default image.', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/open-graph-defaults',
					'details'      => array( 'opengraph_enabled' => true, 'default_image_set' => false ),
				);
			}

			return null;
		}

		if ( $has_rankmath || $has_aioseo || $has_seopress ) {
			// These plugins enable OG by default; assume configured if active.
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No plugin managing Open Graph meta tags is active. Without Open Graph (og:title, og:description, og:image), links shared on social media will display generic or missing previews. Install an SEO plugin such as Yoast SEO or Rank Math to manage Open Graph tags.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/open-graph-defaults',
			'details'      => array( 'opengraph_enabled' => null, 'default_image_set' => null ),
		);
	}
}
