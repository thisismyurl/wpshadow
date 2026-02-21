<?php
/**
 * Media SSL/HTTPS Enforcement Treatment
 *
 * Validates that media files are served over HTTPS when site uses HTTPS.
 * Mixed content (HTTPS page loading HTTP media) breaks modern browsers.
 * Browser blocks media + shows security warnings. Also reduces SEO ranking.
 *
 * **What This Check Does:**
 * - Detects if site uses HTTPS
 * - Checks media URL configuration (should be HTTPS)
 * - Tests actual media loading (validates no mixed content)
 * - Scans page source for HTTP:// media references
 * - Checks CDN configuration (if present)
 * - Validates SSL certificate applies to media domain
 *
 * **Why This Matters:**
 * Mixed content kills user experience. Scenarios:
 * - Site uses HTTPS. Media URL is HTTP
 * - Browser: "Page secure, but images insecure"
 * - Browser blocks images (mixed content)
 * - Website appears broken (images missing)
 * - Users see warning icon (lost trust)
 * - Google penalizes (lower SEO ranking)
 *
 * **Business Impact:**
 * E-commerce site migrated to HTTPS. Forgot to update media URLs (still HTTP).
 * Customer visits product page. Images blocked (mixed content). Looks broken.
 * Customer leaves without buying. Conversion drop: 15-30%. Sales impact: $50K/month.
 * Plus SEO penalty (lower rankings). All fixable by updating media to HTTPS.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Site appears secure (no warnings)
 * - #9 Show Value: Quantified conversion impact
 * - #10 Beyond Pure: HTTPS everywhere
 *
 * **Related Checks:**
 * - HTTP to HTTPS Redirect (enforce HTTPS)
 * - SSL Certificate Installation (infrastructure)
 * - Mixed Content Detection (overall security)
 *
 * **Learn More:**
 * Media HTTPS setup: https://wpshadow.com/kb/wordpress-media-https
 * Video: Fixing mixed content (7min): https://wpshadow.com/training/mixed-content-media
 *
 * @package    WPShadow
 * @subpackage Treatments\Security
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_SSL_HTTPS_Enforcement Class
 *
 * Validates that media URLs use HTTPS when the site is HTTPS.
 *
 * **Detection Pattern:**
 * 1. Check if site uses HTTPS (wp-config, settings)
 * 2. Query media URL setting (should use HTTPS)
 * 3. Scan actual media URLs on homepage
 * 4. Check for HTTP:// prefix on media
 * 5. Test CDN configuration (if present)
 * 6. Return severity if mixed content detected
 *
 * **Real-World Scenario:**
 * WordPress site switched to HTTPS (good!). Media URLs still hardcoded as HTTP
 * in database. All images show mixed content warning. Users see broken site.
 * Conversion drops 20%. Single day lost: $10K in revenue. Fix: update media
 * URLs to HTTPS (free, 5 minutes). Prevents $300K annual loss.
 *
 * **Implementation Notes:**
 * - Checks WordPress media URL setting
 * - Validates HTTPS usage
 * - Tests actual page rendering (catches hardcoded URLs)
 * - Severity: critical (major mixed content), medium (minor issues)
 * - Treatment: update media URLs to HTTPS
 *
 * @since 1.6030.2148
 */
class Treatment_Media_SSL_HTTPS_Enforcement extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'media-ssl-https-enforcement';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Media SSL/HTTPS Enforcement';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media files are served over HTTPS';

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
	 * - Upload base URL protocol
	 * - Media URLs protocol
	 * - Mixed content in posts
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_SSL_HTTPS_Enforcement' );
	}
}
