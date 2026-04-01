<?php
/**
 * Media CORS Configuration Treatment
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
 * @subpackage Treatments\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_CORS_Configuration Class
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
 * @since 0.6093.1200
 */
class Treatment_Media_CORS_Configuration extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'media-cors-configuration';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Media CORS Configuration';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests Cross-Origin Resource Sharing settings for media';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - Access-Control-Allow-Origin header
	 * - CDN host differences
	 * - send_headers filters
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_CORS_Configuration' );
	}
}
