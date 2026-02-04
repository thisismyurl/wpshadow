<?php
/**
 * Phishing URL Detection Diagnostic
 *
 * Checks external links in content against PhishTank database of known
 * phishing URLs to protect visitors from account credential theft.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Security\Security_API_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Phishing_Url_Phishtank Class
 *
 * Detects links in posts and pages that point to known phishing URLs.
 * Phishing sites trick users into entering their credentials, credit card info,
 * or other sensitive data by impersonating legitimate services.
 *
 * Uses the free PhishTank API (https://phishtank.com/api_info.php) which requires
 * registration but has unlimited API calls.
 *
 * PhishTank is maintained by OpenDNS and updated in real-time with user submissions.
 *
 * @since 1.6035.0000
 */
class Diagnostic_Phishing_Url_Phishtank extends Diagnostic_Base {

	/**
	 * The diagnostic slug (unique identifier).
	 *
	 * @var string
	 */
	protected static $slug = 'phishing-url-phishtank';

	/**
	 * The diagnostic title shown to users.
	 *
	 * @var string
	 */
	protected static $title = 'Phishing URL Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for known phishing links in your content';

	/**
	 * The diagnostic family (for grouping related diagnostics).
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Maximum links to check per run.
	 *
	 * @var int
	 */
	const MAX_LINKS_TO_CHECK = 100;

	/**
	 * Cache duration (24 hours).
	 *
	 * @var int
	 */
	const CACHE_TTL = 86400;

	/**
	 * PhishTank API endpoint.
	 *
	 * @var string
	 */
	const API_URL = 'https://checkurl.phishtank.com/checkurl/';

	/**
	 * Run the diagnostic check.
	 *
	 * Extracts external links from posts and checks against PhishTank.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if phishing links found, null otherwise.
	 */
	public static function check() {
		// Check if PhishTank API is enabled.
		if ( ! Security_API_Manager::is_enabled( 'phishtank' ) ) {
			return array(
				'id'            => 'phishtank-api-not-configured',
				'title'         => __( 'Phishing URL Detection Not Set Up Yet', 'wpshadow' ),
				'description'   => sprintf(
					/* translators: %s is the service name */
					__(
						'Get a free %s API key to check links for known phishing scams.',
						'wpshadow'
					),
					'PhishTank'
				),
				'severity'      => 'info',
				'threat_level'  => 0,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/phishtank-api-setup',
				'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'   => __( 'Set Up Free PhishTank API', 'wpshadow' ),
			);
		}

		// Get API key.
		$api_key = Security_API_Manager::get_api_key( 'phishtank' );
		if ( empty( $api_key ) ) {
			return array(
				'id'            => 'phishtank-api-key-missing',
				'title'         => __( 'PhishTank API Key Not Configured', 'wpshadow' ),
				'description'   => __( 'PhishTank is enabled but no API key found.', 'wpshadow' ),
				'severity'      => 'info',
				'threat_level'  => 0,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/phishtank-api-setup',
				'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'   => __( 'Add PhishTank API Key', 'wpshadow' ),
			);
		}

		// Extract external links from posts.
		$external_links = self::extract_external_links();
		if ( empty( $external_links ) ) {
			return null;
		}

		// Check links against PhishTank.
		$phishing_links = array();
		foreach ( $external_links as $link_data ) {
			$phishing_info = self::check_url( $link_data['url'], $api_key );

			if ( is_wp_error( $phishing_info ) ) {
				continue; // API error.
			}

			if ( ! empty( $phishing_info ) ) {
				$phishing_links[] = array_merge( $link_data, $phishing_info );
			}
		}

		// No phishing links found.
		if ( empty( $phishing_links ) ) {
			return null;
		}

		// Calculate severity and threat level.
		$severity     = self::determine_severity( $phishing_links );
		$threat_level = self::calculate_threat_level( $phishing_links );
		$description  = self::build_description( $phishing_links );

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => $description,
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'affected_items'  => $phishing_links,
			'item_count'      => count( $phishing_links ),
			'total_checked'   => count( $external_links ),
			'kb_link'         => 'https://wpshadow.com/kb/phishing-links-fix',
		);
	}

	/**
	 * Extract all external links from post/page content.
	 *
	 * @since  1.6035.0000
	 * @return array Array of link data with post information.
	 */
	private static function extract_external_links() : array {
		$external_links = array();
		$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );

		// Query recent posts and pages.
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_status'    => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			// Extract URLs.
			$pattern = '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/i';
			preg_match_all( $pattern, $post->post_content, $matches );

			if ( ! empty( $matches[2] ) ) {
				foreach ( $matches[2] as $url ) {
					// Skip non-HTTP(S) URLs.
					if ( ! preg_match( '/^https?:\/\//i', $url ) ) {
						continue;
					}

					// Skip internal links.
					$url_domain = wp_parse_url( $url, PHP_URL_HOST );
					if ( $url_domain === $site_domain ) {
						continue;
					}

					// Add to list (avoid duplicates).
					if ( ! isset( $external_links[ $url ] ) ) {
						$external_links[ $url ] = array(
							'url'        => $url,
							'post_id'    => $post->ID,
							'post_title' => $post->post_title,
						);
					}

					// Limit to prevent timeouts.
					if ( count( $external_links ) >= self::MAX_LINKS_TO_CHECK ) {
						break 2;
					}
				}
			}
		}

		return array_values( $external_links );
	}

	/**
	 * Check URL against PhishTank API.
	 *
	 * @since  1.6035.0000
	 * @param  string $url URL to check.
	 * @param  string $api_key PhishTank API key.
	 * @return array|false|WP_Error Phishing info array, false if safe, WP_Error on error.
	 */
	private static function check_url( string $url, string $api_key ) {
		// Check cache first.
		$cache_key = 'wpshadow_phishtank_' . sanitize_key( $url );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached; // Can return false (safe) or array (phishing).
		}

		// Build request body.
		$body = array(
			'url'     => $url,
			'app_key' => $api_key,
			'format'  => 'json',
		);

		// Make API request.
		$response = wp_remote_post(
			self::API_URL,
			array(
				'timeout' => 5,
				'body'    => $body,
			)
		);

		// Handle network errors.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check response code.
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			set_transient( $cache_key, false, self::CACHE_TTL ); // Cache as safe on error.
			return false;
		}

		// Parse response.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// PhishTank response structure: { "results": { "in_database": bool, "verified": bool, ... } }
		if ( ! is_array( $data ) || empty( $data['results'] ) ) {
			set_transient( $cache_key, false, self::CACHE_TTL );
			return false;
		}

		$results = $data['results'];

		// If in_database is true, it's a known phishing URL.
		if ( ! empty( $results['in_database'] ) ) {
			$phishing_info = array(
				'verified'   => $results['verified'] ?? false,
				'phish_type' => $results['phish_detail_type'] ?? 'Unknown',
				'phish_id'   => $results['phish_id'] ?? 0,
			);

			// Cache phishing info.
			set_transient( $cache_key, $phishing_info, self::CACHE_TTL );

			return $phishing_info;
		}

		// Not in database = safe.
		set_transient( $cache_key, false, self::CACHE_TTL );
		return false;
	}

	/**
	 * Determine severity based on phishing links found.
	 *
	 * @since  1.6035.0000
	 * @param  array $phishing_links Array of phishing links.
	 * @return string Severity level.
	 */
	private static function determine_severity( array $phishing_links ) : string {
		$count = count( $phishing_links );
		$verified_count = 0;

		// Count verified phishing attempts.
		foreach ( $phishing_links as $link ) {
			if ( ! empty( $link['verified'] ) ) {
				$verified_count++;
			}
		}

		// Verified phishing = critical.
		if ( $verified_count > 0 ) {
			return 'critical';
		}

		// Multiple unverified = high.
		if ( $count >= 3 ) {
			return 'high';
		}

		// One or two = medium.
		return 'medium';
	}

	/**
	 * Calculate threat level (0-100 scale).
	 *
	 * @since  1.6035.0000
	 * @param  array $phishing_links Array of phishing links.
	 * @return int Threat level.
	 */
	private static function calculate_threat_level( array $phishing_links ) : int {
		$count = count( $phishing_links );
		$verified_count = 0;

		foreach ( $phishing_links as $link ) {
			if ( ! empty( $link['verified'] ) ) {
				$verified_count++;
			}
		}

		// Verified phishing is more serious.
		if ( $verified_count > 0 ) {
			return 90 + min( $verified_count * 2, 10 );
		}

		// Unverified phishing.
		if ( $count >= 5 ) {
			return 80;
		} elseif ( $count >= 3 ) {
			return 60;
		} else {
			return 40;
		}
	}

	/**
	 * Build user-friendly description.
	 *
	 * @since  1.6035.0000
	 * @param  array $phishing_links Array of phishing links.
	 * @return string Description text.
	 */
	private static function build_description( array $phishing_links ) : string {
		$count = count( $phishing_links );
		$verified_count = 0;

		foreach ( $phishing_links as $link ) {
			if ( ! empty( $link['verified'] ) ) {
				$verified_count++;
			}
		}

		// What we found.
		if ( $verified_count > 0 ) {
			$description = sprintf(
				/* translators: %1$d is verified phishing, %2$d is total */
				_n(
					'We found %1$d verified phishing link (%2$d total) in your content.',
					'We found %1$d verified phishing links (%2$d total) in your content.',
					$verified_count,
					'wpshadow'
				),
				$verified_count,
				$count
			);
		} else {
			$description = sprintf(
				/* translators: %d is the number of phishing links */
				_n(
					'We found %d suspected phishing link in your content.',
					'We found %d suspected phishing links in your content.',
					$count,
					'wpshadow'
				),
				$count
			);
		}

		$description .= ' ';

		// Explain phishing.
		$description .= __(
			'Phishing links trick visitors into entering their login credentials, credit card info, or other sensitive data by pretending to be legitimate sites. This is a serious security threat.',
			'wpshadow'
		);

		$description .= "\n\n";

		// List the phishing links.
		$description .= __( 'Phishing links found:', 'wpshadow' ) . "\n";
		foreach ( array_slice( $phishing_links, 0, 10 ) as $link ) {
			$verified_text = ! empty( $link['verified'] ) ? __( '[VERIFIED]', 'wpshadow' ) . ' ' : '';
			$description .= sprintf(
				'• %s%s - %s',
				$verified_text,
				esc_html( $link['url'] ),
				esc_html( $link['post_title'] )
			) . "\n";
		}

		if ( count( $phishing_links ) > 10 ) {
			$description .= sprintf(
				__( '... and %d more', 'wpshadow' ),
				count( $phishing_links ) - 10
			) . "\n";
		}

		$description .= "\n";

		// Action steps.
		$description .= __( 'What to do immediately:', 'wpshadow' ) . "\n";
		$description .= __( '1. Remove or update all the links listed above', 'wpshadow' ) . "\n";
		$description .= __( '2. If verified phishing, report to hosting provider', 'wpshadow' ) . "\n";
		$description .= __( '3. Check if your site was hacked', 'wpshadow' ) . "\n";
		$description .= __( '4. Consider warning your visitors', 'wpshadow' ) . "\n";

		return $description;
	}
}
