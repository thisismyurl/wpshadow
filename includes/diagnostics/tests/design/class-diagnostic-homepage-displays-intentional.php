<?php
/**
 * Homepage Displays Intentional Diagnostic
 *
 * WordPress defaults to showing the latest blog posts on the homepage
 * (show_on_front = posts). For most non-blog sites this is unintentional:
 * visitors land on a raw list of posts instead of a crafted landing page
 * that communicates the site's purpose and drives conversions.
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
 * Diagnostic_Homepage_Displays_Intentional Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Homepage_Displays_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-displays-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Displays Intentional';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that the Reading Settings homepage configuration is set deliberately — either a static front page or a page-for-posts setup — rather than left at the WordPress default.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the show_on_front, page_on_front, and page_for_posts options
	 * to determine whether the homepage is configured deliberately.
	 *
	 * - show_on_front = 'posts' with no page_on_front set: WordPress default.
	 * - show_on_front = 'page' with page_on_front = 0: broken — page setting
	 *   selected but no page chosen.
	 * - show_on_front = 'page' with a valid page_on_front: intentional.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$show_on_front  = (string) get_option( 'show_on_front', 'posts' );
		$page_on_front  = (int) get_option( 'page_on_front', 0 );
		$page_for_posts = (int) get_option( 'page_for_posts', 0 );

		// Deliberate static-page configuration — always pass.
		if ( 'page' === $show_on_front && $page_on_front > 0 ) {
			return null;
		}

		// Broken: page display selected but no page chosen.
		if ( 'page' === $show_on_front && 0 === $page_on_front ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The homepage is set to display a static page (Reading Settings) but no page has been selected. Visitors will see an empty or default WordPress front page.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'kb_link'      => 'https://wpshadow.com/kb/homepage-displays-intentional?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'show_on_front'  => $show_on_front,
					'page_on_front'  => $page_on_front,
					'page_for_posts' => $page_for_posts,
					'fix'            => __( 'Go to Settings &rsaquo; Reading and choose a page for "Homepage". Create a dedicated homepage if none exists.', 'wpshadow' ),
				),
			);
		}

		// show_on_front = 'posts': WordPress default — likely unintentional for non-blogs.
		// Only flag if there are also no posts pages configured (pure default state).
		if ( 'posts' === $show_on_front && 0 === $page_for_posts ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The homepage is using the WordPress default setting (Latest Posts). Unless this is a pure blog, consider configuring a static homepage via Settings &rsaquo; Reading to give visitors a purposeful first impression.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'kb_link'      => 'https://wpshadow.com/kb/homepage-displays-intentional?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'show_on_front'  => $show_on_front,
					'page_on_front'  => $page_on_front,
					'page_for_posts' => $page_for_posts,
					'fix'            => __( 'Review Settings &rsaquo; Reading. If this is not a pure blog, create a Homepage page and a Blog page, then set "Homepage displays" to "A static page" and assign them. This gives visitors a designed front page rather than a raw posts feed.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
