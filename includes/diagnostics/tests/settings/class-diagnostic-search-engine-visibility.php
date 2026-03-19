<?php
/**
 * Search Engine Visibility Diagnostic
 *
 * Verifies that the site is not accidentally blocking search engines from
 * crawling and indexing the site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Engine Visibility Diagnostic Class
 *
 * Ensures site is not hidden from search engines.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Search_Engine_Visibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-engine-visibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Engine Visibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site is not hidden from search engines';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Site is not set to discourage search engines
	 * - robots.txt is not blocking crawlers
	 * - No noindex is set globally
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if search engines are discouraged.
		$blog_public = get_option( 'blog_public', 1 );

		if ( '0' === (string) $blog_public ) {
			$issues[] = __( 'Search engine visibility is set to discourage indexing; your site will not appear in search results', 'wpshadow' );
		}

		// Check for robots.txt blocking.
		if ( function_exists( 'get_home_path' ) ) {
			$robots_path = get_home_path() . 'robots.txt';
			if ( file_exists( $robots_path ) ) {
				$robots_content = file_get_contents( $robots_path );
				if ( $robots_content && false !== strpos( strtolower( $robots_content ), 'disallow: /' ) ) {
					$issues[] = __( 'robots.txt appears to block all crawlers from accessing the site', 'wpshadow' );
				}
			}
		}

		// Check for global noindex.
		if ( isset( $GLOBALS['wp_query'] ) ) {
			if ( get_the_ID() && is_singular() ) {
				$noindex = get_post_meta( get_the_ID(), '_yoast_wpseo_meta-robots-noindex', true );
				if ( '1' === $noindex ) {
					$issues[] = __( 'Current page/post has noindex set; it will not be indexed by search engines', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/search-engine-visibility',
			);
		}

		return null;
	}
}
