<?php
/**
 * CNAME and CDN Configuration Diagnostic
 *
 * Checks CDN and CNAME record configuration.
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
 * CNAME and CDN Configuration Diagnostic Class
 *
 * Verifies CDN routing and CNAME configuration.
 * Like checking mail forwarding rules are set up correctly.
 *
 * @since 0.6093.1200
 */
class Diagnostic_DNS_CDN_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-cdn-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CNAME and CDN Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks CDN and CNAME record configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'dns';

	/**
	 * Run the CNAME/CDN configuration diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if configuration issues detected, null otherwise.
	 */
	public static function check() {
		// Detect CDN plugins/services.
		$cdn_services = array(
			'Cloudflare'   => class_exists( 'CF\WordPress\Hooks' ) || defined( 'CLOUDFLARE_PLUGIN_DIR' ),
			'Cloudflare (Server)' => isset( $_SERVER['HTTP_CF_RAY'] ),
			'Jetpack CDN'  => class_exists( 'Jetpack' ) && \Jetpack::is_module_active( 'photon' ),
			'W3 Total Cache CDN' => class_exists( 'W3TC\CdnEngine' ) || ( function_exists( 'w3tc_get_string' ) && w3_get_option( 'cdn.enabled' ) ),
			'WP Rocket CDN' => defined( 'WP_ROCKET_VERSION' ) && function_exists( 'get_rocket_option' ) && get_rocket_option( 'cdn' ),
		);

		$active_cdn = array();
		foreach ( $cdn_services as $name => $detected ) {
			if ( $detected ) {
				$active_cdn[] = $name;
			}
		}

		// If CDN detected, check CNAME configuration.
		if ( ! empty( $active_cdn ) ) {
			// Check if using www subdomain (common CDN setup).
			$site_url = get_site_url();
			$parsed = wp_parse_url( $site_url );
			$domain = $parsed['host'] ?? '';

			if ( 0 === strpos( $domain, 'www.' ) && function_exists( 'dns_get_record' ) ) {
				// Check if www is a CNAME.
				$records = @dns_get_record( $domain, DNS_CNAME );

				if ( false === $records || empty( $records ) ) {
					// www is not a CNAME, but an A record (less optimal for CDN).
					return array(
						'id'           => self::$slug . '-www-not-cname',
						'title'        => __( 'WWW Subdomain Not Using CNAME', 'wpshadow' ),
						'description'  => sprintf(
							/* translators: %s: CDN name */
							__( 'You\'re using %s (a CDN), but your www subdomain uses an A record instead of a CNAME (like using a direct address instead of a forwarding rule). For better CDN performance and easier management, use a CNAME record for www that points to your CDN provider. Check your DNS provider settings and CDN documentation.', 'wpshadow' ),
							implode( ', ', $active_cdn )
						),
						'severity'     => 'low',
						'threat_level' => 25,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/cdn-cname-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
						'context'      => array(
							'cdn_services' => $active_cdn,
							'domain'       => $domain,
						),
					);
				} else {
					// CNAME found - check if it points to CDN.
					$cname_target = $records[0]['target'] ?? '';
					$cname_target_lower = strtolower( $cname_target );

					$cdn_domains = array(
						'cloudflare' => array( 'cloudflare.com', 'cloudflare.net' ),
						'cloudfront' => array( 'cloudfront.net' ),
						'fastly'     => array( 'fastly.net', 'fastlylb.net' ),
						'akamai'     => array( 'akamai.net', 'akamaicdn.net' ),
						'maxcdn'     => array( 'maxcdn.com', 'netdna-cdn.com' ),
					);

					$points_to_cdn = false;
					foreach ( $cdn_domains as $cdn_name => $domains ) {
						foreach ( $domains as $cdn_domain ) {
							if ( false !== strpos( $cname_target_lower, $cdn_domain ) ) {
								$points_to_cdn = true;
								break 2;
							}
						}
					}

					if ( ! $points_to_cdn ) {
						return array(
							'id'           => self::$slug . '-cname-mismatch',
							'title'        => __( 'CNAME May Not Point to CDN', 'wpshadow' ),
							'description'  => sprintf(
								/* translators: 1: CNAME target, 2: CDN name */
								__( 'Your www subdomain has a CNAME pointing to %1$s, but you\'re using %2$s. The CNAME should typically point to your CDN provider (like forwarding to the correct shipping center). Verify your DNS configuration matches your CDN provider\'s setup instructions. Incorrect CNAME routing can bypass CDN benefits.', 'wpshadow' ),
								esc_html( $cname_target ),
								implode( ', ', $active_cdn )
							),
							'severity'     => 'medium',
							'threat_level' => 45,
							'auto_fixable' => false,
							'kb_link'      => 'https://wpshadow.com/kb/cdn-cname-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
							'context'      => array(
								'cname_target' => $cname_target,
								'cdn_services' => $active_cdn,
							),
						);
					}
				}
			}
		}

		// Check for apex domain CNAME (not allowed, causes issues).
		$site_url = get_site_url();
		$parsed = wp_parse_url( $site_url );
		$domain = $parsed['host'] ?? '';
		$dns_domain = preg_replace( '/^www\./', '', $domain );

		// Only check if this is the apex domain (no subdomains).
		if ( $domain === $dns_domain && function_exists( 'dns_get_record' ) ) {
			$cname_records = @dns_get_record( $domain, DNS_CNAME );

			if ( ! empty( $cname_records ) ) {
				return array(
					'id'           => self::$slug . '-apex-cname',
					'title'        => __( 'CNAME on Apex Domain (Not Allowed)', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %s: domain name */
						__( 'Your main domain (%s) is using a CNAME record (like trying to forward your main address to another address). DNS rules don\'t allow CNAMEs on apex domains because it conflicts with other records like MX (email). This will cause email and other issues. Use an A record for your apex domain, or use a subdomain like www with a CNAME instead.', 'wpshadow' ),
						esc_html( $domain )
					),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/apex-cname-issue?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'domain' => $domain,
					),
				);
			}
		}

		return null; // CDN/CNAME configuration is fine.
	}
}
