<?php
/**
 * No DNS Prefetch Configuration Diagnostic
 *
 * Detects when DNS prefetching is not configured,
 * wasting time on DNS lookups for external resources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No DNS Prefetch Configuration
 *
 * Checks whether DNS prefetching is configured
 * for external resource optimization.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_DNS_Prefetch_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-dns-prefetch-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Prefetch Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether DNS prefetch is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check homepage for DNS prefetch hints
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		
		// Check for dns-prefetch links
		$has_dns_prefetch = preg_match( '/<link[^>]*rel=["\']dns-prefetch["\']/i', $body );

		// Check if external resources exist
		$has_external = preg_match( '/fonts\.googleapis\.com|cdnjs\.cloudflare\.com|ajax\.googleapis\.com/i', $body );

		if ( $has_external && ! $has_dns_prefetch ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'DNS prefetching isn\'t configured, which wastes time on DNS lookups. When loading external resources (Google Fonts, CDNs, analytics), browser must: resolve domain to IP (DNS lookup ~20-120ms). DNS prefetch does this early, in parallel. Add to <head>: <link rel="dns-prefetch" href="//fonts.googleapis.com">. This saves 20-120ms per external domain, most helpful for: fonts, CDNs, analytics, social widgets. Free performance win.',
					'wpshadow'
				),
				'severity'      => 'low',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'External Resource Load Time',
					'potential_gain' => 'Save 20-120ms per external domain',
					'roi_explanation' => 'DNS prefetch eliminates DNS lookup time for external resources, saving 20-120ms per domain.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/dns-prefetch-configuration',
			);
		}

		return null;
	}
}
