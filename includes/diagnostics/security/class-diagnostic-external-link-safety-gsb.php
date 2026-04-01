<?php
/**
 * External Link Safety Check Diagnostic
 *
 * Scans all external links in posts and pages to detect if they point to
 * malicious sites using Google Safe Browsing API. Helps prevent users from
 * being exposed to malware, phishing, or unwanted software.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Security\Security_API_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_External_Link_Safety_Gsb Class
 *
 * Analyzes URLs in posts and pages to detect if they point to:
 * - Malware distribution sites
 * - Phishing pages
 * - Sites hosting unwanted software
 * - Sites with deceptive content
 *
 * Uses Google Safe Browsing API (v4) which requires authentication.
 * Free tier: 10,000 lookups per day
 *
 * Note: This checks a sample of links due to API rate limits. To check all links,
 * enable scheduled daily scans.
 *
 * @since 0.6093.1200
 */
class Diagnostic_External_Link_Safety_Gsb extends Diagnostic_Base {

	/**
	 * The diagnostic slug (unique identifier).
	 *
	 * @var string
	 */
	protected static $slug = 'external-link-safety-gsb';

	/**
	 * The diagnostic title shown to users.
	 *
	 * @var string
	 */
	protected static $title = 'External Link Safety Check';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks external links for malware, phishing, and unsafe content';

	/**
	 * The diagnostic family (for grouping related diagnostics).
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Maximum links to check per run (API rate limit consideration).
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
	 * Google Safe Browsing API v4 endpoint.
	 *
	 * @var string
	 */
	const API_URL = 'https://safebrowsing.googleapis.com/v4/threatMatches:find';

	/**
	 * Run the diagnostic check.
	 *
	 * Finds external links in posts/pages and checks them against Google
	 * Safe Browsing for malware, phishing, and unwanted software.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if unsafe links found, null otherwise.
	 */
	public static function check() {
		// Check if Google Safe Browsing API is enabled.
		if ( ! Security_API_Manager::is_enabled( 'google_safe_browsing' ) ) {
			return array(
				'id'            => 'gsb-api-not-configured',
				'title'         => __( 'External Link Safety Check Not Set Up Yet', 'wpshadow' ),
				'description'   => sprintf(
					/* translators: %s is the service name */
					__(
						'Get a free %s API key to scan external links for malware and phishing.',
						'wpshadow'
					),
					'Google Safe Browsing'
				),
				'severity'      => 'info',
				'threat_level'  => 0,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gsb-api-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'   => __( 'Set Up Free Google Safe Browsing API', 'wpshadow' ),
			);
		}

		// Get API key.
		$api_key = Security_API_Manager::get_api_key( 'google_safe_browsing' );
		if ( empty( $api_key ) ) {
			return array(
				'id'            => 'gsb-api-key-missing',
				'title'         => __( 'Google Safe Browsing API Key Not Configured', 'wpshadow' ),
				'description'   => __( 'Google Safe Browsing is enabled but no API key found.', 'wpshadow' ),
				'severity'      => 'info',
				'threat_level'  => 0,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gsb-api-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'action_url'    => admin_url( 'admin.php?page=wpshadow-security-api' ),
				'action_text'   => __( 'Add Google Safe Browsing API Key', 'wpshadow' ),
			);
		}

		// Extract external links from posts.
		$external_links = self::extract_external_links();
		if ( empty( $external_links ) ) {
			return null; // No external links found.
		}

		// Check links against Google Safe Browsing.
		$unsafe_links = array();
		foreach ( $external_links as $link_data ) {
			$threat_info = self::check_url( $link_data['url'], $api_key );

			if ( is_wp_error( $threat_info ) ) {
				// API error.
				continue;
			}

			if ( ! empty( $threat_info ) ) {
				// Link is unsafe - add to results.
				$unsafe_links[] = array_merge( $link_data, $threat_info );
			}
		}

		// No unsafe links found.
		if ( empty( $unsafe_links ) ) {
			return null;
		}

		// Calculate severity and threat level.
		$severity     = self::determine_severity( $unsafe_links );
		$threat_level = self::calculate_threat_level( $unsafe_links );
		$description  = self::build_description( $unsafe_links );

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => $description,
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'affected_items'  => $unsafe_links,
			'item_count'      => count( $unsafe_links ),
			'total_checked'   => count( $external_links ),
			'kb_link'         => 'https://wpshadow.com/kb/unsafe-links-fix?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Extract all external links from post/page content.
	 *
	 * Queries posts and pages, extracts URLs, filters to external only.
	 *
	 * @since 0.6093.1200
	 * @return array Array of link data with post ID and title.
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
			// Extract URLs using regex.
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
							'url'      => $url,
							'post_id'  => $post->ID,
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
	 * Check URL against Google Safe Browsing API.
	 *
	 * @since 0.6093.1200
	 * @param  string $url URL to check.
	 * @param  string $api_key Google Safe Browsing API key.
	 * @return array|false|WP_Error Threat info array, false if safe, WP_Error on API error.
	 */
	private static function check_url( string $url, string $api_key ) {
		// Check cache first.
		$cache_key = 'wpshadow_gsb_' . sanitize_key( $url );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached; // Can return false (safe) or array (threat).
		}

		// Build API request.
		$request_body = array(
			'client' => array(
				'clientId'      => 'wpshadow',
				'clientVersion' => '1.0',
			),
			'threatInfo' => array(
				'threatTypes'       => array(
					'MALWARE',
					'SOCIAL_ENGINEERING',
					'UNWANTED_SOFTWARE',
					'POTENTIALLY_HARMFUL_APPLICATION',
				),
				'platformTypes'     => array( 'ALL_PLATFORMS' ),
				'threatEntryTypes'  => array( 'URL' ),
				'threatEntries'     => array(
					array( 'url' => $url ),
				),
			),
		);

		// Make API request.
		$response = wp_remote_post(
			self::API_URL . '?key=' . urlencode( $api_key ),
			array(
				'timeout'   => 5,
				'headers'   => array(
					'Content-Type' => 'application/json',
				),
				'body'      => wp_json_encode( $request_body ),
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

		// If no matches, URL is safe.
		if ( empty( $data['matches'] ) ) {
			set_transient( $cache_key, false, self::CACHE_TTL );
			return false;
		}

		// URL has threats.
		$threats = array();
		foreach ( $data['matches'] as $match ) {
			foreach ( $match['threatType'] as $threat_type ) {
				$threats[] = array(
					'threat_type' => $threat_type,
					'platform'    => $match['platformType'] ?? 'Unknown',
				);
			}
		}

		$threat_info = array(
			'threats' => $threats,
			'threat_count' => count( $threats ),
		);

		// Cache threat info.
		set_transient( $cache_key, $threat_info, self::CACHE_TTL );

		return $threat_info;
	}

	/**
	 * Determine severity based on threat types found.
	 *
	 * @since 0.6093.1200
	 * @param  array $unsafe_links Array of unsafe links.
	 * @return string Severity level.
	 */
	private static function determine_severity( array $unsafe_links ) : string {
		$count = count( $unsafe_links );

		// Multiple unsafe links = critical.
		if ( $count >= 5 ) {
			return 'critical';
		}

		// Several unsafe links = high.
		if ( $count >= 3 ) {
			return 'high';
		}

		// One or two = medium.
		return 'medium';
	}

	/**
	 * Calculate threat level (0-100 scale).
	 *
	 * @since 0.6093.1200
	 * @param  array $unsafe_links Array of unsafe links.
	 * @return int Threat level.
	 */
	private static function calculate_threat_level( array $unsafe_links ) : int {
		$count = count( $unsafe_links );

		if ( $count >= 10 ) {
			return 90;
		} elseif ( $count >= 5 ) {
			return 70;
		} elseif ( $count >= 3 ) {
			return 50;
		} else {
			return 35;
		}
	}

	/**
	 * Build user-friendly description.
	 *
	 * @since 0.6093.1200
	 * @param  array $unsafe_links Array of unsafe links.
	 * @return string Description text.
	 */
	private static function build_description( array $unsafe_links ) : string {
		$count = count( $unsafe_links );

		// What we found.
		$description = sprintf(
			/* translators: %d is the number of unsafe links */
			_n(
				'We found %d external link to a potentially unsafe website.',
				'We found %d external links to potentially unsafe websites.',
				$count,
				'wpshadow'
			),
			$count
		);

		$description .= ' ';

		// Why it matters.
		$description .= __(
			'These links point to sites that may contain malware, phishing scams, unwanted software, or other threats. Visitors clicking these links could be harmed, and it damages your site\'s reputation.',
			'wpshadow'
		);

		$description .= "\n\n";

		// List the unsafe links.
		$description .= __( 'Unsafe links found:', 'wpshadow' ) . "\n";
		foreach ( array_slice( $unsafe_links, 0, 10 ) as $link ) {
			// Extract threat types.
			$threat_types = array();
			foreach ( $link['threats'] ?? array() as $threat ) {
				$threat_types[] = self::translate_threat_type( $threat['threat_type'] );
			}

			$description .= sprintf(
				'• %s - %s (in %s)',
				esc_html( $link['url'] ),
				implode( ', ', $threat_types ),
				esc_html( $link['post_title'] )
			) . "\n";
		}

		if ( count( $unsafe_links ) > 10 ) {
			$description .= sprintf(
				__( '... and %d more', 'wpshadow' ),
				count( $unsafe_links ) - 10
			) . "\n";
		}

		$description .= "\n";

		// Action steps.
		$description .= __( 'What to do:', 'wpshadow' ) . "\n";
		$description .= __( '1. Review the links above', 'wpshadow' ) . "\n";
		$description .= __( '2. Decide if they are legitimate or if you added them unknowingly', 'wpshadow' ) . "\n";
		$description .= __( '3. Remove or update the links', 'wpshadow' ) . "\n";
		$description .= __( '4. If you were hacked, see our recovery guide', 'wpshadow' ) . "\n";

		return $description;
	}

	/**
	 * Translate threat type code to human-readable name.
	 *
	 * @since 0.6093.1200
	 * @param  string $threat_type Threat type code.
	 * @return string Human-readable threat name.
	 */
	private static function translate_threat_type( string $threat_type ) : string {
		$translations = array(
			'MALWARE'                           => __( 'Malware', 'wpshadow' ),
			'SOCIAL_ENGINEERING'                => __( 'Phishing/Social Engineering', 'wpshadow' ),
			'UNWANTED_SOFTWARE'                 => __( 'Unwanted Software', 'wpshadow' ),
			'POTENTIALLY_HARMFUL_APPLICATION'   => __( 'Harmful Application', 'wpshadow' ),
		);

		return $translations[ $threat_type ] ?? $threat_type;
	}
}
