<?php
/**
 * Author Credentials Display Diagnostic
 *
 * Verifies site displays author bylines and expertise information
 * for trust and E-E-A-T (Experience, Expertise, Authoritativeness, Trust).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6034.2329
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author Credentials Display Diagnostic Class
 *
 * Analyzes site for proper author attribution and biographical
 * information for Google E-E-A-T and user trust.
 *
 * **Why This Matters:**
 * - Google's E-E-A-T requires author credentials
 * - Author attribution increases trust by 46%
 * - Required for YMYL (Your Money Your Life) content
 * - Builds personal authority and brand
 * - Professional sites need credible authors
 *
 * **Author Best Practices:**
 * - Display author bylines on all posts
 * - Author bio with credentials
 * - Author photo for recognition
 * - Links to author social profiles
 * - Author archive pages
 *
 * @since 1.6034.2329
 */
class Diagnostic_Displays_Author_Credentials extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'displays-author-credentials';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Author Credentials Display';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site displays author bylines and expertise information';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2329
	 * @return array|null Finding array if poor author display, null otherwise.
	 */
	public static function check() {
		$author_score = 0;
		$evidence = array();

		// Check 1: Authors have biographical information
		$authors_with_bios = self::count_authors_with_bios();
		if ( $authors_with_bios > 0 ) {
			$author_score += 30;
			$evidence[] = sprintf(
				/* translators: %d: number of authors with bios */
				__( '%d author(s) have biographical information', 'wpshadow' ),
				$authors_with_bios
			);
		}

		// Check 2: Theme displays author bylines
		if ( self::theme_shows_author_bylines() ) {
			$author_score += 25;
			$evidence[] = __( 'Theme displays author bylines on posts', 'wpshadow' );
		}

		// Check 3: Author plugin for enhanced display
		if ( self::has_author_plugin() ) {
			$author_score += 20;
			$evidence[] = __( 'Author display plugin installed', 'wpshadow' );
		}

		// Check 4: Multiple authors (team, not single blog)
		$author_count = self::count_active_authors();
		if ( $author_count >= 2 ) {
			$author_score += 15;
			$evidence[] = sprintf(
				/* translators: %d: number of active authors */
				__( '%d active author(s) contribute content', 'wpshadow' ),
				$author_count
			);
		}

		// Check 5: Authors have social links
		if ( self::authors_have_social_links() ) {
			$author_score += 10;
			$evidence[] = __( 'Authors have social profile links', 'wpshadow' );
		}

		// Score >= 50 indicates good author credentials
		if ( $author_score >= 50 ) {
			return null; // Author display is adequate
		}

		$severity = 'medium';
		$threat_level = 45;

		if ( $author_score < 25 ) {
			$severity = 'high';
			$threat_level = 60;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Weak author credential display. Google E-E-A-T requires visible author expertise. Author attribution increases trust by 46%.', 'wpshadow' ),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/author-credentials',
			'details'      => array(
				'author_score'   => $author_score,
				'evidence_found' => $evidence,
				'recommendation' => __( 'Add author bios, display bylines, and show credentials', 'wpshadow' ),
				'implementation_checklist' => array(
					'Write author bios (100+ words)',
					'Include credentials and expertise',
					'Add author photos',
					'Link to social profiles',
					'Enable author bylines in theme',
					'Create author archive pages',
					'Consider author box plugin',
				),
			),
		);
	}

	/**
	 * Count authors with biographical information
	 *
	 * @since  1.6034.2329
	 * @return int Number of authors with bios.
	 */
	private static function count_authors_with_bios() {
		$authors = get_users(
			array(
				'role__in' => array( 'author', 'editor', 'administrator' ),
				'has_published_posts' => true,
			)
		);

		$authors_with_bios = 0;
		foreach ( $authors as $author ) {
			$bio = get_user_meta( $author->ID, 'description', true );
			if ( ! empty( $bio ) && strlen( $bio ) >= 50 ) {
				$authors_with_bios++;
			}
		}

		return $authors_with_bios;
	}

	/**
	 * Check if theme displays author bylines
	 *
	 * @since  1.6034.2329
	 * @return bool True if bylines detected.
	 */
	private static function theme_shows_author_bylines() {
		$recent_post = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			)
		);

		if ( empty( $recent_post ) ) {
			return false;
		}

		// Check theme template for author display
		$theme = wp_get_theme();
		$template_file = locate_template( array( 'single.php', 'content-single.php', 'content.php' ) );

		if ( $template_file ) {
			$template_content = file_get_contents( $template_file );
			// Look for author display functions
			if ( preg_match( '/(the_author|get_the_author|author_link|byline)/', $template_content ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for author display plugins
	 *
	 * @since  1.6034.2329
	 * @return bool True if author plugin active.
	 */
	private static function has_author_plugin() {
		$author_plugins = array(
			'simple-author-box/simple-author-box.php',
			'wp-user-profile-avatar/wp-user-profile-avatar.php',
			'molongui-authorship/molongui-authorship.php',
			'co-authors-plus/co-authors-plus.php',
		);

		foreach ( $author_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count active contributing authors
	 *
	 * @since  1.6034.2329
	 * @return int Number of active authors.
	 */
	private static function count_active_authors() {
		$six_months_ago = date( 'Y-m-d', strtotime( '-6 months' ) );

		$authors = get_users(
			array(
				'role__in' => array( 'author', 'editor', 'administrator' ),
				'has_published_posts' => true,
			)
		);

		$active_count = 0;
		foreach ( $authors as $author ) {
			$recent_posts = get_posts(
				array(
					'author'         => $author->ID,
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'date_query'     => array(
						array(
							'after' => $six_months_ago,
						),
					),
				)
			);

			if ( ! empty( $recent_posts ) ) {
				$active_count++;
			}
		}

		return $active_count;
	}

	/**
	 * Check if authors have social profile links
	 *
	 * @since  1.6034.2329
	 * @return bool True if social links found.
	 */
	private static function authors_have_social_links() {
		$authors = get_users(
			array(
				'role__in' => array( 'author', 'editor', 'administrator' ),
				'has_published_posts' => true,
				'number'   => 5,
			)
		);

		foreach ( $authors as $author ) {
			$url = get_user_meta( $author->ID, 'url', true );
			$twitter = get_user_meta( $author->ID, 'twitter', true );
			$linkedin = get_user_meta( $author->ID, 'linkedin', true );

			if ( ! empty( $url ) || ! empty( $twitter ) || ! empty( $linkedin ) ) {
				return true;
			}
		}

		return false;
	}
}
