<?php
/**
 * Email Delivery Test Diagnostic
 *
 * Checks whether outbound email is likely to deliver by verifying mail
 * configuration and SMTP setup.
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
 * Diagnostic_Email_Delivery_Test Class
 *
 * Evaluates email delivery readiness using WordPress APIs and common
 * SMTP plugin detection. Avoids sending test emails.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Email_Delivery_Test extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-delivery-test';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Delivery Test';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether email delivery is likely configured correctly';

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
		if ( ! function_exists( 'wp_mail' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'wp_mail is not available. Email delivery is disabled.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-delivery-test?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

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

		$from_email = apply_filters( 'wp_mail_from', get_option( 'admin_email' ) );
		$site_domain = parse_url( home_url(), PHP_URL_HOST );
		$from_domain = is_string( $from_email ) && strpos( $from_email, '@' ) !== false ? substr( $from_email, strpos( $from_email, '@' ) + 1 ) : '';

		if ( null === $active_smtp ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No SMTP plugin detected. Default PHP mail often fails delivery or lands in spam.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-delivery-test?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'from_email'  => $from_email,
					'from_domain' => $from_domain,
					'site_domain' => $site_domain,
				),
			);
		}

		if ( $site_domain && $from_domain && $from_domain !== $site_domain ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Email From address does not match the site domain. This can reduce deliverability.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-delivery-test?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'from_email'  => $from_email,
					'from_domain' => $from_domain,
					'site_domain' => $site_domain,
					'smtp_plugin' => $active_smtp,
				),
			);
		}

		return null;
	}
}