<?php
/**
 * SMTP Server Connectivity Diagnostic
 *
 * Tests SMTP server connectivity to ensure emails can be sent.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
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
 * Verifies that the configured SMTP server is reachable and can accept connections.
 *
 * @since 1.6093.1200
 */
class Diagnostic_SMTP_Server_Connectivity extends Diagnostic_Base {

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
	protected static $description = 'Verifies SMTP server is reachable for email delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the SMTP server connectivity diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if connectivity issue detected, null otherwise.
	 */
	public static function check() {
		$smtp_config = self::get_smtp_configuration();
		
		if ( empty( $smtp_config['host'] ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No SMTP server configured. WordPress will attempt to use PHP mail() which may be unreliable.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/smtp-server-configuration',
			);
		}

		$host = $smtp_config['host'];
		$port = $smtp_config['port'];
		$timeout = 10;

		// Test connection to SMTP server.
		$errno = 0;
		$errstr = '';
		$connection = @fsockopen( $host, $port, $errno, $errstr, $timeout );

		if ( ! $connection ) {
			$message = sprintf(
				/* translators: 1: SMTP host, 2: port, 3: error message */
				__( 'Cannot connect to SMTP server %1$s:%2$d. Error: %3$s', 'wpshadow' ),
				$host,
				$port,
				$errstr ? $errstr : __( 'Connection timeout', 'wpshadow' )
			);

			// Try fallback ports if configured port failed.
			$fallback_ports = array( 25, 465, 587 );
			$fallback_ports = array_diff( $fallback_ports, array( $port ) );

			foreach ( $fallback_ports as $fallback_port ) {
				$test_connection = @fsockopen( $host, $fallback_port, $errno, $errstr, $timeout );
				if ( $test_connection ) {
					fclose( $test_connection );
					$message .= ' ' . sprintf(
						/* translators: %d: port number */
						__( 'However, port %d is accessible.', 'wpshadow' ),
						$fallback_port
					);
					break;
				}
			}

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $message,
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/smtp-connectivity-issues',
				'meta'        => array(
					'host' => $host,
					'port' => $port,
				),
			);
		}

		fclose( $connection );
		return null;
	}

	/**
	 * Get SMTP configuration from various sources.
	 *
	 * @since 1.6093.1200
	 * @return array SMTP configuration with host and port.
	 */
	private static function get_smtp_configuration(): array {
		$config = array(
			'host' => '',
			'port' => 25,
		);

		// Check wp-config.php constants.
		if ( defined( 'WPMS_ON' ) && WPMS_ON && defined( 'WPMS_SMTP_HOST' ) ) {
			$config['host'] = WPMS_SMTP_HOST;
			if ( defined( 'WPMS_SMTP_PORT' ) ) {
				$config['port'] = (int) WPMS_SMTP_PORT;
			}
			return $config;
		}

		// Check WP Mail SMTP plugin.
		$wp_mail_smtp = get_option( 'wp_mail_smtp' );
		if ( ! empty( $wp_mail_smtp['mail']['mailer'] ) && 'smtp' === $wp_mail_smtp['mail']['mailer'] ) {
			if ( ! empty( $wp_mail_smtp['smtp']['host'] ) ) {
				$config['host'] = $wp_mail_smtp['smtp']['host'];
			}
			if ( ! empty( $wp_mail_smtp['smtp']['port'] ) ) {
				$config['port'] = (int) $wp_mail_smtp['smtp']['port'];
			}
			return $config;
		}

		// Check Easy WP SMTP plugin.
		$easy_wp_smtp = get_option( 'swpsmtp_options' );
		if ( ! empty( $easy_wp_smtp['smtp_settings']['host'] ) ) {
			$config['host'] = $easy_wp_smtp['smtp_settings']['host'];
			if ( ! empty( $easy_wp_smtp['smtp_settings']['port'] ) ) {
				$config['port'] = (int) $easy_wp_smtp['smtp_settings']['port'];
			}
			return $config;
		}

		// Check Post SMTP plugin.
		$post_smtp = get_option( 'postman_options' );
		if ( ! empty( $post_smtp['hostname'] ) ) {
			$config['host'] = $post_smtp['hostname'];
			if ( ! empty( $post_smtp['port'] ) ) {
				$config['port'] = (int) $post_smtp['port'];
			}
			return $config;
		}

		return $config;
	}
}
