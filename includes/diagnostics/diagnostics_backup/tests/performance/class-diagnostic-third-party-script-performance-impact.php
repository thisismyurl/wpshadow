<?php
/**
 * Third-Party Script Performance Impact Diagnostic
 *
 * Measures performance cost of external scripts (ads, analytics, tracking).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Third-Party Script Performance Impact Class
 *
 * Tests third-party scripts.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Third_Party_Script_Performance_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-script-performance-impact';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Script Performance Impact';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures performance cost of external scripts (ads, analytics, tracking)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$third_party_check = self::check_third_party_scripts();
		
		if ( $third_party_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $third_party_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/third-party-script-performance-impact',
				'meta'         => array(
					'third_party_count'    => $third_party_check['third_party_count'],
					'third_party_domains'  => $third_party_check['third_party_domains'],
					'script_types'         => $third_party_check['script_types'],
					'recommendations'      => $third_party_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check third-party scripts.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_third_party_scripts() {
		global $wp_scripts;

		$check = array(
			'has_issues'          => false,
			'issues'              => array(),
			'third_party_count'   => 0,
			'third_party_domains' => array(),
			'script_types'        => array(),
			'recommendations'     => array(),
		);

		if ( empty( $wp_scripts->queue ) ) {
			return $check;
		}

		$home_url = home_url();

		// Identify third-party scripts.
		foreach ( $wp_scripts->queue as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
				continue;
			}

			$script = $wp_scripts->registered[ $handle ];

			if ( empty( $script->src ) ) {
				continue;
			}

			// Check if external domain.
			if ( 0 !== strpos( $script->src, $home_url ) && 
			     0 !== strpos( $script->src, '/' ) && 
			     0 !== strpos( $script->src, 'wp-includes' ) ) {

				$check['third_party_count']++;

				// Extract domain.
				if ( preg_match( '/https?:\/\/([^\/]+)/', $script->src, $matches ) ) {
					$domain = $matches[1];
					$check['third_party_domains'][] = $domain;

					// Categorize script type.
					if ( false !== strpos( $domain, 'google-analytics' ) || 
					     false !== strpos( $domain, 'googletagmanager' ) ) {
						$check['script_types']['analytics'] = ( $check['script_types']['analytics'] ?? 0 ) + 1;
					} elseif ( false !== strpos( $domain, 'doubleclick' ) || 
					           false !== strpos( $domain, 'googlesyndication' ) ||
					           false !== strpos( $domain, 'adsbygoogle' ) ) {
						$check['script_types']['ads'] = ( $check['script_types']['ads'] ?? 0 ) + 1;
					} elseif ( false !== strpos( $domain, 'facebook' ) || 
					           false !== strpos( $domain, 'twitter' ) ||
					           false !== strpos( $domain, 'linkedin' ) ) {
						$check['script_types']['social'] = ( $check['script_types']['social'] ?? 0 ) + 1;
					} else {
						$check['script_types']['other'] = ( $check['script_types']['other'] ?? 0 ) + 1;
					}
				}
			}
		}

		// Deduplicate domains.
		$check['third_party_domains'] = array_unique( $check['third_party_domains'] );

		// Detect issues.
		if ( $check['third_party_count'] > 5 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of third-party scripts */
				__( '%d third-party scripts detected (can add 1-3 seconds to page load)', 'wpshadow' ),
				$check['third_party_count']
			);
			$check['recommendations'][] = __( 'Load third-party scripts asynchronously or with delay', 'wpshadow' );
		}

		if ( isset( $check['script_types']['ads'] ) && $check['script_types']['ads'] > 2 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of ad scripts */
				__( '%d advertising scripts detected (major performance impact)', 'wpshadow' ),
				$check['script_types']['ads']
			);
			$check['recommendations'][] = __( 'Consider consolidating ad providers or lazy loading ads', 'wpshadow' );
		}

		if ( isset( $check['script_types']['analytics'] ) && $check['script_types']['analytics'] > 1 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of analytics scripts */
				__( '%d analytics scripts detected (redundant tracking)', 'wpshadow' ),
				$check['script_types']['analytics']
			);
			$check['recommendations'][] = __( 'Consolidate analytics to single provider (Google Analytics 4)', 'wpshadow' );
		}

		if ( count( $check['third_party_domains'] ) > 10 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of domains */
				__( '%d external domains contacted (DNS lookups add latency)', 'wpshadow' ),
				count( $check['third_party_domains'] )
			);
			$check['recommendations'][] = __( 'Use dns-prefetch or preconnect for critical third-party domains', 'wpshadow' );
		}

		return $check;
	}
}
