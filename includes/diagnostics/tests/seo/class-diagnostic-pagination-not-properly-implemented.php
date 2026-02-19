<?php
/**
 * Pagination Not Properly Implemented Diagnostic
 *
 * Checks pagination.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Pagination_Not_Properly_Implemented Class
 *
 * Performs diagnostic check for Pagination Not Properly Implemented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Pagination_Not_Properly_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pagination-not-properly-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pagination Not Properly Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks pagination';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'implement_safe_pagination' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Pagination is not configured with safe patterns yet. Adding predictable page handling helps visitors browse large content collections more smoothly.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/pagination-not-properly-implemented',
			);
		}

		return null;
	}
}
