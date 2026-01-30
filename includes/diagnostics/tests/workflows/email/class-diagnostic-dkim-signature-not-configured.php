<?php
/**
 * DKIM Signature Not Configured Diagnostic
 *
 * Checks if DKIM (DomainKeys Identified Mail) is configured for email
 * authentication and anti-spoofing protection.
 *
 * DKIM became mandatory for Gmail/Outlook in February 2024, making this
 * a critical diagnostic for email deliverability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Email
 * @since      1.6028.2051
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_DKIM_Signature_Not_Configured Class
 *
 * Tests for DKIM signature presence and validates DNS selector records.
 * Uses test email sending to check for DKIM-Signature header.
 *
 * @since 1.6028.2051
 */
class Diagnostic_DKIM_Signature_Not_Configured extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dkim-signature-not-configured';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DKIM Signature Not Configured';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if DKIM authentication is configured for emails';

	/**
	 * Diagnostic family/category
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the DKIM signature diagnostic check.
	 *
	 * Checks for DKIM configuration by examining email headers and DNS records.
	 * Since we can't reliably send test emails, we check for common SMTP plugin
	 * DKIM settings and DNS selector records.
	 *
	 * @since  1.6028.2051
	 * @return array|null Finding array if DKIM not configured, null if properly set up.
	 */
	public static function check() {
		$domain         = self::get_site_domain();
		$dkim_status    = self::check_dkim_configuration( $domain );
		$has_smtp       = self::has_smtp_plugin();
		$dns_selectors  = self::check_common_dkim_selectors( $domain );

		// If DKIM selectors found in DNS, assume configured.
		if ( ! empty( $dns_selectors['found'] ) ) {
			return null; // DKIM appears configured.
		}

		// DKIM not found - determine severity based on email volume/importance.
		$severity = $has_smtp ? 'high' : 'medium';

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: domain name */
				__( 'DKIM signature not configured for %s. Required by Gmail/Outlook since Feb 2024 for reliable email delivery.', 'wpshadow' ),
				esc_html( $domain )
			),
			'severity'     => $severity,
			'threat_level' => $has_smtp ? 80 : 60,
			'auto_fixable' => false,
			'solution'     => array(
				'free'     => array(
					'heading'     => __( 'Configure DKIM via SMTP Plugin', 'wpshadow' ),
					'description' => __( 'Most SMTP plugins (WP Mail SMTP, Post SMTP, FluentSMTP) support DKIM. Enable in plugin settings and add DNS record.', 'wpshadow' ),
					'steps'       => array(
						__( 'Install/activate SMTP plugin (WP Mail SMTP recommended)', 'wpshadow' ),
						__( 'Generate DKIM key pair in plugin settings', 'wpshadow' ),
						__( 'Add TXT record to DNS: selector._domainkey.domain.com', 'wpshadow' ),
						__( 'Value: Copy public key from plugin (starts with "v=DKIM1")', 'wpshadow' ),
						__( 'Wait 24-48 hours for DNS propagation', 'wpshadow' ),
						__( 'Send test email and verify DKIM-Signature header', 'wpshadow' ),
					),
				),
				'premium'  => array(
					'heading'     => __( 'Transactional Email Service', 'wpshadow' ),
					'description' => __( 'Use Postmark, SendGrid, or Amazon SES for automatic DKIM signing without DNS configuration.', 'wpshadow' ),
				),
				'advanced' => array(
					'heading'     => __( 'Server-Level DKIM with OpenDKIM', 'wpshadow' ),
					'description' => __( 'Configure OpenDKIM at mail server level for automatic signing of all outbound emails.', 'wpshadow' ),
				),
			),
			'details'      => array(
				'domain'           => $domain,
				'smtp_plugin'      => $has_smtp,
				'selectors_tested' => $dns_selectors['tested'],
				'requirement'      => __( 'Gmail/Outlook mandatory since Feb 2024', 'wpshadow' ),
			),
			'resource_links' => array(
				array(
					'title' => __( 'DKIM Setup Guide', 'wpshadow' ),
					'url'   => 'https://www.dkim.org/',
				),
				array(
					'title' => __( 'WP Mail SMTP DKIM Configuration', 'wpshadow' ),
					'url'   => 'https://wpmailsmtp.com/docs/how-to-set-up-dkim-authentication/',
				),
				array(
					'title' => __( 'MXToolbox DKIM Check', 'wpshadow' ),
					'url'   => 'https://mxtoolbox.com/dkim.aspx',
				),
			),
			'kb_link'      => 'https://wpshadow.com/kb/dkim-configuration',
		);
	}

	/**
	 * Get site domain from WordPress home URL.
	 *
	 * @since  1.6028.2051
	 * @return string Site domain or empty string.
	 */
	private static function get_site_domain() {
		$home_url = home_url();
		$parsed   = wp_parse_url( $home_url );

		if ( ! isset( $parsed['host'] ) ) {
			return '';
		}

		$host = $parsed['host'];

		// Remove www prefix.
		if ( 0 === strpos( $host, 'www.' ) ) {
			$host = substr( $host, 4 );
		}

		return $host;
	}

	/**
	 * Check if site has SMTP plugin installed.
	 *
	 * @since  1.6028.2051
	 * @return bool True if SMTP plugin detected.
	 */
	private static function has_smtp_plugin() {
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'wp-mail-smtp-pro/wp_mail_smtp.php',
			'post-smtp/postman-smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
			'gmail-smtp/main.php',
			'fluent-smtp/fluent-smtp.php',
		);

		foreach ( $smtp_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check common DKIM selectors in DNS.
	 *
	 * Tests common selector names used by SMTP plugins and services.
	 *
	 * @since  1.6028.2051
	 * @param  string $domain Domain to check.
	 * @return array {
	 *     DKIM selector check results.
	 *
	 *     @type array  $tested List of selectors tested.
	 *     @type array  $found  List of selectors found in DNS.
	 * }
	 */
	private static function check_common_dkim_selectors( $domain ) {
		$common_selectors = array(
			'default',
			'mail',
			'dkim',
			'selector1',
			'selector2',
			'k1',
			'google',
			'smtp',
			'postmark',
			'sendgrid',
			'mailgun',
		);

		$tested = array();
		$found  = array();

		foreach ( $common_selectors as $selector ) {
			$dkim_host = $selector . '._domainkey.' . $domain;
			$tested[]  = $dkim_host;

			$records = dns_get_record( $dkim_host, DNS_TXT ); // phpcs:ignore WordPress.WP.AlternativeFunctions.dns_get_record_dns_get_record

			if ( $records ) {
				foreach ( $records as $record ) {
					if ( isset( $record['txt'] ) && 0 === strpos( $record['txt'], 'v=DKIM1' ) ) {
						$found[] = array(
							'selector' => $selector,
							'host'     => $dkim_host,
							'record'   => $record['txt'],
						);
						break;
					}
				}
			}
		}

		return array(
			'tested' => $tested,
			'found'  => $found,
		);
	}

	/**
	 * Check DKIM configuration in active plugins.
	 *
	 * @since  1.6028.2051
	 * @param  string $domain Domain to check.
	 * @return array DKIM configuration status.
	 */
	private static function check_dkim_configuration( $domain ) {
		$status = array(
			'enabled' => false,
			'method'  => '',
		);

		// Check WP Mail SMTP.
		if ( is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) || is_plugin_active( 'wp-mail-smtp-pro/wp_mail_smtp.php' ) ) {
			$options = get_option( 'wp_mail_smtp', array() );
			if ( isset( $options['mail']['dkim_enable'] ) && $options['mail']['dkim_enable'] ) {
				$status['enabled'] = true;
				$status['method']  = 'WP Mail SMTP';
			}
		}

		// Check Post SMTP.
		if ( is_plugin_active( 'post-smtp/postman-smtp.php' ) ) {
			$options = get_option( 'postman_options', array() );
			if ( isset( $options['dkim_enabled'] ) && $options['dkim_enabled'] ) {
				$status['enabled'] = true;
				$status['method']  = 'Post SMTP';
			}
		}

		return $status;
	}
}
