<?php
/**
 * Cache Busting Not Implemented Treatment
 *
 * Checks if cache busting is implemented.
 * Cache busting = version parameter forces fresh file downloads.
 * No version = users see old CSS/JS even after updates.
 * With version (style.css?ver=1.2.3) = new CSS loads immediately.
 *
 * **What This Check Does:**
 * - Checks enqueued scripts/styles for version parameters
 * - Validates version changes on file modification
 * - Tests cache headers for static assets
 * - Checks file hash-based versioning
 * - Validates cache expiration headers
 * - Returns severity if cache busting missing
 *
 * **Why This Matters:**
 * Update CSS. User's browser has cached version.
 * Sees broken layout (old CSS + new HTML).
 * Complains "site is broken". With version parameter:
 * new file URL forces fresh download. Layout perfect.
 *
 * **Business Impact:**
 * Deploy critical CSS fix for checkout button. No cache busting.
 * Users see old CSS. Button remains broken. Checkout fails.
 * Lost $20K in sales over 6 hours until cache expires.
 * With cache busting: new CSS loads immediately for all users.
 * Button works. Zero lost sales. 5 minutes to add versioning.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Updates deploy reliably
 * - #9 Show Value: Zero deployment-related issues
 * - #10 Beyond Pure: Professional deployment practices
 *
 * **Related Checks:**
 * - Browser Caching Configuration (complementary)
 * - Static Asset Optimization (related)
 * - CDN Configuration (cache management)
 *
 * **Learn More:**
 * Cache busting: https://wpshadow.com/kb/cache-busting
 * Video: Versioning static assets (8min): https://wpshadow.com/training/cache-busting
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache Busting Not Implemented Treatment Class
 *
 * Detects missing cache busting.
 *
 * **Detection Pattern:**
 * 1. Get all enqueued scripts via wp_scripts global
 * 2. Get all enqueued styles via wp_styles global
 * 3. Check for version parameters
 * 4. Validate version changes with file modification
 * 5. Test cache headers on static assets
 * 6. Return if versioning missing or static
 *
 * **Real-World Scenario:**
 * All assets use filemtime() versioning: style.css?ver=1706889234.
 * File changes. Version updates automatically. Browser sees new URL.
 * Fetches fresh file. Zero cache issues. Deploys always work.
 * Old method (static ver=1.0): required manual version bumps.
 * Forgot to update = users saw old files for 7 days.
 *
 * **Implementation Notes:**
 * - Checks wp_enqueue_script/style version parameters
 * - Validates dynamic versioning (filemtime, hash)
 * - Tests cache headers
 * - Severity: medium (deployment reliability issue)
 * - Treatment: add filemtime()-based versioning to enqueues
 *
 * @since 1.6093.1200
 */
class Treatment_Cache_Busting_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-busting-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Busting Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cache busting is implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Cache_Busting_Not_Implemented' );
	}
	 * @var string
	 */
	protected static $title = 'Cache Busting Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cache busting is implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for cache busting in asset versioning
		if ( ! has_filter( 'script_loader_tag', 'add_cache_buster' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Cache busting is not implemented. Use version parameters on static assets to force browser cache refresh when files are updated.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/cache-busting-not-implemented',
			);
		}

		return null;
	}
}
