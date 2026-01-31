<?php
/**
 * Static Asset Caching Headers Diagnostic
 *
 * Checks if proper caching headers are set for static assets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2308
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static Asset Caching Headers Diagnostic Class
 *
 * Verifies proper cache control headers for static assets.
 *
 * @since 1.2601.2308
 */
class Diagnostic_Static_Asset_Caching_Headers extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'static-asset-caching-headers';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Static Asset Caching Headers';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures proper cache control headers are configured for static assets';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2308
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if .htaccess exists and has caching rules
		$htaccess_path = ABSPATH . '.htaccess';
		$has_htaccess_caching = false;

		if ( file_exists( $htaccess_path ) ) {
			$htaccess_content = file_get_contents( $htaccess_path );
			if ( strpos( $htaccess_content, 'Cache-Control' ) !== false ||
				 strpos( $htaccess_content, 'mod_expires' ) !== false ||
				 strpos( $htaccess_content, 'ExpiresActive' ) !== false ) {
				$has_htaccess_caching = true;
			}
		}

		// Check for caching plugins that handle headers
		$caching_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-rocket/wp-rocket.php',
			'litespeed-cache/litespeed-cache.php',
		);

		$has_caching_plugin = false;
		foreach ( $caching_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_caching_plugin = true;
				break;
			}
		}

		// Check for web.config (IIS)
		$web_config_path = ABSPATH . 'web.config';
		$has_web_config_caching = false;

		if ( file_exists( $web_config_path ) ) {
			$web_config_content = file_get_contents( $web_config_path );
			if ( strpos( $web_config_content, 'httpExpires' ) !== false ||
				 strpos( $web_config_content, 'clientCache' ) !== false ) {
				$has_web_config_caching = true;
			}
		}

		// Determine if caching headers are properly configured
		$has_proper_caching = $has_htaccess_caching || $has_caching_plugin || $has_web_config_caching;

		if ( ! $has_proper_caching ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Static asset caching headers not properly configured. Set browser cache headers to improve performance.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/static-asset-caching-headers',
			);
		}

		return null;
	}
}
