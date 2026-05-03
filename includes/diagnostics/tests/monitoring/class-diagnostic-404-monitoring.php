<?php
/**
 * 404 Monitoring Diagnostic
 *
 * Undetected 404 errors mean broken links and lost visitors are invisible.
 * A 404 monitoring plugin captures these events and enables orderly
 * redirects, recovering SEO equity and improving user experience. Without
 * it, site owners cannot know which URLs are broken or where traffic is
 * being lost.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_404_Monitoring Class
 *
 * @since 0.6095
 */
class Diagnostic_404_Monitoring extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = '404-monitoring';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = '404 Monitoring';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that a 404 error monitoring or redirect management solution is active so broken URLs are captured and can be fixed or redirected.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Plugins known to monitor or log 404 errors.
	 * Keys are plugin file paths (relative to wp-content/plugins/).
	 * Values are human-readable names for display.
	 */
	private const MONITORING_PLUGINS = array(
		'redirection/redirection.php'                  => 'Redirection',
		'rankmath/rankmath.php'                        => 'Rank Math SEO',
		'rank-math/rank-math.php'                      => 'Rank Math SEO',
		'wordpress-seo-premium/wp-seo-premium.php'     => 'Yoast SEO Premium',
		'seopress/seopress.php'                        => 'SEOPress',
		'seopress-pro/seopress-pro.php'                => 'SEOPress Pro',
		'404-to-301/404-to-301.php'                    => '404 to 301',
		'all-404-redirect-to-homepage/all-404-redirect-to-homepage.php' => 'All 404 Redirect to Homepage',
		'404-solution/404-solution.php'                => '404 Solution',
		'smart-404/smart-404.php'                      => 'Smart 404',
		'log-404/log-404.php'                          => 'Log 404s',
		'wp-404-auto-redirect-to-similar-post/wp-404-auto-redirect-to-similar-post.php' => 'WP 404 Auto Redirect',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the active plugin list against a curated list of plugins that
	 * provide 404 monitoring or redirect management. Returns null (healthy)
	 * if at least one is active.
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( array_keys( self::MONITORING_PLUGINS ) as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No 404 monitoring or redirect management plugin is active. Broken URLs, changed post slugs, or deleted content are causing silent errors that lose visitors and SEO equity without your knowledge.', 'thisismyurl-shadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'details'      => array(
				'recommended_plugin' => 'Redirection',
				'plugin_url'         => 'https://wordpress.org/plugins/redirection/',
				'fix'                => __( 'Install and activate the free Redirection plugin. Enable 404 logging in its settings, review the log regularly, and create redirects for any high-traffic broken URLs. Rank Math SEO and SEOPress also include built-in redirect managers if you prefer an all-in-one solution.', 'thisismyurl-shadow' ),
			),
		);
	}
}
