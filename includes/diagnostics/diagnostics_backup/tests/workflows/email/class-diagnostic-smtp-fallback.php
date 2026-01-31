<?php
/**
 * Diagnostic: SMTP Fallback Test
 *
 * Checks whether SMTP settings are missing and mail will fallback to PHP mail().
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
 * Class Diagnostic_Smtp_Fallback
 *
 * Tests if mail will fallback to PHP mail() due to missing SMTP config.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Smtp_Fallback extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'smtp-fallback';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'SMTP Fallback Test';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mail will fallback to PHP mail() due to missing SMTP configuration';

	/**
	 * Check SMTP fallback conditions.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$host = get_option( 'mailserver_url', 'mail.example.com' );
		$user = get_option( 'mailserver_login', '' );
		$pass = get_option( 'mailserver_pass', '' );

		$using_defaults = ( 'mail.example.com' === $host );
		$missing_creds  = empty( $user ) || empty( $pass );

		if ( $using_defaults || $missing_creds ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'SMTP settings are incomplete. WordPress will fallback to PHP mail(), which is often blocked or unreliable. Configure SMTP host, port, username, and password.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/smtp_fallback',
				'meta'        => array(
					'host'         => $host,
					'has_username' => ! empty( $user ),
					'has_password' => ! empty( $pass ),
				),
			);
		}

		return null;
	}
}
