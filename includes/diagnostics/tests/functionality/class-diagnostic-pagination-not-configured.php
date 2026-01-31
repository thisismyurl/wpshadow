<?php
/**
 * Pagination Not Configured Diagnostic
 *
 * Checks if pagination is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2349
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pagination Not Configured Diagnostic Class
 *
 * Detects missing pagination.
 *
 * @since 1.2601.2349
 */
class Diagnostic_Pagination_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pagination-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pagination Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pagination is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2349
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if posts_per_page is more than 1
		$posts_per_page = get_option( 'posts_per_page' );

		if ( absint( $posts_per_page ) < 5 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Pagination is not properly configured. Increase posts per page and use pagination to improve navigation.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/pagination-not-configured',
			);
		}

		return null;
	}
}
