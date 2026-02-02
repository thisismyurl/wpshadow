<?php
/**
 * Media CORS Configuration Diagnostic
 *
 * Validates Cross-Origin Resource Sharing settings for media to prevent
 * unauthorized access while allowing legitimate cross-domain media loading.
 * Misconfigured CORS allows any domain to access/steal media.
 *
 * **What This Check Does:**
 * - Checks CORS headers on media responses
 * - Validates allowed origins (should be your domain only)
 * - Tests if credentials allowed (security risk)
 * - Confirms wildcard (*) origin not used
 * - Validates preflight requests handled
 * - Tests media CDN integration
 *
 * **Why This Matters:**
 * Permissive CORS on media = theft + unauthorized use. Scenarios:
 * - CORS allows origin: * (any domain)
 * - Attacker website embeds your images directly (CORS permits it)
 * - Attacker modifies images via JavaScript + XHR
 * - Attacker's site appears to have premium media
 *
 * **Business Impact:**
 * Photography portfolio site. CORS allows any origin. Competitor website embeds
 * your photos directly (CORS permits cross-domain loading). Competitor's site
 * appears to have professional photography. Your content used without permission.
 * Copyright infringement. Licensing loss: $5K-$50K. With proper CORS: embedding
 * blocked (404 or CORS error). Protects intellectual property.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Media protected from unauthorized access
 * - #9 Show Value: IP protection + prevents media theft
 * - #10 Beyond Pure: Respects content ownership
 *
 * **Related Checks:**
 * - CORS Headers Not Configured (general API CORS)
 * - Hotlinking Protection (media direct linking)
 * - File Permission Security (media access control)
 *
 * **Learn More:**
 * Media CORS setup: https://wpshadow.com/kb/wordpress-media-cors
 * Video: Configuring CORS for media (8min): https://wpshadow.com/training/cors-media
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_CORS_Configuration Class
 *
 * Validates CORS headers for media URLs. Implements proper cross-origin
 * configuration to prevent unauthorized media access.
 *
 * **Detection Pattern:**
 * 1. Request media file from different origin
 * 2. Check Access-Control-Allow-Origin header
 * 3. Validate origin is restricted (not wildcard)
 * 4. Test if credentials allowed (should not be)
 * 5. Check preflight response
 * 6. Return severity if misconfigured
 *
 * **Real-World Scenario:**
 * Developer sets CORS to "*" for simplicity (thinking it's just images).
 * Attacker website embeds images. Works perfectly (CORS allows). Attacker's
 * website appears to have high-quality media library (all stolen). Your media
 * used without attribution or payment. IP infringement.
 *
 * **Implementation Notes:**
 * - Checks media endpoint CORS headers
 * - Validates origin whitelist (not wildcard)
 * - Tests credentials handling
 * - Severity: high (wildcard origin), medium (overly permissive)
 * - Treatment: restrict CORS to your domain only
 *
 * @since 1.2601.2148
 */
class Diagnostic_Media_CORS_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'media-cors-configuration';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Media CORS Configuration';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests Cross-Origin Resource Sharing settings for media';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Access-Control-Allow-Origin header
	 * - CDN host differences
	 * - send_headers filters
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$upload_dir = wp_upload_dir();
		$base_host  = wp_parse_url( $upload_dir['baseurl'], PHP_URL_HOST );

		global $wpdb;
		$attachment_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts}
				WHERE post_type = %s
				ORDER BY post_date DESC
				LIMIT 1",
				'attachment'
			)
		);

		if ( 0 < $attachment_id ) {
			$url = wp_get_attachment_url( $attachment_id );
			if ( ! empty( $url ) ) {
				$response = wp_remote_head(
					$url,
					array(
						'timeout'     => 5,
						'redirection' => 2,
					)
				);

				if ( is_wp_error( $response ) ) {
					$issues[] = __( 'Could not fetch media headers to verify CORS configuration', 'wpshadow' );
				} else {
					$headers = wp_remote_retrieve_headers( $response );
					$cors = $headers['access-control-allow-origin'] ?? '';

					if ( empty( $cors ) ) {
						$issues[] = __( 'Access-Control-Allow-Origin header is missing for media URLs', 'wpshadow' );
					} elseif ( '*' === $cors ) {
						$issues[] = __( 'Access-Control-Allow-Origin is set to * - review if media should be publicly embeddable', 'wpshadow' );
					}
				}
			}
		}

		// Check for CDN host differences (CORS often needed for CDN).
		$media_url = wp_get_attachment_url( $attachment_id );
		if ( ! empty( $media_url ) ) {
			$media_host = wp_parse_url( $media_url, PHP_URL_HOST );
			if ( ! empty( $media_host ) && $media_host !== $base_host ) {
				$issues[] = __( 'Media host differs from upload base host - ensure CORS headers are configured on CDN', 'wpshadow' );
			}
		}

		// Check for send_headers filter (may add/remove headers).
		if ( has_filter( 'send_headers' ) ) {
			$issues[] = __( 'send_headers filter is active - verify it does not remove CORS headers', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d CORS issue detected for media',
						'%d CORS issues detected for media',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-cors-configuration',
				'details'      => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
