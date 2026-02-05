<?php
/**
 * WCAG 2.4.2 Page Titled Treatment
 *
 * Validates that every page has a descriptive title element.
 *
 * @since   1.6035.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Page Titles Treatment Class
 *
 * Checks for proper <title> elements on all pages (WCAG 2.4.2 Level A).
 *
 * @since 1.6035.1200
 */
class Treatment_WCAG_Page_Titles extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-page-titles';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Page Titles (WCAG 2.4.2)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that every page has a descriptive title element';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if theme supports title-tag.
		if ( ! current_theme_supports( 'title-tag' ) ) {
			$issues[] = __( 'Theme does not support automatic title tags. Add add_theme_support(\'title-tag\') to functions.php', 'wpshadow' );
		}

		// Check theme header for manual <title> tag (anti-pattern).
		$theme_header = get_template_directory() . '/header.php';
		if ( file_exists( $theme_header ) ) {
			$content = file_get_contents( $theme_header );

			// Check if wp_head() is called.
			if ( strpos( $content, 'wp_head()' ) === false ) {
				$issues[] = __( 'Theme header.php is missing wp_head() call, which is required for title generation', 'wpshadow' );
			}

			// Check for hardcoded <title> tags (bad practice).
			if ( preg_match( '/<title[^>]*>/', $content ) ) {
				$issues[] = __( 'Theme has hardcoded <title> tag. Remove it and use add_theme_support(\'title-tag\') instead', 'wpshadow' );
			}
		}

		// Sample recent posts to check for generic titles.
		$posts = get_posts(
			array(
				'numberposts' => 10,
				'post_status' => 'publish',
				'post_type'   => 'any',
			)
		);

		$generic_count = 0;
		$site_name     = get_bloginfo( 'name' );

		foreach ( $posts as $post ) {
			$title = get_the_title( $post->ID );
			if ( empty( $title ) || $title === $site_name || $title === 'Home' ) {
				$generic_count++;
			}
		}

		if ( $generic_count > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with generic titles */
				__( 'Found %d posts with missing or generic titles. Each page should have a unique, descriptive title', 'wpshadow' ),
				$generic_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Page titles are like book chapter headings—they help everyone understand where they are. Screen reader users especially rely on titles to navigate between browser tabs and bookmarks. Without proper titles, all your tabs would just say "Home" and users couldn\'t tell them apart. Think of it like labeling folders: "Documents" is better than just having 5 folders all called "Folder".', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-page-titles',
			);
		}

		return null;
	}
}
