<?php
/**
 * Search Enabled Diagnostic
 *
 * WordPress site search helps visitors find specific content quickly.
 * For sites with many published posts this is a key UX feature. However
 * some themes and plugins disable the search form without a deliberate
 * decision being made, leaving large content libraries undiscoverable.
 * This diagnostic flags sites whose content volume suggests search would
 * be valuable but where no search form is visible in the theme.
 *
 * @package WPShadow
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
 * Diagnostic_Search_Enabled_Intentional Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Search_Enabled_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'search-enabled-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Search Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether site search is available via a theme search form or widget for sites that have sufficient content to benefit from it.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'design';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Minimum number of published posts before we expect a search interface.
	 */
	private const CONTENT_THRESHOLD = 15;

	/**
	 * Run the diagnostic check.
	 *
	 * Counts published posts. When the count exceeds the threshold, scans
	 * the active theme templates for a search form and checks active widget
	 * areas for a search widget. Returns a finding only when content volume
	 * is high enough to justify search but no search form is detectable.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// Count published posts efficiently.
		$post_count = wp_count_posts( 'post' );
		$published  = (int) ( $post_count->publish ?? 0 );

		// Not enough content for search to add meaningful value — pass.
		if ( $published < self::CONTENT_THRESHOLD ) {
			return null;
		}

		// Check active widget areas for a search widget.
		$sidebars_widgets = wp_get_sidebars_widgets();
		foreach ( $sidebars_widgets as $sidebar_id => $widget_ids ) {
			if ( 'wp_inactive_widgets' === $sidebar_id || empty( $widget_ids ) ) {
				continue;
			}
			foreach ( (array) $widget_ids as $widget_id ) {
				if ( str_contains( (string) $widget_id, 'search' ) ) {
					return null;
				}
			}
		}

		// Check theme template files for a search form.
		$stylesheet_dir = get_stylesheet_directory();
		$template_dir   = get_template_directory();

		$dirs = array_unique(
			array_filter( array( $stylesheet_dir, $template_dir ), 'is_dir' )
		);

		$search_patterns = array(
			'get_search_form',
			'searchform',
			'search-form',
			'wp:search',          // block theme search block
			'type="search"',
			'name="s"',           // WordPress search query parameter
		);

		foreach ( $dirs as $dir ) {
			try {
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
				);
			} catch ( \Exception $e ) {
				continue;
			}

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() ) {
					continue;
				}

				$ext = strtolower( $file->getExtension() );
				if ( ! in_array( $ext, array( 'php', 'html' ), true ) ) {
					continue;
				}

				$content = file_get_contents( $file->getPathname() );
				if ( false === $content ) {
					continue;
				}

				foreach ( $search_patterns as $pattern ) {
					if ( str_contains( $content, $pattern ) ) {
						return null;
					}
				}
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of published posts */
				__( 'The site has %d published posts but no search form was detected in the active theme templates or widget areas. Visitors cannot search the content library, making specific content harder to find.', 'wpshadow' ),
				$published
			),
			'severity'     => 'low',
			'threat_level' => 20,
			'kb_link'      => 'https://wpshadow.com/kb/search-enabled-intentional?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'published_posts' => $published,
				'fix'             => __( 'Add a Search widget to a prominent sidebar or header widget area in Appearance &rsaquo; Widgets. Alternatively, add the Search block to your theme header template in the Site Editor, or call get_search_form() in your theme\'s header.php.', 'wpshadow' ),
			),
		);
	}
}
