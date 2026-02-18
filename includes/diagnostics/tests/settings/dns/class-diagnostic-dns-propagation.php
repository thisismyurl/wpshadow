<?php
/**
 * DNS Propagation Check Diagnostic
 *
 * Verifies DNS changes have propagated globally.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1620
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DNS Propagation Check Diagnostic Class
 *
 * Checks if DNS changes have spread worldwide.
 * Like verifying everyone has your new phone number.
 *
 * @since 1.6035.1620
 */
class Diagnostic_DNS_Propagation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-propagation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Propagation Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies DNS changes have propagated globally';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'dns';

	/**
	 * Run the DNS propagation diagnostic check.
	 *
	 * @since  1.6035.1620
	 * @return array|null Finding array if propagation issues detected, null otherwise.
	 */
	public static function check() {
		// Get domain from site URL.
		$site_url = get_site_url();
		$parsed = wp_parse_url( $site_url );
		$domain = $parsed['host'] ?? '';

		if ( empty( $domain ) ) {
			return null;
		}

		// Check if there was a recent DNS change tracked.
		$last_dns_change = get_option( 'wpshadow_last_dns_change', 0 );
		$hours_since_change = 0;

		if ( $last_dns_change > 0 ) {
			$hours_since_change = ( time() - $last_dns_change ) / 3600;
		}

		// If recent change (< 48 hours), check propagation.
		if ( $hours_since_change > 0 && $hours_since_change < 48 ) {
			return array(
				'id'           => self::$slug . '-in-progress',
				'title'        => __( 'DNS Changes Propagating', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: hours since change */
					__( 'DNS changes were made %d hours ago and are still spreading globally (like waiting for everyone to update their address books with your new address). DNS propagation typically takes 24-48 hours. Some visitors may temporarily see an old version or have connection issues during this time. This is normal and will resolve automatically.', 'wpshadow' ),
					(int) $hours_since_change
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-propagation',
				'context'      => array(
					'hours_since_change' => $hours_since_change,
					'last_change' => $last_dns_change,
				),
			);
		}

		// Check TTL (Time To Live) values.
		if ( function_exists( 'dns_get_record' ) ) {
			$dns_domain = preg_replace( '/^www\./', '', $domain );
			$records = @dns_get_record( $dns_domain, DNS_A );

			if ( ! empty( $records ) ) {
				$ttl = $records[0]['ttl'] ?? 0;

				// Very high TTL can delay DNS propagation.
				if ( $ttl > 86400 ) { // More than 1 day.
					return array(
						'id'           => self::$slug . '-high-ttl',
						'title'        => __( 'Very High DNS TTL Setting', 'wpshadow' ),
						'description'  => sprintf(
							/* translators: %s: TTL duration */
							__( 'Your DNS Time To Live (TTL) is set to %s (like telling people to keep your address for a very long time before checking if it changed). High TTL values make DNS changes take longer to spread. Before making DNS changes (like moving hosts), reduce TTL to 5 minutes a day in advance. After changes propagate, you can increase it again for better performance.', 'wpshadow' ),
							human_time_diff( 0, $ttl )
						),
						'severity'     => 'low',
						'threat_level' => 20,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/dns-ttl',
						'context'      => array(
							'ttl_seconds' => $ttl,
						),
					);
				}

				// Very low TTL increases DNS query load.
				if ( $ttl < 300 ) { // Less than 5 minutes.
					return array(
						'id'           => self::$slug . '-low-ttl',
						'title'        => __( 'Very Low DNS TTL Setting', 'wpshadow' ),
						'description'  => sprintf(
							/* translators: %d: TTL in seconds */
							__( 'Your DNS TTL is only %d seconds (like asking people to constantly check for your new address). This causes more DNS queries and slightly slower site access. Unless you\'re planning DNS changes soon, increase TTL to 1-24 hours (3600-86400 seconds) for better performance.', 'wpshadow' ),
							$ttl
						),
						'severity'     => 'low',
						'threat_level' => 20,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/dns-ttl',
						'context'      => array(
							'ttl_seconds' => $ttl,
						),
					);
				}
			}
		}

		return null; // DNS propagation is fine.
	}
}
