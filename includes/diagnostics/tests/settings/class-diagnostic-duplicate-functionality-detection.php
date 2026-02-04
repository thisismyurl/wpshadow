<?php
/**
 * Duplicate Functionality Detection Diagnostic
 *
 * Detects when multiple plugins provide overlapping functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate Functionality Detection Diagnostic
 *
 * Identifies duplicate functionality across multiple plugins.
 *
 * @since 1.6030.2230
 */
class Diagnostic_Duplicate_Functionality_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-functionality-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Functionality Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when multiple plugins provide overlapping functionality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$duplicates = array();
		$active_plugins = get_option( 'active_plugins', array() );

		// Map of plugin categories and their files
		$plugin_categories = array(
			'SEO' => array(
				'wordpress-seo/wp-seo.php',
				'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'wp-seo-yoast/wp-seo-yoast.php',
				'rank-math-seo/rank-math.php',
				'the-seo-framework/the-seo-framework.php',
			),
			'Caching' => array(
				'wp-rocket/wp-rocket.php',
				'w3-total-cache/w3-total-cache.php',
				'wp-super-cache/wp-super-cache.php',
				'autoptimize/autoptimize.php',
				'comet-cache/comet-cache.php',
			),
			'Contact Forms' => array(
				'contact-form-7/wp-contact-form-7.php',
				'wpforms-lite/wpforms.php',
				'ninja-forms/ninja-forms.php',
				'formidable/formidable.php',
				'gravity_forms_plugin/gravityforms.php',
			),
			'Backup' => array(
				'updraftplus/updraftplus.php',
				'backwpup/backwpup.php',
				'jetpack/jetpack.php',
				'wordpress-backup-to-dropbox/backup.php',
				'backup-guard/backup-guard.php',
			),
			'Analytics' => array(
				'jetpack/jetpack.php',
				'google-analytics-for-wordpress/googleanalyticsbyadmonition.php',
				'google-site-kit/google-site-kit.php',
				'monster-insights-lite/monster-insights-lite.php',
			),
			'Security' => array(
				'wordfence/wordfence.php',
				'sucuri-scanner/sucuri.php',
				'all-in-one-wp-security-and-firewall/wp-security.php',
				'iThemes-Security-Pro/wp-security-pro.php',
			),
			'Performance' => array(
				'wp-rocket/wp-rocket.php',
				'perfmatrix/perfmatrix.php',
				'litespeed-cache/litespeed-cache.php',
				'hummingbird-performance/wp-hummingbird.php',
			),
		);

		// Check for duplicates
		foreach ( $plugin_categories as $category => $plugins ) {
			$active_in_category = array();

			foreach ( $plugins as $plugin ) {
				if ( in_array( $plugin, $active_plugins, true ) ) {
					$active_in_category[] = basename( dirname( $plugin ) );
				}
			}

			if ( count( $active_in_category ) > 1 ) {
				$duplicates[ $category ] = $active_in_category;
			}
		}

		// Report findings
		if ( ! empty( $duplicates ) ) {
			$severity     = 'medium';
			$threat_level = 50;

			if ( count( $duplicates ) > 2 ) {
				$severity     = 'high';
				$threat_level = 75;
			}

			$description = __( 'Multiple plugins providing same functionality detected', 'wpshadow' );

			$details = array(
				'duplicate_categories' => $duplicates,
				'total_duplicates'     => count( $duplicates ),
				'recommendations'      => array(
					__( 'Consider disabling duplicate plugins to reduce overhead', 'wpshadow' ),
					__( 'Duplicate caching plugins can cause issues - keep only one', 'wpshadow' ),
					__( 'Multiple security plugins may lock you out', 'wpshadow' ),
				),
			);

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/duplicate-functionality-detection',
				'details'      => $details,
			);
		}

		return null;
	}
}
