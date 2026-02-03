<?php
/**
 * DNS Prefetch/Preconnect Headers Diagnostic
 *
 * Checks if DNS prefetch and preconnect headers are configured to optimize
 * connection establishment with third-party domains.
 *
 * @since   1.26033.2078
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DNS Prefetch/Preconnect Headers Diagnostic Class
 *
 * Verifies resource hints configuration:
 * - dns-prefetch for external domains
 * - preconnect for critical domains
 * - Implementation via wp_resource_hints filter
 *
 * @since 1.26033.2078
 */
class Diagnostic_Dns_Prefetch_Preconnect extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-prefetch-preconnect';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Prefetch/Preconnect Headers';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for DNS prefetch and preconnect optimization headers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2078
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$prefetch_configured = false;
		$preconnect_hints     = array();
		$external_domains    = array();

		// Check for wp_resource_hints filter
		if ( has_filter( 'wp_resource_hints' ) ) {
			$prefetch_configured = true;
		}

		// Detect external domains from enqueued scripts
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && ! empty( $script->src ) ) {
					$domain = parse_url( $script->src, PHP_URL_HOST );
					if ( $domain && ! in_array( $domain, array( $_SERVER['HTTP_HOST'] ?? '', parse_url( home_url(), PHP_URL_HOST ) ), true ) ) {
						$external_domains[] = $domain;
					}
				}
			}
		}

		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				$style = $wp_styles->registered[ $handle ] ?? null;
				if ( $style && ! empty( $style->src ) ) {
					$domain = parse_url( $style->src, PHP_URL_HOST );
					if ( $domain && ! in_array( $domain, array( $_SERVER['HTTP_HOST'] ?? '', parse_url( home_url(), PHP_URL_HOST ) ), true ) ) {
						$external_domains[] = $domain;
					}
				}
			}
		}

		$unique_domains = array_unique( $external_domains );

		if ( ! $prefetch_configured && count( $unique_domains ) >= 2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of external domains detected */
					__( 'DNS prefetch/preconnect headers are not configured. Detected %d external domains that could benefit from prefetch/preconnect hints.', 'wpshadow' ),
					count( $unique_domains )
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/dns-prefetch-preconnect',
				'meta'          => array(
					'external_domains'     => array_slice( $unique_domains, 0, 5 ),
					'domain_count'         => count( $unique_domains ),
					'recommendation'       => 'Add to functions.php: add_filter( "wp_resource_hints", function( $hints ) { $hints[] = array( "href" => "https://example.com", "rel" => "preconnect" ); return $hints; } );',
					'impact'               => 'Reduces connection time by 50-100ms per domain',
				),
			);
		}

		return null;
	}
}
