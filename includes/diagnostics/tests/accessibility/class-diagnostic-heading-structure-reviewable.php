<?php
/**
 * Heading Structure Reviewable Diagnostic
 *
 * Scans published posts and pages for two heading anti-patterns that
 * break screen-reader navigation: an H1 inside the post body (which
 * duplicates the post title acting as the page H1) and skipped heading
 * levels (e.g. H2 → H4 with no H3), violating WCAG 1.3.1.
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
 * Diagnostic_Heading_Structure_Reviewable Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Heading_Structure_Reviewable extends Diagnostic_Base {

	/** @var string */
	protected static $slug = 'heading-structure-reviewable';

	/** @var string */
	protected static $title = 'Heading Structure Reviewable';

	/** @var string */
	protected static $description = 'Scans published posts and pages for heading issues: an H1 inside the post body (duplicating the title) or skipped heading levels (e.g. H2 to H4), which disrupts screen-reader document outline navigation.';

	/** @var string */
	protected static $family = 'accessibility';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Fetches up to 100 recently modified published posts and pages, builds
	 * an ordered list of headings from each post_content, then checks for:
	 *  - An H1 tag inside the body (duplicate with the post title).
	 *  - A heading level that skips a rank (e.g. H2 directly to H4).
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$affected = array();

		foreach ( $posts as $post ) {
			if ( count( $affected ) >= 10 ) {
				break;
			}

			$content = (string) $post->post_content;
			if ( '' === $content ) {
				continue;
			}

			// Extract heading levels in document order.
			if ( ! preg_match_all( '/<h([1-6])[\s>]/i', $content, $matches ) ) {
				continue;
			}

			$levels = array_map( 'intval', $matches[1] );

			$has_h1        = in_array( 1, $levels, true );
			$skipped_level = null;

			for ( $i = 1; $i < count( $levels ); $i++ ) {
				if ( $levels[ $i ] > $levels[ $i - 1 ] + 1 ) {
					$skipped_level = 'H' . $levels[ $i - 1 ] . ' to H' . $levels[ $i ];
					break;
				}
			}

			if ( ! $has_h1 && null === $skipped_level ) {
				continue;
			}

			$item = array(
				'post_id'    => $post->ID,
				'post_title' => $post->post_title,
				'post_type'  => $post->post_type,
				'edit_url'   => get_edit_post_link( $post->ID, 'raw' ),
			);

			if ( $has_h1 ) {
				$item['issue'] = __( 'H1 found inside post body — conflicts with title-level H1.', 'wpshadow' );
			} else {
				/* translators: %s: heading jump e.g. "H2 to H4" */
				$item['issue'] = sprintf( __( 'Heading level skipped: %s.', 'wpshadow' ), $skipped_level );
			}

			$affected[] = $item;
		}

		if ( empty( $affected ) ) {
			return null;
		}

		$count = count( $affected );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of posts */
				_n(
					'%d published post or page has a heading structure issue (H1 in body or skipped level) that makes screen-reader document navigation unreliable.',
					'%d published posts and pages have heading structure issues (H1 in body or skipped levels) that make screen-reader document navigation unreliable.',
					$count,
					'wpshadow'
				),
				$count
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'kb_link'      => '',
			'details'      => array(
				'affected_count' => $count,
				'affected_posts' => $affected,
				'fix'            => __( 'Edit each flagged post: remove any H1 tags from the body content (the post title serves as the page H1), and ensure heading levels increase sequentially without skipping ranks.', 'wpshadow' ),
			),
		);
	}
}
