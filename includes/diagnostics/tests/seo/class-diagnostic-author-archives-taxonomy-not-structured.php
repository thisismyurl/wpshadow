<?php
/**
 * Author Archives Taxonomy Not Structured Diagnostic
 *
 * Checks if author archives have taxonomy structure.
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
 * Author Archives Taxonomy Not Structured Diagnostic Class
 *
 * Detects missing author taxonomy structure.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Author_Archives_Taxonomy_Not_Structured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'author-archives-taxonomy-not-structured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Author Archives Taxonomy Not Structured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if author archives have taxonomy structure';

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
		// Check for author page schema
		if ( ! has_filter( 'wp_head', 'add_author_archive_schema' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Author archive taxonomy is not structured. Add Author schema markup to author archive pages for better search visibility.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/author-archives-taxonomy-not-structured',
			);
		}

		return null;
	}
}
