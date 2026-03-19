<?php
/**
 * Third-Party Script Audit Diagnostic
 *
 * Tests if third-party scripts are regularly reviewed and audited
 * for performance impact, security, and necessity.
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
 * Third-Party Script Audit Diagnostic Class
 *
 * Evaluates third-party script usage and provides recommendations
 * for optimization and security.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Third_Party_Script_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'audits-third-party-scripts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Script Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if third-party scripts are regularly reviewed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the third-party script audit diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if third-party script issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Get homepage content to analyze scripts.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		if ( is_wp_error( $response ) ) {
			$warnings[] = __( 'Could not fetch homepage for script analysis', 'wpshadow' );
			return null;
		}

		$html = wp_remote_retrieve_body( $response );

		// Detect third-party script domains.
		preg_match_all( '/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $script_matches );

		$third_party_domains = array();
		$site_domain = wp_parse_url( $homepage_url, PHP_URL_HOST );

		if ( ! empty( $script_matches[1] ) ) {
			foreach ( $script_matches[1] as $script_url ) {
				$script_domain = wp_parse_url( $script_url, PHP_URL_HOST );
				
				// Check if external domain.
				if ( $script_domain && $script_domain !== $site_domain ) {
					if ( ! isset( $third_party_domains[ $script_domain ] ) ) {
						$third_party_domains[ $script_domain ] = 0;
					}
					$third_party_domains[ $script_domain ]++;
				}
			}
		}

		$stats['total_external_scripts'] = count( $script_matches[1] );
		$stats['third_party_domains'] = array_keys( $third_party_domains );
		$stats['third_party_domain_count'] = count( $third_party_domains );

		// Categorize common third-party services.
		$service_categories = array(
			'analytics' => array(
				'google-analytics.com',
				'googletagmanager.com',
				'matomo.org',
				'hotjar.com',
				'segment.com',
			),
			'advertising' => array(
				'doubleclick.net',
				'googleadservices.com',
				'facebook.net',
				'ads-twitter.com',
				'bing.com',
			),
			'social_media' => array(
				'facebook.com',
				'twitter.com',
				'linkedin.com',
				'instagram.com',
				'pinterest.com',
			),
			'cdn' => array(
				'cloudflare.com',
				'jsdelivr.net',
				'cdnjs.cloudflare.com',
				'unpkg.com',
				'googleapis.com',
			),
			'chat' => array(
				'intercom.io',
				'drift.com',
				'tawk.to',
				'crisp.chat',
				'zendesk.com',
			),
			'payment' => array(
				'stripe.com',
				'paypal.com',
				'square.com',
				'braintree.com',
			),
		);

		$detected_services = array();
		foreach ( $third_party_domains as $domain => $count ) {
			foreach ( $service_categories as $category => $patterns ) {
				foreach ( $patterns as $pattern ) {
					if ( strpos( $domain, $pattern ) !== false ) {
						if ( ! isset( $detected_services[ $category ] ) ) {
							$detected_services[ $category ] = array();
						}
						$detected_services[ $category ][] = $domain;
						break 2;
					}
				}
			}
		}

		$stats['detected_services'] = $detected_services;

		// Check for async/defer attributes.
		$scripts_with_async = 0;
		$scripts_with_defer = 0;
		$blocking_scripts = 0;

		preg_match_all( '/<script[^>]*>/i', $html, $all_script_tags );
		
		if ( ! empty( $all_script_tags[0] ) ) {
			foreach ( $all_script_tags[0] as $script_tag ) {
				if ( strpos( $script_tag, 'async' ) !== false ) {
					$scripts_with_async++;
				} elseif ( strpos( $script_tag, 'defer' ) !== false ) {
					$scripts_with_defer++;
				} elseif ( strpos( $script_tag, 'src=' ) !== false ) {
					// External script without async/defer is render-blocking.
					$blocking_scripts++;
				}
			}
		}

		$stats['scripts_with_async'] = $scripts_with_async;
		$stats['scripts_with_defer'] = $scripts_with_defer;
		$stats['blocking_scripts'] = $blocking_scripts;

		// Check for script management plugins.
		$script_management_plugins = array(
			'autoptimize/autoptimize.php'              => 'Autoptimize',
			'async-javascript/async-javascript.php'    => 'Async JavaScript',
			'wp-rocket/wp-rocket.php'                  => 'WP Rocket',
			'flying-scripts/flying-scripts.php'        => 'Flying Scripts',
		);

		$has_script_management = false;
		foreach ( $script_management_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_script_management = true;
				break;
			}
		}

		$stats['has_script_management'] = $has_script_management;

		// Check for Google Tag Manager (helps manage scripts).
		$has_gtm = false;
		if ( preg_match( '/googletagmanager\.com\/gtm\.js|GTM-[A-Z0-9]+/i', $html ) ) {
			$has_gtm = true;
		}

		$stats['has_google_tag_manager'] = $has_gtm;

		// Check for Content Security Policy.
		$headers = wp_remote_retrieve_headers( $response );
		$has_csp = isset( $headers['content-security-policy'] ) || isset( $headers['Content-Security-Policy'] );
		$stats['has_csp'] = $has_csp;

		// Check for Subresource Integrity (SRI).
		$scripts_with_sri = 0;
		preg_match_all( '/<script[^>]*integrity=["\'][^"\']+["\'][^>]*>/i', $html, $sri_matches );
		$scripts_with_sri = count( $sri_matches[0] );

		$stats['scripts_with_sri'] = $scripts_with_sri;

		// Calculate script optimization score.
		$optimization_features = 0;
		$total_features = 5;

		if ( $has_script_management ) { $optimization_features++; }
		if ( $has_gtm ) { $optimization_features++; }
		if ( $has_csp ) { $optimization_features++; }
		if ( $blocking_scripts < $stats['total_external_scripts'] * 0.5 ) { $optimization_features++; }
		if ( $stats['third_party_domain_count'] < 10 ) { $optimization_features++; }

		$stats['script_optimization_score'] = round( ( $optimization_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( $stats['third_party_domain_count'] > 15 ) {
			$issues[] = sprintf(
				/* translators: %d: number of domains */
				__( 'Too many third-party domains (%d) - each domain adds DNS lookup overhead', 'wpshadow' ),
				$stats['third_party_domain_count']
			);
		}

		if ( $blocking_scripts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of blocking scripts */
				__( '%d render-blocking scripts detected - use async/defer attributes', 'wpshadow' ),
				$blocking_scripts
			);
		}

		if ( ! $has_script_management ) {
			$warnings[] = __( 'No script management plugin active - consider Autoptimize or WP Rocket', 'wpshadow' );
		}

		if ( ! $has_gtm && count( $detected_services ) > 3 ) {
			$warnings[] = __( 'Multiple third-party services without Google Tag Manager - GTM simplifies management', 'wpshadow' );
		}

		if ( ! $has_csp ) {
			$warnings[] = __( 'No Content Security Policy detected - add for better script security', 'wpshadow' );
		}

		if ( $scripts_with_sri === 0 && $stats['total_external_scripts'] > 0 ) {
			$warnings[] = __( 'No Subresource Integrity (SRI) detected - add for external script security', 'wpshadow' );
		}

		if ( isset( $detected_services['advertising'] ) && count( $detected_services['advertising'] ) > 2 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of ad services */
				__( 'Multiple advertising services (%d) detected - consolidate to reduce performance impact', 'wpshadow' ),
				count( $detected_services['advertising'] )
			);
		}

		if ( $stats['third_party_domain_count'] > 10 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of domains */
				__( '%d third-party domains detected - review and remove unnecessary scripts', 'wpshadow' ),
				$stats['third_party_domain_count']
			);
		}

		if ( $stats['script_optimization_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'Script optimization score is low (%s%%) - audit and optimize third-party scripts', 'wpshadow' ),
				$stats['script_optimization_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Third-party script audit has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/third-party-scripts',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Third-party script audit has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/third-party-scripts',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Third-party scripts are well managed.
	}
}
