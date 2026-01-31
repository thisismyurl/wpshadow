<?php
/**
 * Diagnostic: Domain Expiry Warning
 *
 * Detects domain registration expiry and warns administrator in advance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Domain_Expiry_Warning
 *
 * Monitors domain registration expiration and provides advance warning
 * to prevent domain loss and site downtime.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Domain_Expiry_Warning extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'domain-expiry-warning';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Domain Expiry Warning';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect domain registration expiry and warn administrator in advance';

	/**
	 * Run the diagnostic check.
	 *
	 * Note: This requires external WHOIS lookup which may not always be available.
	 * This implementation provides a placeholder that can be enhanced with
	 * actual WHOIS API integration.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if expiring soon, null otherwise.
	 */
	public static function check() {
		// Get domain from site URL
		$site_url = get_site_url();
		$parsed_url = wp_parse_url( $site_url );
		$domain = $parsed_url['host'] ?? '';

		if ( empty( $domain ) ) {
			return null;
		}

		// Check if domain expiry data is cached
		$cache_key = 'wpshadow_domain_expiry_' . md5( $domain );
		$domain_data = get_transient( $cache_key );

		if ( false === $domain_data ) {
			// No cached data - return info message
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: domain name */
					__( 'Domain expiry monitoring for %s is not yet configured. Domain expiry monitoring requires WHOIS API integration. An expired domain causes complete site downtime and can be difficult to recover. Consider enabling domain monitoring through your registrar or a monitoring service.', 'wpshadow' ),
					esc_html( $domain )
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/compliance-domain-expiry-warning',
				'meta'        => array(
					'domain' => $domain,
					'monitoring_enabled' => false,
				),
			);
		}

		// Domain expiry data is available
		$expiry_timestamp = absint( $domain_data['expiry_timestamp'] ?? 0 );
		
		if ( empty( $expiry_timestamp ) ) {
			return null;
		}

		$current_timestamp = time();
		$days_until_expiry = floor( ( $expiry_timestamp - $current_timestamp ) / DAY_IN_SECONDS );

		// Determine severity based on days remaining
		if ( $days_until_expiry > 90 ) {
			// Domain valid for >90 days - all good
			return null;
		}

		if ( $days_until_expiry < 0 ) {
			// Domain already expired
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: domain name */
					__( 'Domain %s has EXPIRED! Your site may go offline at any time. Contact your domain registrar immediately to renew the domain before it enters redemption period.', 'wpshadow' ),
					esc_html( $domain )
				),
				'severity'    => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/compliance-domain-expiry-warning',
				'meta'        => array(
					'domain' => $domain,
					'days_until_expiry' => $days_until_expiry,
					'expiry_date' => gmdate( 'Y-m-d', $expiry_timestamp ),
					'expired' => true,
				),
			);
		}

		if ( $days_until_expiry <= 30 ) {
			// Less than 30 days - critical
			$severity = 'high';
			$threat_level = 60;
		} else {
			// 31-90 days - warning
			$severity = 'medium';
			$threat_level = 40;
		}

		$description = sprintf(
			/* translators: 1: domain name, 2: number of days until expiry */
			_n(
				'Domain %1$s expires in %2$d day. Renew your domain registration immediately to prevent site downtime and potential domain loss.',
				'Domain %1$s expires in %2$d days. Renew your domain registration soon to prevent site downtime and potential domain loss.',
				$days_until_expiry,
				'wpshadow'
			),
			esc_html( $domain ),
			$days_until_expiry
		) . ' ' . sprintf(
			/* translators: %s: expiry date */
			__( 'Expiry date: %s', 'wpshadow' ),
			gmdate( 'F j, Y', $expiry_timestamp )
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/compliance-domain-expiry-warning',
			'meta'        => array(
				'domain' => $domain,
				'days_until_expiry' => $days_until_expiry,
				'expiry_date' => gmdate( 'Y-m-d', $expiry_timestamp ),
				'expired' => false,
			),
		);
	}
}
