<?php
/**
 * Keep-Alive Connection Not Configured Diagnostic
 *
 * Checks keep-alive.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Keep_Alive_Connection_Not_Configured Class
 *
 * Performs diagnostic check for Keep Alive Connection Not Configured.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Keep_Alive_Connection_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'keep-alive-connection-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keep-Alive Connection Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks keep-alive';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if HTTP Keep-Alive is enabled on the server.
		// Keep-Alive allows multiple requests over a single TCP connection,
		// reducing overhead and improving performance.

		// This is an Apache/Nginx configuration, not a WordPress setting.
		// We check the server's HTTP response headers for Connection: keep-alive.

		$site_url = get_site_url();

		// Make a HEAD request to check response headers.
		// phpcs:ignore WordPress.WP.RemoteCall.dynamic_using_variable_url
		$response = wp_remote_head(
			$site_url,
			array(
				'timeout'    => 10,
				'sslverify'  => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			// Can't reach the site, skip this check.
			return null;
		}

		$headers = wp_remote_retrieve_headers( $response );
		$connection_header = isset( $headers['connection'] ) ? strtolower( $headers['connection'] ) : '';

		// Keep-Alive should be in the Connection header.
		if ( false === strpos( $connection_header, 'keep-alive' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your server is not using HTTP Keep-Alive connections (like hanging up the phone after each word instead of having a conversation). This means every request to your site has to establish a new network connection, which wastes time and bandwidth. Enabling Keep-Alive lets your browser reuse the same connection for multiple requests, making your site feel faster. Ask your hosting provider to enable this in Apache or Nginx.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/keep-alive-connection-not-configured',
				'context'      => array(
					'connection_header' => $connection_header,
					'keep_alive_enabled' => false,
				),
			);
		}

		// Keep-Alive is enabled - great!
		return null;
	}
}
