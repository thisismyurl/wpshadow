<?php
/**
 * Content Distribution Network Failover Not Configured Diagnostic
 *
 * Checks if CDN has failover configuration.
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
 * Content Distribution Network Failover Not Configured Diagnostic Class
 *
 * Detects missing CDN failover.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Content_Distribution_Network_Failover_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-distribution-network-failover-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Distribution Network Failover Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CDN failover is configured';

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
			'cdn-enabler/cdn-enabler.php',
		);

		$cdn_active = false;
		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$cdn_active = true;
				break;
			}
		}

		if ( $cdn_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CDN is configured but failover is not. If CDN goes down, content won\'t load from fallback location.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-distribution-network-failover-not-configured',
			);
		}

		return null;
	}
}
