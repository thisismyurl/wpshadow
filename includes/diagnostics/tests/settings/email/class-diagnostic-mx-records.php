<?php
/**
 * MX Records Diagnostic
 *
 * Checks if MX (Mail Exchange) records are properly configured.
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
 * MX Records Diagnostic Class
 *
 * Verifies that MX records are configured properly. MX records tell other
 * email servers where to deliver mail for your domain (like a forwarding
 * address on your mailbox).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mx_Records extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mx-records';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'MX Records Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if MX records are properly configured for email delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the MX records diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if MX issues detected, null otherwise.
	 */
	public static function check() {
		// Get the site domain.
		$site_url = get_site_url();
		$domain   = wp_parse_url( $site_url, PHP_URL_HOST );

		if ( empty( $domain ) ) {
			return null;
		}

		// Remove www. prefix if present.
		$domain = preg_replace( '/^www\./i', '', $domain );

		// Check for MX records via DNS lookup.
		if ( ! function_exists( 'dns_get_record' ) ) {
			return array(
				'id'           => self::$slug . '-unavailable',
				'title'        => __( 'MX Records Check Unavailable', 'wpshadow' ),
				'description'  => __( 'Your server configuration prevents DNS lookups. To check MX records manually, use online tools like mxtoolbox.com or ask your hosting provider.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mx-records',
			);
		}

		// Get MX records for the domain.
		$mx_records = @dns_get_record( $domain, DNS_MX ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( false === $mx_records || empty( $mx_records ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: domain name */
					__( 'Your domain (%s) doesn\'t have MX records configured (these tell other email servers where to deliver mail to you—like a forwarding address on a mailbox). While this doesn\'t prevent you from sending emails, it may affect replies and could indicate DNS configuration issues.', 'wpshadow' ),
					$domain
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mx-records',
				'context'      => array(
					'domain'     => $domain,
					'mx_found'   => false,
				),
			);
		}

		// MX records found - check configuration quality.
		$warnings = array();

		// Check for single MX record (no redundancy).
		if ( count( $mx_records ) === 1 ) {
			$warnings[] = __( 'Only one MX record found - consider adding backup mail servers for redundancy', 'wpshadow' );
		}

		// Check for very high priority numbers (lower is better).
		$has_low_priority = false;
		foreach ( $mx_records as $mx ) {
			if ( isset( $mx['pri'] ) && $mx['pri'] <= 10 ) {
				$has_low_priority = true;
				break;
			}
		}

		if ( ! $has_low_priority ) {
			$warnings[] = __( 'MX records have high priority numbers - lower numbers indicate higher priority', 'wpshadow' );
		}

		// Sort MX records by priority for display.
		usort( $mx_records, function( $a, $b ) {
			return ( $a['pri'] ?? 999 ) - ( $b['pri'] ?? 999 );
		});

		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug . '-warnings',
				'title'        => __( 'MX Records Warnings', 'wpshadow' ),
				'description'  => __( 'MX records exist but could be improved for better email reliability.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mx-records',
				'context'      => array(
					'domain'     => $domain,
					'mx_records' => $mx_records,
					'warnings'   => $warnings,
				),
			);
		}

		return null; // MX records configured properly.
	}
}
