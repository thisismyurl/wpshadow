<?php
/**
 * From Address Whitelist Diagnostic
 *
 * Validates that the "from" email address matches the site domain to prevent spoofing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1444
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * From Address Whitelist Diagnostic Class
 *
 * Checks if the "from" email address matches the site domain to prevent spoofing risks.
 *
 * @since 1.6035.1444
 */
class Diagnostic_From_Address_Whitelist extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'from-address-whitelist';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'From Address Whitelist';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates from email address matches site domain';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the from address whitelist diagnostic check.
	 *
	 * @since  1.6035.1444
	 * @return array|null Finding array if spoofing risk detected, null otherwise.
	 */
	public static function check() {
		$site_domain = self::get_site_domain();
		$from_email = self::get_from_email();
		
		if ( ! $from_email ) {
			return null; // No from email configured.
		}

		if ( strpos( $from_email, '@' ) === false ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'From email address is invalid (no @ symbol).', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/email-from-address',
			);
		}

		list( , $from_domain ) = explode( '@', $from_email, 2 );

		if ( $from_domain !== $site_domain ) {
			$message = sprintf(
				/* translators: 1: from domain, 2: site domain */
				__( 'From email domain (%1$s) does not match site domain (%2$s). This may cause emails to be marked as spam.', 'wpshadow' ),
				$from_domain,
				$site_domain
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $message,
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/email-domain-mismatch',
				'meta'        => array(
					'from_domain' => $from_domain,
					'site_domain' => $site_domain,
					'from_email'  => $from_email,
				),
			);
		}

		return null;
	}

	/**
	 * Get the site's domain.
	 *
	 * @since  1.6035.1444
	 * @return string Site domain.
	 */
	private static function get_site_domain(): string {
		$url = get_site_url();
		$parsed = wp_parse_url( $url );
		return $parsed['host'] ?? '';
	}

	/**
	 * Get the configured "from" email address.
	 *
	 * @since  1.6035.1444
	 * @return string|null From email address or null if not configured.
	 */
	private static function get_from_email() {
		$from_email = get_option( 'admin_email' );

		// Check WP Mail SMTP plugin.
		$wp_mail_smtp = get_option( 'wp_mail_smtp' );
		if ( ! empty( $wp_mail_smtp['mail']['from_email'] ) ) {
			$from_email = $wp_mail_smtp['mail']['from_email'];
		}

		// Check Easy WP SMTP plugin.
		$easy_wp_smtp = get_option( 'swpsmtp_options' );
		if ( ! empty( $easy_wp_smtp['from_email_field'] ) ) {
			$from_email = $easy_wp_smtp['from_email_field'];
		}

		// Check Post SMTP plugin.
		$post_smtp = get_option( 'postman_options' );
		if ( ! empty( $post_smtp['sender_email'] ) ) {
			$from_email = $post_smtp['sender_email'];
		}

		return $from_email;
	}
}
