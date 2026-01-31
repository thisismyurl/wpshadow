<?php
/**
 * CDN Integration Not Configured Diagnostic
 *
 * Checks if CDN is configured for static assets.
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
 * CDN Integration Not Configured Diagnostic Class
 *
 * Detects missing CDN configuration.
 *
 * @since 1.2601.2310
 */
class Diagnostic_CDN_Integration_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-integration-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CDN Integration Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CDN is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for CDN plugins
		$cdn_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'cloudflare/cloudflare.php',
			'wp-fastest-cache/wp-fastest-cache.php',
			'cdn-enabler/cdn-enabler.php',
		);

		$cdn_active = false;
		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$cdn_active = true;
				break;
			}
		}

		if ( ! $cdn_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No CDN or cache plugin is configured. Static assets are served from your server, increasing load times for distant users.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cdn-integration-not-configured',
			);
		}

		return null;
	}
}
