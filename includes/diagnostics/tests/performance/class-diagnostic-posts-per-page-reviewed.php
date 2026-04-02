<?php
/**
 * Posts Per Page Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the performance gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Posts_Per_Page_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Posts_Per_Page_Reviewed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'posts-per-page-reviewed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Posts Per Page Reviewed';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for Posts Per Page Reviewed';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check posts_per_page for extremely high or low values affecting archives.
	 *
	 * TODO Fix Plan:
	 * - Set archive pagination to balance crawlability, UX, and performance.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$count = WP_Settings::get_posts_per_page();

		// Up to 20 posts per page is considered a reasonable default.
		if ( $count <= 20 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: posts per page setting */
				__( 'Your site is configured to display %d posts per page on archives and the blog roll. Fetching and rendering a large number of posts on a single page increases database query load, PHP memory usage, and page weight. Consider reducing this to 10–15 and relying on pagination instead.', 'wpshadow' ),
				$count
			),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/posts-per-page',
			'details'      => array(
				'posts_per_page' => $count,
				'recommended_max' => 20,
			),
		);
	}
}
