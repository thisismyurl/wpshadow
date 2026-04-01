<?php
/**
 * Author Archive Pages Not Indexed Diagnostic
 *
 * Checks if author archives are indexed.
 *
 * @package    WPShadow
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
 * Author Archive Pages Not Indexed Diagnostic Class
 *
 * Detects unindexed author archives.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Author_Archive_Pages_Not_Indexed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'author-archive-pages-not-indexed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Author Archive Pages Not Indexed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if author archives are indexed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if author archives are indexed
		if ( get_option( 'blog_public' ) === '0' ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Author archive pages are not indexed. Enable search engine indexing for author archives to improve author visibility and SEO.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/author-archive-pages-not-indexed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
