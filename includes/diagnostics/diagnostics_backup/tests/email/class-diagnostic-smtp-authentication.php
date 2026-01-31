<?php
/**
 * Diagnostic: SMTP Authentication
 *
 * Checks if SMTP credentials are configured in WordPress settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Email
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Smtp_Authentication
 *
 * Tests if SMTP settings (host/login) are configured.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Smtp_Authentication extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'smtp-authentication';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'SMTP Authentication';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SMTP credentials are configured';

	/**
	 * Check SMTP settings from WordPress mail options.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$host  = get_option( 'mailserver_url', 'mail.example.com' );
		$user  = get_option( 'mailserver_login', '' );
		$pass  = get_option( 'mailserver_pass', '' );
		$port  = (int) get_option( 'mailserver_port', 25 );

		$using_defaults = ( 'mail.example.com' === $host );

		if ( $using_defaults || empty( $user ) || empty( $pass ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'SMTP authentication is not configured. Outgoing mail will fall back to PHP mail, which is less reliable. Set SMTP host, username, password, and port.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/smtp_authentication',
				'meta'        => array(
					'host'           => $host,
					'port'           => $port,
					'has_username'   => ! empty( $user ),
					'has_password'   => ! empty( $pass ),
				),
			);
		}

		return null;
	}
}
