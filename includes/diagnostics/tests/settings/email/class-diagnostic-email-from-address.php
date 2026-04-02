<?php
/**
 * Email From Address Diagnostic
 *
 * Validates that the email "from" address matches the site domain to prevent
 * spoofing and improve deliverability.
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
 * Email From Address Diagnostic Class
 *
 * Checks that emails are sent from a legitimate address matching your domain.
 * This is like making sure your return address on mail matches where you
 * actually live - otherwise it looks suspicious.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Email_From_Address extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-from-address';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email From Address Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates email from address matches site domain';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the email from address diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if from address issues detected, null otherwise.
	 */
	public static function check() {
		$from_email = get_option( 'admin_email' ); // WordPress default from email.
		$site_url   = get_site_url();
		$site_domain = wp_parse_url( $site_url, PHP_URL_HOST );

		$issues = array();

		// Extract domain from email address.
		if ( $from_email && strpos( $from_email, '@' ) !== false ) {
			list( $local, $email_domain ) = explode( '@', $from_email, 2 );

			// Check if email domain matches site domain.
			if ( strtolower( $email_domain ) !== strtolower( $site_domain ) ) {
				// Check if they share the same root domain (e.g., subdomain vs apex).
				$site_root = self::get_root_domain( $site_domain );
				$email_root = self::get_root_domain( $email_domain );

				if ( $site_root !== $email_root ) {
					$issues[] = sprintf(
						/* translators: 1: from email address, 2: site domain */
						__( 'From email (%1$s) domain doesn\'t match site domain (%2$s) - this may trigger spam filters', 'wpshadow' ),
						$from_email,
						$site_domain
					);
				}
			}

			// Check for common problematic addresses.
			$problematic_locals = array( 'noreply', 'no-reply', 'donotreply', 'wordpress' );
			$local_lower = strtolower( $local );

			foreach ( $problematic_locals as $problem ) {
				if ( strpos( $local_lower, $problem ) !== false ) {
					$issues[] = sprintf(
						/* translators: %s: email address */
						__( 'Using "%s" as from address may reduce deliverability - use a monitored address instead', 'wpshadow' ),
						$from_email
					);
					break;
				}
			}
		} else {
			$issues[] = __( 'From email address is not properly configured', 'wpshadow' );
		}

		// Check if wp_mail "from" has been customized.
		$from_customized = false;
		if ( has_filter( 'wp_mail_from' ) || has_filter( 'wp_mail_from_name' ) ) {
			$from_customized = true;
		}

		// Check SMTP plugin configuration.
		$smtp_from = self::get_smtp_from_address();
		if ( $smtp_from && $smtp_from !== $from_email ) {
			$smtp_domain = '';
			if ( strpos( $smtp_from, '@' ) !== false ) {
				list( , $smtp_domain ) = explode( '@', $smtp_from, 2 );
			}

			if ( $smtp_domain && strtolower( $smtp_domain ) !== strtolower( $site_domain ) ) {
				$issues[] = sprintf(
					/* translators: 1: SMTP from address, 2: site domain */
					__( 'SMTP plugin from address (%1$s) doesn\'t match site domain (%2$s)', 'wpshadow' ),
					$smtp_from,
					$site_domain
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your emails are being sent from an address that doesn\'t match your website\'s domain (like sending mail with someone else\'s return address). Email servers are suspicious of this because spammers often fake sender addresses. When your "from" address doesn\'t match your domain, emails are more likely to land in spam folders or be rejected entirely.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
			'severity'     => 'medium',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/email-from-address',
			'context'      => array(
				'site_domain'      => $site_domain,
				'from_email'       => $from_email,
				'smtp_from_email'  => $smtp_from,
				'from_customized'  => $from_customized,
				'issues'           => $issues,
			),
		);
	}

	/**
	 * Extract root domain from a full domain.
	 *
	 * @since 1.6093.1200
	 * @param  string $domain Full domain name.
	 * @return string Root domain.
	 */
	private static function get_root_domain( $domain ) {
		$parts = explode( '.', $domain );
		if ( count( $parts ) <= 2 ) {
			return $domain;
		}

		// Return last two parts (example.com from subdomain.example.com).
		return implode( '.', array_slice( $parts, -2 ) );
	}

	/**
	 * Get SMTP from address from various plugin configurations.
	 *
	 * @since 1.6093.1200
	 * @return string|null SMTP from address or null if not configured.
	 */
	private static function get_smtp_from_address() {
		// Check WP Mail SMTP plugin.
		if ( is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) ) {
			$options = get_option( 'wp_mail_smtp', array() );
			if ( ! empty( $options['mail']['from_email'] ) ) {
				return $options['mail']['from_email'];
			}
		}

		// Check Easy WP SMTP plugin.
		if ( is_plugin_active( 'easy-wp-smtp/easy-wp-smtp.php' ) ) {
			$options = get_option( 'easy_wp_smtp', array() );
			if ( ! empty( $options['from_email_field'] ) ) {
				return $options['from_email_field'];
			}
		}

		// Check Post SMTP plugin.
		if ( is_plugin_active( 'post-smtp/postman-smtp.php' ) ) {
			$options = get_option( 'postman_options', array() );
			if ( ! empty( $options['message_sender_email'] ) ) {
				return $options['message_sender_email'];
			}
		}

		return null;
	}
}
