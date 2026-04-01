<?php
/**
 * Email SMTP Working Diagnostic
 *
 * Checks whether SMTP configuration is active for reliable email delivery.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Email_SMTP_Working Class
 *
 * Verifies that SMTP is configured via common plugins.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Email_SMTP_Working extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-smtp-working';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email SMTP Working';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether SMTP is configured for email delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
			'post-smtp/postman-smtp.php'    => 'Post SMTP',
			'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP',
			'fluent-smtp/fluent-smtp.php'   => 'FluentSMTP',
		);

		$active_smtp = null;
		foreach ( $smtp_plugins as $plugin => $label ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_smtp = $label;
				break;
			}
		}

		if ( null === $active_smtp ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No SMTP plugin detected. Configure SMTP to improve email deliverability.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-smtp-working?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		$wp_mail_smtp = get_option( 'wp_mail_smtp', array() );
		if ( 'WP Mail SMTP' === $active_smtp && empty( $wp_mail_smtp['mail']['mailer'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WP Mail SMTP is active but not fully configured. Complete SMTP setup.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-smtp-working?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}