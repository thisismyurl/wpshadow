<?php
/**
 * Diagnostic: Permalink Structure Configuration
 *
 * Checks if WordPress uses SEO-friendly permalink structure.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Permalinks Class
 *
 * Detects if WordPress uses SEO-friendly permalink structure. URLs matter
 * for search engine optimization:
 *
 * **Bad (default):** example.com/?p=123  
 * **Good (recommended):** example.com/my-awesome-post/
 *
 * Search engines prefer clean, descriptive URLs that:
 * - Contain relevant keywords
 * - Are readable to humans
 * - Suggest content topic
 * - Are shareable and memorable
 *
 * Sites using default (?p=) URLs lose ranking potential compared to
 * competitors with clean permalinks.
 *
 * @since 1.2601.2200
 */
class Diagnostic_Permalinks extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'permalinks';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Structure';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Verifies WordPress uses SEO-friendly URLs instead of default parameter-based structure';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'SEO';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the permalink structure setting. Default or empty means using
	 * ?p=123 style URLs.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if using default URLs, null if using pretty permalinks.
	 */
	public static function check() {
		$permalink_structure = get_option( 'permalink_structure' );

		// Good: Using pretty permalinks
		if ( ! empty( $permalink_structure ) ) {
			return null;
		}

		// Low: Using default (?p=123) URLs
		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => __(
				'Your site is using the default permalink structure (?p=123), which is not SEO-friendly. Search engines prefer clean, descriptive URLs like /blog/my-awesome-post/. Enable pretty permalinks in WordPress settings.',
				'wpshadow'
			),
			'severity'           => 'low',
			'threat_level'       => 35,
			'site_health_status' => 'recommended',
			'auto_fixable'       => true,
			'kb_link'            => 'https://wpshadow.com/kb/seo-permalinks',
			'family'             => self::$family,
			'details'            => array(
				'current_structure'  => 'Default (?p=123)',
				'recommended'        => '/%postname%/ or /%year%/%month%/%postname%/',
				'seo_impact'         => 'Negative - clean URLs improve search rankings by 10-30%',
				'recommendation'     => 'Set permalink structure in WordPress Settings > Permalinks',
			),
		);
	}
}
