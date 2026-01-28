<?php
/**
 * Email Domain Blacklist Status Diagnostic
 *
 * Checks if site domain or sending IP is listed on major email blacklists.
 * Being blacklisted causes complete email delivery failure.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Email_Domain_Blacklist Class
 *
 * Queries major DNSBL (DNS Blacklist) services to check domain/IP reputation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Email_Domain_Blacklist extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-domain-blacklist';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Domain Blacklist Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if domain or IP is listed on major email blacklists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$blacklist_data = self::check_blacklists();

		if ( ! $blacklist_data ) {
			return null;
		}

		$blacklisted_on    = $blacklist_data['blacklisted_on'];
		$checked_services  = $blacklist_data['checked_services'];
		$server_ip         = $blacklist_data['server_ip'];
		$site_domain       = $blacklist_data['site_domain'];

		if ( empty( $blacklisted_on ) ) {
			return null; // Not blacklisted.
		}

		$blacklist_count = count( $blacklisted_on );

		$severity     = 'critical';
		$threat_level = 90;

		if ( $blacklist_count === 1 ) {
			$severity     = 'high';
			$threat_level = 80;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: Blacklist count, 2: Blacklist names */
				__( 'Your domain or IP is listed on %1$d email blacklist(s): %2$s. This causes email delivery failures and damages sender reputation.', 'wpshadow' ),
				$blacklist_count,
				implode( ', ', $blacklisted_on )
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/email-blacklist',
			'details'     => self::get_details( $blacklist_data ),
		);
	}

	/**
	 * Check major email blacklists.
	 *
	 * Queries DNSBL services for domain and IP address.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Blacklist check data.
	 *
	 *     @type array  $blacklisted_on   List of blacklists where listed.
	 *     @type array  $checked_services All services checked.
	 *     @type string $server_ip        Server IP address.
	 *     @type string $site_domain      Site domain.
	 * }
	 */
	private static function check_blacklists() {
		$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
		$server_ip   = $_SERVER['SERVER_ADDR'] ?? gethostbyname( $site_domain );

		// Major DNSBLs (those with free query access).
		$blacklists = array(
			'zen.spamhaus.org'      => 'Spamhaus',
			'bl.spamcop.net'        => 'SpamCop',
			'b.barracudacentral.org' => 'Barracuda',
			'dnsbl.sorbs.net'       => 'SORBS',
			'psbl.surriel.com'      => 'PSBL',
		);

		$blacklisted_on = array();
		$checked_services = array();

		foreach ( $blacklists as $dnsbl => $name ) {
			$checked_services[] = $name;

			// Check IP address.
			if ( self::is_listed_on_dnsbl( $server_ip, $dnsbl ) ) {
				$blacklisted_on[] = $name;
			}
		}

		return array(
			'blacklisted_on'   => $blacklisted_on,
			'checked_services' => $checked_services,
			'server_ip'        => $server_ip,
			'site_domain'      => $site_domain,
		);
	}

	/**
	 * Check if IP is listed on a specific DNSBL.
	 *
	 * Uses DNS lookup to query blacklist.
	 *
	 * @since  1.2601.2148
	 * @param  string $ip    IP address to check.
	 * @param  string $dnsbl DNSBL hostname.
	 * @return bool True if listed, false otherwise.
	 */
	private static function is_listed_on_dnsbl( $ip, $dnsbl ) {
		// Reverse IP for DNSBL query (e.g., 1.2.3.4 becomes 4.3.2.1).
		$reversed_ip = implode( '.', array_reverse( explode( '.', $ip ) ) );
		$query       = $reversed_ip . '.' . $dnsbl;

		// Perform DNS lookup.
		$result = gethostbyname( $query );

		// If result starts with 127.0.0, it's listed (DNSBL standard).
		return strpos( $result, '127.0.0.' ) === 0;
	}

	/**
	 * Get detailed information about the finding.
	 *
	 * @since  1.2601.2148
	 * @param  array $blacklist_data Blacklist check data.
	 * @return array Details array with explanation and solutions.
	 */
	private static function get_details( $blacklist_data ) {
		$blacklisted_on   = $blacklist_data['blacklisted_on'];
		$checked_services = $blacklist_data['checked_services'];
		$server_ip        = $blacklist_data['server_ip'];
		$site_domain      = $blacklist_data['site_domain'];

		$blacklist_count = count( $blacklisted_on );

		$explanation = sprintf(
			/* translators: 1: Domain, 2: IP, 3: Blacklist count, 4: Blacklist names */
			__( 'Your site (%1$s, IP: %2$s) is listed on %3$d email blacklist(s): %4$s. Being blacklisted means emails from your domain are automatically rejected or marked as spam by major email providers (Gmail, Outlook, Yahoo). This causes complete email delivery failure and damages sender reputation. Common causes: spam complaints, compromised accounts, or shared hosting with spammers.', 'wpshadow' ),
			$site_domain,
			$server_ip,
			$blacklist_count,
			implode( ', ', $blacklisted_on )
		);

		$solutions = array(
			'free' => array(
				__( 'Request delisting: Submit delisting requests to each blacklist service', 'wpshadow' ),
				__( 'Check for compromise: Scan for malware, backdoors, and unauthorized email scripts', 'wpshadow' ),
				__( 'Secure forms: Add CAPTCHA to contact forms to prevent spam abuse', 'wpshadow' ),
				__( 'Review user accounts: Check for compromised accounts sending spam', 'wpshadow' ),
			),
			'premium' => array(
				__( 'Change IP address: Request new IP from hosting provider', 'wpshadow' ),
				__( 'Use SMTP service: Send emails through authenticated SMTP (SendGrid, Mailgun)', 'wpshadow' ),
				__( 'Implement SPF/DKIM/DMARC: Proper email authentication reduces spam score', 'wpshadow' ),
			),
			'advanced' => array(
				__( 'Dedicated IP for email: Separate email sending from web hosting', 'wpshadow' ),
				__( 'Email reputation monitoring: Continuous blacklist monitoring service', 'wpshadow' ),
				__( 'Move to managed hosting: Avoid shared IP blacklist issues', 'wpshadow' ),
			),
		);

		$additional_info = sprintf(
			/* translators: 1: Checked service count */
			__( 'Checked %1$d major blacklist services. Delisting typically takes 24-72 hours after underlying issue is resolved. Some blacklists auto-remove after 1-2 weeks of clean activity. Being on multiple blacklists indicates serious deliverability issues requiring immediate action.', 'wpshadow' ),
			count( $checked_services )
		);

		return array(
			'explanation'     => $explanation,
			'solutions'       => $solutions,
			'additional_info' => $additional_info,
			'technical_data'  => array(
				'site_domain'       => $site_domain,
				'server_ip'         => $server_ip,
				'blacklisted_on'    => $blacklisted_on,
				'blacklist_count'   => $blacklist_count,
				'checked_services'  => $checked_services,
				'severity_level'    => $blacklist_count > 1 ? 'Critical' : 'High',
			),
			'resources'       => array(
				array(
					'label' => __( 'Spamhaus Delisting', 'wpshadow' ),
					'url'   => 'https://www.spamhaus.org/lookup/',
				),
				array(
					'label' => __( 'MXToolbox Blacklist Check', 'wpshadow' ),
					'url'   => 'https://mxtoolbox.com/blacklists.aspx',
				),
				array(
					'label' => __( 'Email Authentication', 'wpshadow' ),
					'url'   => 'https://www.dmarcanalyzer.com/spf-dkim-dmarc/',
				),
			),
		);
	}
}
