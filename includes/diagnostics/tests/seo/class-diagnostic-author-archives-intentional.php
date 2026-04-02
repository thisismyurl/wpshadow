<?php
/**
 * Author Archives Intentional Diagnostic
 *
 * Verifies that author archives are intentionally enabled or disabled and
 * are not creating thin-content pages that harm SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Author_Archives_Intentional Class
 *
 * Evaluates whether author archives make sense given the number of site
 * authors, flagging single-author sites where the archive duplicates content.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Author_Archives_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'author-archives-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Author Archives Intentional';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether author archive pages are intentionally enabled or disabled, as single-author sites rarely need them and they can create duplicate content.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Counts distinct post authors, checks SEO-plugin author-archive indexing
	 * settings (Yoast/Rank Math), and flags single-author sites that expose
	 * indexable author archives without any SEO plugin managing them.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when author archives create thin content, null when healthy.
	 */
	public static function check() {
		global $wpdb;

		// Count distinct authors with published posts.
		$author_count = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_author)
			 FROM {$wpdb->posts}
			 WHERE post_status = 'publish'
			   AND post_type = 'post'"
		);

		// Multi-author sites: author archives add real value; no issue.
		if ( $author_count > 1 ) {
			return null;
		}

		$active_plugins = (array) get_option( 'active_plugins', array() );
		$has_yoast      = in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true )
		               || in_array( 'wordpress-seo-premium/wp-seo-premium.php', $active_plugins, true );
		$has_rankmath   = in_array( 'seo-by-rank-math/rank-math.php', $active_plugins, true )
		               || in_array( 'seo-by-rank-math-pro/rank-math-pro.php', $active_plugins, true );

		if ( $has_yoast ) {
			$titles = get_option( 'wpseo_titles', array() );
			// Default in Yoast is to noindex author archives. Only flag if explicitly set to index.
			if ( isset( $titles['noindex-author-wpseo'] ) && ! $titles['noindex-author-wpseo'] ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'This site has a single author and author archives are set to be indexed by Yoast SEO. Single-author sites produce thin author archive pages that duplicate the blog index. Set author archives to noindex in Yoast SEO → Search Appearance → Archives.', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/author-archives?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array( 'author_count' => $author_count, 'noindex_author' => false ),
				);
			}
			return null;
		}

		if ( $has_rankmath ) {
			// Rank Math noindexes author archives by default. Assume configured unless proven otherwise.
			return null;
		}

		// No SEO plugin on a single-author site.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'This appears to be a single-author site with no SEO plugin managing author archive indexability. Author archives on single-author sites act as near-duplicate versions of the blog index and should be set to noindex. Install an SEO plugin such as Yoast SEO or Rank Math and disable author archive indexing.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/author-archives?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array( 'author_count' => $author_count, 'noindex_author' => null ),
		);
	}
}
