<?php
/**
 * Open Graph Tags Missing Diagnostic
 *
 * Checks if Open Graph meta tags are implemented.
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
 * Open Graph Tags Missing Diagnostic Class
 *
 * Detects missing Open Graph implementation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Open_Graph_Tags_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'open-graph-tags-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Open Graph Tags Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Open Graph tags are configured';

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
		// Check for Open Graph filter
		if ( has_filter( 'wp_head', 'og_meta_output' ) || has_filter( 'wp_head', 'rel_canonical' ) ) {
			return null; // Likely implemented by SEO plugin
		}

		// Check if SEO plugin is active
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'rank-math-seo/rank-math.php',
			'jetpack/jetpack.php',
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
				'description'   => __( 'Open Graph tags are not implemented. Social media shares won\'t display proper titles, descriptions, or images.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/open-graph-tags-missing',
			);
		}

		return null;
	}
}
