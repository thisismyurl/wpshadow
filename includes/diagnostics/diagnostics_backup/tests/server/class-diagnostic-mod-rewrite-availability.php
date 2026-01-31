<?php
/**
 * Diagnostic: Apache Mod_Rewrite Availability
 *
 * Checks if Apache mod_rewrite module is available and enabled.
 * Required for pretty permalinks and many SEO features.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Server
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Mod_Rewrite_Availability
 *
 * Tests Apache mod_rewrite module availability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Mod_Rewrite_Availability extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mod-rewrite-availability';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Apache Mod_Rewrite Availability';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Apache mod_rewrite is available';

	/**
	 * Check mod_rewrite availability.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if running Apache.
		$is_apache = false;

		if ( function_exists( 'apache_get_modules' ) ) {
			$is_apache = true;
			$modules   = apache_get_modules();

			// Check if mod_rewrite is loaded.
			if ( in_array( 'mod_rewrite', $modules, true ) ) {
				return null; // mod_rewrite is available.
			}

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Apache mod_rewrite module is not loaded. Pretty permalinks and .htaccess rules will not work.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mod_rewrite_availability',
				'meta'        => array(
					'is_apache'      => true,
					'mod_rewrite'    => false,
					'loaded_modules' => $modules,
				),
			);
		}

		// Check via $_SERVER variables.
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		if ( stripos( $server_software, 'Apache' ) !== false || stripos( $server_software, 'LiteSpeed' ) !== false ) {
			$is_apache = true;
		}

		if ( ! $is_apache ) {
			return null; // Not applicable for non-Apache servers.
		}

		// Check if got_url_rewrite() indicates rewrite support.
		if ( function_exists( 'got_url_rewrite' ) && ! got_url_rewrite() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'URL rewriting is not available. WordPress cannot use pretty permalinks.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mod_rewrite_availability',
				'meta'        => array(
					'is_apache'      => true,
					'got_url_rewrite' => false,
				),
			);
		}

		// Check permalink structure.
		$permalink_structure = get_option( 'permalink_structure', '' );

		if ( empty( $permalink_structure ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Permalink structure is not configured. This may indicate mod_rewrite is unavailable.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mod_rewrite_availability',
				'meta'        => array(
					'is_apache'            => true,
					'permalink_structure' => '',
				),
			);
		}

		// mod_rewrite appears to be available.
		return null;
	}
}
