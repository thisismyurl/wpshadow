<?php
/**
 * OpenGraph Meta Tags Not Configured Diagnostic
 *
 * Checks if OpenGraph meta tags are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2347
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OpenGraph Meta Tags Not Configured Diagnostic Class
 *
 * Detects missing OpenGraph meta tags.
 *
 * @since 1.2601.2347
 */
class Diagnostic_OpenGraph_Meta_Tags_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'opengraph-meta-tags-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OpenGraph Meta Tags Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if OpenGraph meta tags are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2347
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for OpenGraph plugins
		$og_plugins = array(
			'jetpack/jetpack.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'wordpress-seo/wp-seo.php',
		);

		$og_active = false;
		foreach ( $og_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$og_active = true;
				break;
			}
		}

		if ( ! $og_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'OpenGraph meta tags are not configured. Add them for better social media sharing appearance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/opengraph-meta-tags-not-configured',
			);
		}

		return null;
	}
}
