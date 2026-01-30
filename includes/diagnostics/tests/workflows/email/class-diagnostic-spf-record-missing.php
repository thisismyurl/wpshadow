<?php
/**
 * SPF Record Missing or Invalid Diagnostic
 *
 * Validates domain has proper SPF (Sender Policy Framework) record to prevent
 * emails being marked as spam and improve deliverability.
 *
 * SPF is the foundation of email authentication, specifying which servers
 * are authorized to send email from the domain. Misconfiguration accounts
 * for 70% of email deliverability issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Email
 * @since      1.6028.2052
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_SPF_Record_Missing Class
 *
 * Queries DNS for SPF TXT record, validates syntax, and verifies
 * current server/SMTP provider is included.
 *
 * @since 1.6028.2052
 */
class Diagnostic_SPF_Record_Missing extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'spf-record-missing';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SPF Record Missing or Invalid';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates domain has proper SPF record for email authentication';

	/**
	 * Diagnostic family/category
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the SPF record diagnostic check.
	 *
	 * Queries DNS for TXT record containing v=spf1, validates syntax,
	 * and checks if current server IP is included.
	 *
	 * @since  1.6028.2052
	 * @return array|null Finding array if SPF issue detected, null if properly configured.
	 */
	public static function check() {
		$domain   = self::get_site_domain();
		$spf_data = self::check_spf_record( $domain );

		if ( ! $spf_data['exists'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: domain name */
					__( 'No SPF record found for domain %s. SPF prevents your emails from being marked as spam and is the foundation of email authentication.', 'wpshadow' ),
					esc_html( $domain )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'solution'     => array(
					'free'     => array(
						'heading'     => __( 'Create SPF DNS Record', 'wpshadow' ),
						'description' => sprintf(
							/* translators: 1: server IP, 2: domain */
							__( 'Add TXT record to domain DNS: v=spf1 ip4:%1$s ~all (replace IP with your server IP: %1$s)', 'wpshadow' ),
							esc_html( $spf_data['server_ip'] ),
							esc_html( $domain )
						),
						'steps'       => array(
							__( 'Determine your email sending method (server, SMTP service)', 'wpshadow' ),
							sprintf(
								/* translators: %s: server IP */
								__( 'For server: v=spf1 ip4:%s ~all', 'wpshadow' ),
								esc_html( $spf_data['server_ip'] )
							),
							__( 'For Gmail: v=spf1 include:_spf.google.com ~all', 'wpshadow' ),
							__( 'For SendGrid: v=spf1 include:sendgrid.net ~all', 'wpshadow' ),
							__( 'Add TXT record to domain DNS', 'wpshadow' ),
							__( 'Wait 24-48 hours for DNS propagation', 'wpshadow' ),
							__( 'Test with MXToolbox SPF Lookup', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'heading'     => __( 'Multiple Sender SPF Setup', 'wpshadow' ),
						'description' => __( 'Combine multiple senders: v=spf1 ip4:server include:smtp.service ~all (max 10 DNS lookups)', 'wpshadow' ),
					),
					'advanced' => array(
						'heading'     => __( 'SPF Flattening for Complex Setups', 'wpshadow' ),
						'description' => __( 'Use SPF flattening services to resolve includes and stay within 10 DNS lookup limit.', 'wpshadow' ),
					),
				),
				'details'      => array(
					'domain'    => $domain,
					'server_ip' => $spf_data['server_ip'],
					'impact'    => __( 'Accounts for 70% of email deliverability issues', 'wpshadow' ),
				),
				'resource_links' => array(
					array(
						'title' => __( 'SPF Record Syntax', 'wpshadow' ),
						'url'   => 'https://www.spf-record.com/',
					),
					array(
						'title' => __( 'MXToolbox SPF Check', 'wpshadow' ),
						'url'   => 'https://mxtoolbox.com/spf.aspx',
					),
					array(
						'title' => __( 'SPF Record Generator', 'wpshadow' ),
						'url'   => 'https://www.spfwizard.net/',
					),
				),
				'kb_link'      => 'https://wpshadow.com/kb/spf-record-configuration',
			);
		}

		// SPF exists - validate syntax and configuration.
		$validation = self::validate_spf_record( $spf_data['record'], $spf_data['server_ip'] );

		if ( ! $validation['valid'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => __( 'SPF Record Invalid', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: validation error message */
					__( 'SPF record exists but has issues: %s', 'wpshadow' ),
					esc_html( implode( ', ', $validation['errors'] ) )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'solution'     => array(
					'free'     => array(
						'heading'     => __( 'Fix SPF Record Syntax', 'wpshadow' ),
						'description' => __( 'Correct SPF record syntax errors and validate configuration.', 'wpshadow' ),
						'steps'       => array_map(
							function( $error ) {
								return sprintf(
									/* translators: %s: error message */
									__( 'Fix: %s', 'wpshadow' ),
									$error
								);
							},
							$validation['errors']
						),
					),
					'premium'  => array(
						'heading'     => __( 'SPF Record Validation Service', 'wpshadow' ),
						'description' => __( 'Use automated SPF validation and monitoring to catch issues before they affect deliverability.', 'wpshadow' ),
					),
					'advanced' => array(
						'heading'     => __( 'SPF Macro Implementation', 'wpshadow' ),
						'description' => __( 'Use SPF macros for dynamic IP resolution and complex routing scenarios.', 'wpshadow' ),
					),
				),
				'details'      => array(
					'domain'     => $domain,
					'spf_record' => $spf_data['record'],
					'errors'     => $validation['errors'],
					'warnings'   => $validation['warnings'],
				),
				'resource_links' => array(
					array(
						'title' => __( 'SPF Syntax Reference', 'wpshadow' ),
						'url'   => 'https://www.spf-record.com/syntax',
					),
				),
				'kb_link'      => 'https://wpshadow.com/kb/spf-record-troubleshooting',
			);
		}

		return null; // SPF configured properly.
	}

	/**
	 * Get site domain from WordPress home URL.
	 *
	 * @since  1.6028.2052
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
	 * Check SPF DNS record for domain.
	 *
	 * @since  1.6028.2052
	 * @param  string $domain Domain to check.
	 * @return array {
	 *     SPF record data.
	 *
	 *     @type bool   $exists    Whether SPF record exists.
	 *     @type string $record    Full SPF TXT record.
	 *     @type string $server_ip Current server IP address.
	 * }
	 */
	private static function check_spf_record( $domain ) {
		$records = dns_get_record( $domain, DNS_TXT ); // phpcs:ignore WordPress.WP.AlternativeFunctions.dns_get_record_dns_get_record

		$spf_record = '';
		if ( $records ) {
			foreach ( $records as $record ) {
				if ( isset( $record['txt'] ) && 0 === strpos( $record['txt'], 'v=spf1' ) ) {
					$spf_record = $record['txt'];
					break;
				}
			}
		}

		$server_ip = isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : '';

		return array(
			'exists'    => ! empty( $spf_record ),
			'record'    => $spf_record,
			'server_ip' => $server_ip,
		);
	}

	/**
	 * Validate SPF record syntax and configuration.
	 *
	 * @since  1.6028.2052
	 * @param  string $spf_record SPF record to validate.
	 * @param  string $server_ip  Current server IP.
	 * @return array {
	 *     Validation results.
	 *
	 *     @type bool  $valid    Whether record is valid.
	 *     @type array $errors   Critical validation errors.
	 *     @type array $warnings Non-critical warnings.
	 * }
	 */
	private static function validate_spf_record( $spf_record, $server_ip ) {
		$errors   = array();
		$warnings = array();

		// Check for v=spf1 prefix.
		if ( 0 !== strpos( $spf_record, 'v=spf1' ) ) {
			$errors[] = __( 'Must start with v=spf1', 'wpshadow' );
		}

		// Check for all/redirect terminator.
		if ( ! preg_match( '/[~\-\+\?]all|redirect=/', $spf_record ) ) {
			$errors[] = __( 'Missing all or redirect mechanism', 'wpshadow' );
		}

		// Count DNS lookups (include and redirect).
		$lookups = preg_match_all( '/include:|redirect=|a:|mx:|ptr:/', $spf_record );
		if ( $lookups > 10 ) {
			$errors[] = sprintf(
				/* translators: %d: number of DNS lookups */
				__( 'Too many DNS lookups (%d > 10 limit)', 'wpshadow' ),
				$lookups
			);
		}

		// Check if server IP included.
		if ( ! empty( $server_ip ) && ! preg_match( '/ip4:' . preg_quote( $server_ip, '/' ) . '/', $spf_record ) ) {
			$warnings[] = sprintf(
				/* translators: %s: server IP */
				__( 'Current server IP %s not explicitly included', 'wpshadow' ),
				$server_ip
			);
		}

		// Check for soft fail vs hard fail.
		if ( preg_match( '/~all/', $spf_record ) ) {
			$warnings[] = __( 'Using ~all (soft fail) - consider -all (hard fail) for better security', 'wpshadow' );
		}

		return array(
			'valid'    => empty( $errors ),
			'errors'   => $errors,
			'warnings' => $warnings,
		);
	}
}
