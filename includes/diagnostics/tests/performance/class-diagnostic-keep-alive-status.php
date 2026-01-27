<?php
/**
 * Diagnostic: HTTP Keep-Alive Status
 *
 * Checks if HTTP Keep-Alive is enabled for persistent connections.
 * Keep-Alive improves performance by reusing TCP connections.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Keep_Alive_Status
 *
 * Tests HTTP Keep-Alive configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Keep_Alive_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'keep-alive-status';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Keep-Alive Status';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if HTTP Keep-Alive is enabled';

	/**
	 * Check HTTP Keep-Alive status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$site_url = home_url();

		// Make a HEAD request to check headers.
		$response = wp_remote_head(
			$site_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null; // Can't check if request fails.
		}

		// Check Connection header.
		$headers    = wp_remote_retrieve_headers( $response );
		$connection = isset( $headers['connection'] ) ? strtolower( $headers['connection'] ) : '';

		// Check Keep-Alive header.
		$keep_alive = isset( $headers['keep-alive'] ) ? $headers['keep-alive'] : '';

		// If Connection: close, Keep-Alive is disabled.
		if ( 'close' === $connection ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'HTTP Keep-Alive is disabled. Persistent connections are not available, which may reduce performance for users loading multiple resources.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/keep_alive_status',
				'meta'        => array(
					'connection' => $connection,
					'keep_alive' => $keep_alive,
				),
			);
		}

		// Check if Keep-Alive timeout is too short.
		if ( ! empty( $keep_alive ) ) {
			// Parse timeout from Keep-Alive header (e.g., "timeout=5, max=100").
			if ( preg_match( '/timeout=(\d+)/', $keep_alive, $matches ) ) {
				$timeout = (int) $matches[1];

				// Warn if timeout is less than 5 seconds.
				if ( $timeout < 5 ) {
					return array(
						'id'          => self::$slug,
						'title'       => self::$title,
						'description' => sprintf(
							/* translators: %d: Keep-Alive timeout in seconds */
							__( 'HTTP Keep-Alive timeout is very short (%d seconds). Consider increasing it for better performance.', 'wpshadow' ),
							$timeout
						),
						'severity'    => 'info',
						'threat_level' => 20,
						'auto_fixable' => false,
						'kb_link'     => 'https://wpshadow.com/kb/keep_alive_status',
						'meta'        => array(
							'timeout' => $timeout,
							'keep_alive' => $keep_alive,
						),
					);
				}
			}
		}

		// Keep-Alive is enabled and properly configured.
		return null;
	}
}
