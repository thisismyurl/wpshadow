<?php
/**
 * Reading Settings Not Optimized Diagnostic
 *
 * Tests for reading/blog page settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reading Settings Not Optimized Diagnostic Class
 *
 * Tests for reading/blog page optimization.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Reading_Settings_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'reading-settings-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Reading Settings Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for reading/blog page settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check posts per page.
		$posts_per_page = get_option( 'posts_per_page' );

		if ( (int) $posts_per_page > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: posts per page */
				__( 'Posts per page is %d - may slow down homepage, consider reducing to 10-20', 'wpshadow' ),
				$posts_per_page
			);
		}

		if ( (int) $posts_per_page < 5 ) {
			$issues[] = __( 'Posts per page is very low - may negatively impact user experience', 'wpshadow' );
		}

		// Check static front page vs blog.
		$show_on_front = get_option( 'show_on_front' );

		if ( $show_on_front === 'posts' ) {
			$issues[] = __( 'Using blog as homepage - consider using static front page for better control', 'wpshadow' );
		} elseif ( $show_on_front === 'page' ) {
			$page_on_front = get_option( 'page_on_front' );

			if ( empty( $page_on_front ) ) {
				$issues[] = __( 'Static front page selected but not configured', 'wpshadow' );
			} else {
				$page = get_post( $page_on_front );
				if ( ! $page || $page->post_status !== 'publish' ) {
					$issues[] = __( 'Front page does not exist or is not published', 'wpshadow' );
				}
			}
		}

		// Check posts page.
		if ( $show_on_front === 'page' ) {
			$page_for_posts = get_option( 'page_for_posts' );

			if ( empty( $page_for_posts ) ) {
				$issues[] = __( 'No posts page configured - blog won\'t display properly', 'wpshadow' );
			}
		}

		// Check feed settings.
		$blog_public = get_option( 'blog_public' );

		if ( (int) $blog_public === 0 ) {
			$issues[] = __( 'Site is not visible to search engines', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/reading-settings-not-optimized',
			);
		}

		return null;
	}
}
