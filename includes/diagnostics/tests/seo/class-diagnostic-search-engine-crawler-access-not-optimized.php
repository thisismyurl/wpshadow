<?php
/**
 * Search Engine Crawler Access Not Optimized Diagnostic
 *
 * Checks if search engine crawler access is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2346
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Engine Crawler Access Not Optimized Diagnostic Class
 *
 * Detects unoptimized crawler access.
 *
 * @since 1.2601.2346
 */
class Diagnostic_Search_Engine_Crawler_Access_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-engine-crawler-access-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Engine Crawler Access Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if crawler access is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2346
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if search engines discouraged
		$blog_public = get_option( 'blog_public' );

		if ( '0' === (string) $blog_public ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Search engines are discouraged from indexing your site. Enable indexing in Settings to improve visibility.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/search-engine-crawler-access-not-optimized',
			);
		}

		return null;
	}
}
