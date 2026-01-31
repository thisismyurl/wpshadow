<?php
/**
 * XML Sitemap Not Generating Diagnostic
 *
 * Checks if XML sitemap is properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML Sitemap Not Generating Diagnostic Class
 *
 * Checks if XML sitemap generation works.
 *
 * @since 1.2601.2310
 */
class Diagnostic_XML_Sitemap_Not_Generating extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-not-generating';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Not Generating';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XML sitemap is accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if SEO plugin is active (provides sitemap)
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'rank-math-seo/rank-math.php',
		);

		$seo_plugin_active = false;
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$seo_plugin_active = true;
				break;
			}
		}

		if ( ! $seo_plugin_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No SEO plugin with sitemap functionality is active. XML sitemap won\'t be generated for search engines.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/xml-sitemap-not-generating',
			);
		}

		return null;
	}
}
