<?php
/**
 * Meta Titles Managed Diagnostic (Stub)
 *
 * TODO stub mapped to the seo gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Meta_Titles_Managed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Meta_Titles_Managed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'meta-titles-managed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Meta Titles Managed';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether an SEO plugin is active to manage meta title tags for posts and pages, rather than relying on the theme\'s default title output.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Detect SEO plugin title template settings or custom title management.
	 *
	 * TODO Fix Plan:
	 * - Configure title templates that reflect page purpose and keyword targets.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'wordpress-seo-premium/wp-seo-premium.php',
			'seo-by-rank-math/rank-math.php',
			'seo-by-rank-math-pro/rank-math-pro.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
			'wp-seopress/seopress.php',
			'wp-seopress-pro/seopress-pro.php',
			'the-seo-framework/the-seo-framework.php',
		);

		$seo_plugin_active = false;
		foreach ( $seo_plugins as $plugin_file ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				$seo_plugin_active = true;
				break;
			}
		}

		if ( ! $seo_plugin_active ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No SEO plugin is managing meta title templates. Without controlled title templates, WordPress outputs the post title followed by the site name with no keyword optimisation or format control. Install an SEO plugin such as Yoast SEO or Rank Math to define title templates for posts, pages, archives, and the homepage.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/meta-titles-managed',
				'details'      => array( 'seo_plugin_active' => false ),
			);
		}

		// Yoast: check if home title template has been customised.
		$has_yoast = in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true )
		          || in_array( 'wordpress-seo-premium/wp-seo-premium.php', $active_plugins, true );

		if ( $has_yoast ) {
			$titles = get_option( 'wpseo_titles', array() );
			if ( empty( $titles['title-home-wpseo'] ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Yoast SEO is active but no homepage title template has been set. Configure the homepage title template in Yoast SEO → Search Appearance → General to control how your home page appears in search results.', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/meta-titles-managed',
					'details'      => array( 'seo_plugin_active' => true, 'home_title_template' => '' ),
				);
			}
		}

		return null;
	}
}
