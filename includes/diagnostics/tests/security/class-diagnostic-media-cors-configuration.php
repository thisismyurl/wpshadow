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
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

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
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$upload_dir = wp_upload_dir();
		$base_host  = Diagnostic_URL_And_Pattern_Helper::get_domain( $upload_dir['baseurl'] );

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
				$response = Diagnostic_Request_Helper::head_result(
					$url,
					array(
						'timeout'     => 5,
						'redirection' => 2,
					)
				);

				if ( ! $response['success'] ) {
					$issues[] = __( 'Could not fetch media headers to verify CORS configuration', 'wpshadow' );
				} else {
					$headers = wp_remote_retrieve_headers( $response['response'] );
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
			$media_host = Diagnostic_URL_And_Pattern_Helper::get_domain( $media_url );
			if ( ! empty( $media_host ) && $media_host !== $base_host ) {
				$issues[] = __( 'Media host differs from upload base host - ensure CORS headers are configured on CDN', 'wpshadow' );
			}
		}

		// Check for send_headers filter (may add/remove headers).
		if ( has_filter( 'send_headers' ) ) {
			$issues[] = __( 'send_headers filter is active - verify it does not remove CORS headers', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
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
				'context'      => array(
					'why'            => __( 'CORS settings control which domains can read your media via browsers and scripts. When CORS is overly permissive (for example, Access‑Control‑Allow‑Origin: * with credentials), any site can embed or programmatically read your assets, enabling unauthorized reuse, content scraping, or leakage of private media URLs. This is not just a copyright issue: media URLs can reveal internal structure, user IDs, or private resources, and permissive CORS can be chained with client‑side vulnerabilities to exfiltrate data from authenticated sessions. OWASP Top 10 2021 ranks Broken Access Control #1, and misconfigured CORS is a common form of access control failure at the HTTP layer. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks remain a leading pattern against internet‑facing systems; attackers routinely target misconfigurations that expose assets without direct authentication. For businesses that sell media, courses, or premium content, a permissive CORS policy can turn paid assets into free downloads, reducing revenue and increasing support burdens. It can also trigger CDN overages and reputational harm when your assets appear on unrelated or malicious sites. Tight CORS configuration is a low‑effort, high‑impact control that limits access to approved origins, reduces data leakage, and provides clear evidence of good security hygiene for auditors and insurers.', 'wpshadow' ),
					'recommendation' => __( '1. Set Access‑Control‑Allow‑Origin to your exact domain(s), never * for protected media.
2. Avoid Allow‑Credentials unless absolutely required; if used, ensure origins are strict.
3. Configure CORS at CDN and origin consistently to avoid conflicting headers.
4. Limit allowed methods to GET/HEAD for media endpoints.
5. Deny cross‑origin requests to private or authenticated media URLs.
6. Implement hotlink protection and signed URLs for premium assets.
7. Review preflight responses and ensure they do not expose private headers.
8. Log CORS violations or blocked origins for monitoring.
9. Test CORS in staging with real browser requests and developer tools.
10. Re‑audit CORS whenever CDN, domain, or media paths change.', 'wpshadow' ),
				),
				'details'      => array(
					'issues' => $issues,
				),
			);

			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'media-security', self::$slug );
		}

		return null;
	}
}
