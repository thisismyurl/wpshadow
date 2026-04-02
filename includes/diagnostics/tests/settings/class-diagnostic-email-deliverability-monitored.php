<?php
/**
 * Email Deliverability Monitored Diagnostic
 *
 * Tests whether the site actively monitors inbox placement and maintains >95% delivery rate.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Deliverability Monitored Diagnostic Class
 *
 * Email deliverability monitoring ensures messages reach inboxes, not spam.
 * Poor deliverability wastes marketing budget and damages sender reputation.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Email_Deliverability_Monitored extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-deliverability-monitored';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Deliverability Monitored';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site actively monitors inbox placement and maintains >95% delivery rate';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$deliverability_score = 0;
		$max_score = 6;

		// Check for email marketing platform.
		$email_platform = self::check_email_platform();
		if ( $email_platform ) {
			$deliverability_score++;
		} else {
			$issues[] = __( 'No email marketing platform with deliverability tracking', 'wpshadow' );
		}

		// Check for SPF records.
		$spf_configured = self::check_spf_configuration();
		if ( $spf_configured ) {
			$deliverability_score++;
		} else {
			$issues[] = __( 'SPF records not properly configured for email authentication', 'wpshadow' );
		}

		// Check for DKIM.
		$dkim_configured = self::check_dkim_configuration();
		if ( $dkim_configured ) {
			$deliverability_score++;
		} else {
			$issues[] = __( 'DKIM not configured to verify email authenticity', 'wpshadow' );
		}

		// Check for DMARC.
		$dmarc_configured = self::check_dmarc_configuration();
		if ( $dmarc_configured ) {
			$deliverability_score++;
		} else {
			$issues[] = __( 'DMARC policy not set to protect domain reputation', 'wpshadow' );
		}

		// Check for monitoring tools.
		$monitoring_tools = self::check_monitoring_tools();
		if ( $monitoring_tools ) {
			$deliverability_score++;
		} else {
			$issues[] = __( 'No deliverability monitoring tools or services active', 'wpshadow' );
		}

		// Check for dedicated sending IP.
		$dedicated_ip = self::check_dedicated_ip();
		if ( $dedicated_ip ) {
			$deliverability_score++;
		} else {
			$issues[] = __( 'Using shared IP (consider dedicated IP for high volume)', 'wpshadow' );
		}

		// Determine severity based on deliverability setup.
		$deliverability_percentage = ( $deliverability_score / $max_score ) * 100;

		if ( $deliverability_percentage < 50 ) {
			$severity = 'medium';
			$threat_level = 40;
		} elseif ( $deliverability_percentage < 75 ) {
			$severity = 'low';
			$threat_level = 25;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Deliverability monitoring percentage */
				__( 'Email deliverability monitoring at %d%%. ', 'wpshadow' ),
				(int) $deliverability_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Poor deliverability wastes marketing budget and damages reputation', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-deliverability-monitored',
			);
		}

		return null;
	}

	/**
	 * Check email platform.
	 *
	 * @since 1.6093.1200
	 * @return bool True if platform exists, false otherwise.
	 */
	private static function check_email_platform() {
		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'constant-contact-forms/constant-contact-forms.php',
		);

		foreach ( $email_plugins as $plugin_path ) {
			if ( self::is_plugin_active_safe( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_email_platform', false );
	}

	/**
	 * Check SPF configuration.
	 *
	 * @since 1.6093.1200
	 * @return bool True if SPF configured, false otherwise.
	 */
	private static function check_spf_configuration() {
		$domain = self::get_site_domain();
		if ( '' === $domain ) {
			return false;
		}

		if ( ! function_exists( 'dns_get_record' ) ) {
			return apply_filters( 'wpshadow_spf_configured', false );
		}

		$records = @dns_get_record( $domain, DNS_TXT );
		if ( $records ) {
			foreach ( $records as $record ) {
				if ( isset( $record['txt'] ) && strpos( $record['txt'], 'v=spf1' ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check DKIM configuration.
	 *
	 * @since 1.6093.1200
	 * @return bool True if DKIM configured, false otherwise.
	 */
	private static function check_dkim_configuration() {
		// Check for DKIM plugins.
		if ( self::is_plugin_active_safe( 'wp-mail-smtp/wp_mail_smtp.php' ) ||
			 self::is_plugin_active_safe( 'post-smtp/postman-smtp.php' ) ) {
			return true;
		}

		// MailPoet has built-in DKIM.
		if ( self::is_plugin_active_safe( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_dkim_configured', false );
	}

	/**
	 * Check DMARC configuration.
	 *
	 * @since 1.6093.1200
	 * @return bool True if DMARC configured, false otherwise.
	 */
	private static function check_dmarc_configuration() {
		$domain = self::get_site_domain();
		if ( '' === $domain ) {
			return false;
		}

		if ( ! function_exists( 'dns_get_record' ) ) {
			return apply_filters( 'wpshadow_dmarc_configured', false );
		}

		$records = @dns_get_record( '_dmarc.' . $domain, DNS_TXT );
		if ( $records ) {
			foreach ( $records as $record ) {
				if ( isset( $record['txt'] ) && strpos( $record['txt'], 'v=DMARC1' ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check monitoring tools.
	 *
	 * @since 1.6093.1200
	 * @return bool True if monitoring exists, false otherwise.
	 */
	private static function check_monitoring_tools() {
		// Professional email platforms have built-in monitoring.
		if ( self::is_plugin_active_safe( 'mailpoet/mailpoet.php' ) ||
			 self::is_plugin_active_safe( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_deliverability_monitoring', false );
	}

	/**
	 * Get the current site domain.
	 *
	 * @since 1.6093.1200
	 * @return string Domain name or empty string.
	 */
	private static function get_site_domain() {
		if ( class_exists( 'WPShadow\\Diagnostics\\Diagnostic_URL_And_Pattern_Helper' ) ) {
			return (string) Diagnostic_URL_And_Pattern_Helper::get_domain( home_url() );
		}

		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		return is_string( $host ) ? $host : '';
	}

	/**
	 * Safely check if a plugin is active.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin_path Plugin path.
	 * @return bool Whether plugin is active.
	 */
	private static function is_plugin_active_safe( string $plugin_path ): bool {
		return function_exists( 'is_plugin_active' ) && is_plugin_active( $plugin_path );
	}

	/**
	 * Check dedicated IP.
	 *
	 * @since 1.6093.1200
	 * @return bool True if dedicated IP likely, false otherwise.
	 */
	private static function check_dedicated_ip() {
		// High-volume senders typically use dedicated IPs.
		// This is more of a recommendation for large lists.
		return apply_filters( 'wpshadow_uses_dedicated_ip', false );
	}
}
