<?php
/**
 * Canonical URL Configuration Treatment
 *
 * Verifies that canonical URLs are properly configured to prevent duplicate
 * content issues and ensure search engines understand the preferred URL version.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1600
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Treatments\Helpers\Treatment_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical URL Configuration Treatment Class
 *
 * Checks for proper canonical URL implementation to avoid duplicate content
 * penalties and ensure SEO best practices.
 *
 * @since 1.6032.1600
 */
class Treatment_Canonical_URL_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'canonical-url-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Canonical URL Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies canonical URL implementation for SEO';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - WordPress canonical URL support enabled
	 * - Canonical tags properly implemented
	 * - No conflicting canonical plugins
	 * - Home URL canonicalization
	 *
	 * @since  1.6032.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if canonical URLs are being output.
		remove_action( 'wp_head', 'rel_canonical' );
		$has_canonical = has_action( 'wp_head', 'rel_canonical' );

		if ( false === $has_canonical ) {
			$issues[] = __( 'WordPress canonical URL output is not enabled', 'wpshadow' );
		}

		// Check for conflicting SEO plugins that might handle canonicals.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'              => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'seo-by-rank-math/rank-math.php'        => 'Rank Math',
		);

		$active_seo_plugins = array();
		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_seo_plugins[] = $name;
			}
		}

		// Having multiple SEO plugins can cause canonical conflicts.
		if ( count( $active_seo_plugins ) > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of SEO plugin names */
				__( 'Multiple SEO plugins detected that may conflict with canonical URLs: %s', 'wpshadow' ),
				implode( ', ', $active_seo_plugins )
			);
		}

		// Check home URL consistency.
		$home_url    = get_option( 'home' );
		$site_url    = get_option( 'siteurl' );
		$has_www     = Treatment_URL_And_Pattern_Helper::has_www( $home_url );
		$site_has_www = Treatment_URL_And_Pattern_Helper::has_www( $site_url );

		if ( $has_www !== $site_has_www ) {
			$issues[] = __( 'Home URL and Site URL have inconsistent www subdomain usage, which can cause canonical URL issues', 'wpshadow' );
		}

		// Check if using HTTPS consistently.
		$home_protocol = Treatment_URL_And_Pattern_Helper::get_scheme( $home_url );
		$site_protocol = Treatment_URL_And_Pattern_Helper::get_scheme( $site_url );

		if ( $home_protocol !== $site_protocol ) {
			$issues[] = sprintf(
				/* translators: 1: home URL scheme, 2: site URL scheme */
				__( 'Home URL uses %1$s but Site URL uses %2$s, creating canonical conflicts', 'wpshadow' ),
				$home_protocol,
				$site_protocol
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/canonical-url-configuration',
			);
		}

		return null;
	}
}
