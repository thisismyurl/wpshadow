<?php
/**
 * DNS Prefetch Configuration Diagnostic
 *
 * Issue #4936: No DNS Prefetch for External Resources
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if DNS prefetch hints are configured.
 * DNS lookups add 20-120ms latency for external resources.
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
 * Diagnostic_DNS_Prefetch_Configuration Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_DNS_Prefetch_Configuration extends Diagnostic_Base {

	protected static $slug = 'dns-prefetch-configuration';
	protected static $title = 'No DNS Prefetch for External Resources';
	protected static $description = 'Checks if DNS prefetch hints are configured for external domains';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add dns-prefetch for CDNs: fonts.googleapis.com', 'wpshadow' );
		$issues[] = __( 'Add dns-prefetch for analytics: google-analytics.com', 'wpshadow' );
		$issues[] = __( 'Add dns-prefetch for social: facebook.net, twitter.com', 'wpshadow' );
		$issues[] = __( 'Add preconnect for critical resources (includes TLS)', 'wpshadow' );
		$issues[] = __( 'Limit to 4-6 domains (diminishing returns)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'DNS prefetch tells browsers to resolve domain names early, saving 20-120ms when resources are needed.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/dns-prefetch?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'html_example'            => '<link rel="dns-prefetch" href="//fonts.googleapis.com">',
					'preconnect_example'      => '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
					'latency_savings'         => '20-120ms per external domain',
				),
			);
		}

		return null;
	}
}
