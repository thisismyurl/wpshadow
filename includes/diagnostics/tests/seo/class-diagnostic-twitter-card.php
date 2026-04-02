<?php
/**
 * Twitter Card Reviewed Diagnostic (Stub)
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
 * Diagnostic_Twitter_Card_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Twitter_Card extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'twitter-card';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Twitter Card';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether Twitter Card meta tags are being output so links shared on Twitter and social media display with a proper image and summary.';

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
	 * - Detect Twitter/X card metadata output on public pages.
	 *
	 * TODO Fix Plan:
	 * - Enable card metadata for better social previews.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// Twitter/X card tags are almost always provided by an SEO plugin.
		// Detect known plugins that output twitter:card meta tags.
		$active_plugins = (array) get_option( 'active_plugins', array() );

		$seo_plugins_with_twitter = array(
			'wordpress-seo/wp-seo.php'                => 'wpseo_social',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'aioseo_social_networks',
			'rank-math/rank-math.php'                  => 'rank_math_modules',
			'seopress/seopress.php'                    => 'seopress_social_option_name',
			'seopress-pro/seopress-pro.php'            => 'seopress_social_option_name',
		);

		foreach ( $seo_plugins_with_twitter as $plugin_file => $option_key ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				// Plugin is active — assume it manages Twitter cards.
				return null;
			}
		}

		// Check if JetPack sharing/publicise is active (handles OG/Twitter metadata).
		if ( in_array( 'jetpack/jetpack.php', $active_plugins, true ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No SEO plugin capable of generating Twitter/X card metadata was detected. Without twitter:card, twitter:title, and twitter:image tags, links shared on X display with minimal formatting and no image preview. Install an SEO plugin such as Yoast SEO or Rank Math and enable its social metadata features.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/twitter-card',
			'details'      => array(
				'seo_plugin_detected' => false,
			),
		);
	}
}
