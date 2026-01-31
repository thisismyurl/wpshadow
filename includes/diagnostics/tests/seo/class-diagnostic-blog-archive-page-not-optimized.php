<?php
/**
 * Blog Archive Page Not Optimized Diagnostic
 *
 * Checks if blog archive pages are optimized.
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
 * Blog Archive Page Not Optimized Diagnostic Class
 *
 * Detects unoptimized archive pages.
 *
 * @since 1.2601.2349
 */
class Diagnostic_Blog_Archive_Page_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'blog-archive-page-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Blog Archive Page Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if blog archive pages are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2349
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if posts_per_page is set reasonably
		$posts_per_page = get_option( 'posts_per_page' );

		if ( absint( $posts_per_page ) > 20 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Blog shows %d posts per page. Reduce to 10-15 for better performance and user experience.', 'wpshadow' ),
					absint( $posts_per_page )
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/blog-archive-page-not-optimized',
			);
		}

		return null;
	}
}
