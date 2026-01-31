<?php
/**
 * Author Archive Pages Not Optimized Diagnostic
 *
 * Checks if author archives are optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author Archive Pages Not Optimized Diagnostic Class
 *
 * Detects unoptimized author archives.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Author_Archive_Pages_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'author-archive-pages-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Author Archive Pages Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if author archives are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if author archives are disabled
		if ( get_option( 'page_for_posts' ) && ! has_filter( 'author_template' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Author archive pages are not optimized. Optimize author archive pages with proper titles, descriptions, and schema markup.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/author-archive-pages-not-optimized',
			);
		}

		return null;
	}
}
