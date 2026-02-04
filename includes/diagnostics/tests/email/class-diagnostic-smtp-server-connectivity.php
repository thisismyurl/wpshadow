<?php
/**
 * SMTP Server Connectivity Diagnostic
 *
 * Tests actual SMTP server connectivity by attempting to connect to the
 * configured SMTP host and port.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1730
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SMTP Server Connectivity Diagnostic Class
 *
 * Tests whether your site can actually connect to the configured SMTP email
 * server. This is like checking if your phone line works before trying to
 * make a call - without a working connection, emails will fail silently.
 *
 * @since 1.6035.1730
 */
class Diagnostic_Smtp_Server_Connectivity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'smtp-server-connectivity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SMTP Server Connectivity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests actual connection to SMTP email server';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the SMTP server connectivity diagnostic check.
	 *
	 * @since  1.6035.1730
	 * @return array|null Finding array if connection issues detected, null otherwise.
	 */
	public static function check() {
		$smtp_config = self::get_smtp_configuration();

		if ( ! $smtp_config ) {
			// No SMTP configured - different diagnostic handles this.
			return null;
		}

		$host = $smtp_config['host'];
		$port = $smtp_config['port'];

		// Test connection to configured port.
		$connection_result = self::test_smtp_connection( $host, $port );

		if ( $connection_result['success'] ) {
			// Connection successful.
			return null;
		}

		// Connection failed - try fallback ports.
		$fallback_ports = array( 25, 465, 587 );
		$working_port   = null;

		foreach ( $fallback_ports as $test_port ) {
			if ( $test_port === $port ) {
				continue; // Already tested.
			}

			$fallback_result = self::test_smtp_connection( $host, $test_port );
			if ( $fallback_result['success'] ) {
				$working_port = $test_port;
				break;
			}
		}

		// Build error message.
		if ( $working_port ) {
			// Found a working port.
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: configured port, 2: working port */
					__( 'Your site cannot connect to the email server on port %1$d (like calling a disconnected phone number), but port %2$d works. This means emails are failing to send. Update your SMTP settings to use port %2$d instead.', 'wpshadow' ),
					$port,
					$working_port
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/smtp-port-configuration',
				'context'      => array(
					'configured_host' => $host,
					'configured_port' => $port,
					'working_port'    => $working_port,
					'error_message'   => $connection_result['error'],
				),
			);
		}

		// No working ports found.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: SMTP host, 2: configured port */
				__( 'Your site cannot connect to the email server at %1$s:%2$d (like trying to call a phone that\'s turned off). This means your emails are not being sent. Check that the server address is correct, your hosting provider allows outbound email connections, and any firewalls are properly configured.', 'wpshadow' ),
				$host,
				$port
			),
			'severity'     => 'critical',
			'threat_level' => 95,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/smtp-connection-failed',
			'context'      => array(
				'configured_host' => $host,
				'configured_port' => $port,
				'tested_ports'    => $fallback_ports,
				'error_message'   => $connection_result['error'],
			),
		);
	}

	/**
	 * Get SMTP configuration from various sources.
	 *
	 * @since  1.6035.1730
	 * @return array|null SMTP configuration array or null if not configured.
	 */
	private static function get_smtp_configuration() {
		// Check wp-config.php constants.
		if ( defined( 'WPMS_ON' ) && defined( 'WPMS_SMTP_HOST' ) ) {
			return array(
				'host'   => WPMS_SMTP_HOST,
				'port'   => defined( 'WPMS_SMTP_PORT' ) ? WPMS_SMTP_PORT : 587,
				'source' => 'wp-config.php',
			);
		}

		if ( defined( 'SMTP_HOST' ) ) {
			return array(
				'host'   => SMTP_HOST,
				'port'   => defined( 'SMTP_PORT' ) ? SMTP_PORT : 587,
				'source' => 'wp-config.php',
			);
		}

		// Check WP Mail SMTP plugin.
		if ( is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) ) {
			$options = get_option( 'wp_mail_smtp', array() );
			if ( ! empty( $options['mail']['smtp_host'] ) ) {
				return array(
					'host'   => $options['mail']['smtp_host'],
					'port'   => $options['mail']['smtp_port'] ?? 587,
					'source' => 'WP Mail SMTP plugin',
				);
			}
		}

		// Check Easy WP SMTP plugin.
		if ( is_plugin_active( 'easy-wp-smtp/easy-wp-smtp.php' ) ) {
			$options = get_option( 'easy_wp_smtp', array() );
			if ( ! empty( $options['smtp_settings']['host'] ) ) {
				return array(
					'host'   => $options['smtp_settings']['host'],
					'port'   => $options['smtp_settings']['port'] ?? 587,
					'source' => 'Easy WP SMTP plugin',
				);
			}
		}

		// Check Post SMTP plugin.
		if ( is_plugin_active( 'post-smtp/postman-smtp.php' ) ) {
			$options = get_option( 'postman_options', array() );
			if ( ! empty( $options['hostname'] ) ) {
				return array(
					'host'   => $options['hostname'],
					'port'   => $options['port'] ?? 587,
					'source' => 'Post SMTP plugin',
				);
			}
		}

		return null;
	}

	/**
	 * Test SMTP connection to a specific host and port.
	 *
	 * @since  1.6035.1730
	 * @param  string $host SMTP host.
	 * @param  int    $port SMTP port.
	 * @return array {
	 *     Connection result.
	 *
	 *     @type bool   $success Whether connection succeeded.
	 *     @type string $error   Error message if failed.
	 * }
	 */
	private static function test_smtp_connection( $host, $port ) {
		$errno  = 0;
		$errstr = '';

		// Suppress warnings, we'll handle errors ourselves.
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$socket = @fsockopen( $host, $port, $errno, $errstr, 10 );

		if ( $socket ) {
			fclose( $socket );
			return array(
				'success' => true,
				'error'   => '',
			);
		}

		return array(
			'success' => false,
			'error'   => $errstr ? $errstr : sprintf(
				/* translators: %d: error number */
				__( 'Connection error %d', 'wpshadow' ),
				$errno
			),
		);
	}
}
