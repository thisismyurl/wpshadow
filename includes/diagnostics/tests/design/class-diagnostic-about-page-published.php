<?php
/**
 * About Page Published Diagnostic
 *
 * An About page builds visitor trust by explaining who is behind the site,
 * their mission, and their credentials. Without one, first-time visitors
 * have no way to evaluate credibility before deciding to engage.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_About_Page_Published Class
 *
 * @since 0.6095
 */
class Diagnostic_About_Page_Published extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'about-page-published';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'About Page Published';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that a published About page exists so visitors can learn who is behind the site and make an informed decision to engage.';

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
	 * Slug fragments and title keywords associated with About pages.
	 */
	private const ABOUT_KEYWORDS = array(
		'about',
		'about-us',
		'our-story',
		'who-we-are',
		'meet-the-team',
		'our-team',
		'company',
		'about-me',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Fetches all published pages and checks whether any of them have a slug
	 * or post title that matches common About page naming conventions.
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'all',
				'no_found_rows'  => true,
			)
		);

		if ( empty( $pages ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No published pages were found on this site. An About page helps visitors understand who is behind the site and builds the trust needed to convert.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'details'      => array(
					'fix' => __( 'Create and publish an About page. Include information about who you are, your mission, and why visitors should trust you. Link to it from your main navigation.', 'wpshadow' ),
				),
			);
		}

		foreach ( $pages as $page ) {
			$slug  = strtolower( $page->post_name );
			$title = strtolower( $page->post_title );

			foreach ( self::ABOUT_KEYWORDS as $keyword ) {
				if ( str_contains( $slug, $keyword ) || str_contains( $title, str_replace( '-', ' ', $keyword ) ) ) {
					return null;
				}
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No published About page was detected. Visitors who want to learn about the people or organisation behind the site have nowhere to go, which reduces trust and conversions.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'details'      => array(
				'fix' => __( 'Create a published page with a URL slug or title containing "about". Include your background, mission, and any social proof (photos, credentials, testimonials). Add it to your primary navigation menu.', 'wpshadow' ),
			),
		);
	}
}
