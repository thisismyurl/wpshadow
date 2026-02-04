<?php
/**
 * Canonical URL For Pagination Not Implemented Diagnostic
 *
 * Checks if canonical URLs for pagination are implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical URL For Pagination Not Implemented Diagnostic Class
 *
 * Detects missing pagination canonical tags.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Canonical_URL_For_Pagination_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'canonical-url-for-pagination-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Canonical URL For Pagination Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if canonical URLs for pagination are implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for pagination canonical tag handling
		if ( ! has_filter( 'wp_head', 'add_pagination_canonical' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Canonical URLs for pagination are not implemented. Use rel=next/prev or canonical tags for paginated content to prevent duplicate content issues.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/canonical-url-for-pagination-not-implemented',
			);
		}

		return null;
	}
}
