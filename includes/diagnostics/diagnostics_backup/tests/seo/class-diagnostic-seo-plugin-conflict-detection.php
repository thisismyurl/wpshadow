<?php
/**
 * SEO Plugin Conflict Detection Diagnostic
 *
 * Checks if multiple SEO plugins create conflicts.
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
 * SEO Plugin Conflict Detection Diagnostic Class
 *
 * Detects conflicts between multiple SEO plugins.
 *
 * @since 1.2601.2310
 */
class Diagnostic_SEO_Plugin_Conflict_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'seo-plugin-conflict-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Plugin Conflict Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for multiple competing SEO plugins';

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
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'rank-math-seo/rank-math.php',
			'the-seo-framework/the-seo-framework.php',
		);

		$active_seo_plugins = array();
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_seo_plugins[] = $plugin;
			}
		}

		if ( count( $active_seo_plugins ) > 1 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Multiple SEO plugins are active (%d found). This can cause duplicate meta tags and conflicting settings.', 'wpshadow' ),
					count( $active_seo_plugins )
				),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/seo-plugin-conflict-detection',
			);
		}

		return null;
	}
}
