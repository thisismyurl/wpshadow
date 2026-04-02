<?php
/**
 * Search Engine Visibility Intentional Diagnostic (Stub)
 *
 * TODO stub mapped to the seo gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Search_Engine_Visibility_Intentional Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Search_Engine_Visibility_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'search-engine-visibility-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Search Engine Visibility Intentional';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the WordPress "discourage search engines" setting is active, which would prevent the entire site from being crawled and indexed.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check blog_public option.
	 *
	 * TODO Fix Plan:
	 * - Set indexability policy by environment.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$blog_public = get_option( 'blog_public', '1' );

		if ( '0' === (string) $blog_public ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress is configured to discourage search engines from indexing this site. This setting adds "Disallow: /" to robots.txt and outputs a noindex header, effectively hiding the site from Google and other search engines. If this is a live site, go to Settings → Reading and uncheck "Discourage search engines from indexing this site".', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/search-engine-visibility',
				'details'      => array(
					'blog_public' => 0,
				),
			);
		}

		return null;
	}
}
