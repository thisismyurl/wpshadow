<?php
/**
 * Sample Content Removed Diagnostic
 *
 * Scans published posts and pages for Lorem Ipsum and other well-known
 * Latin placeholder phrases that indicate template content was never replaced.
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
 * Diagnostic_Sample_Content_Removed Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Sample_Content_Removed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'sample-content-removed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Placeholder Text Detected in Published Content';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Scans published posts and pages for Lorem Ipsum and other well-known placeholder phrases that indicate template content was never replaced.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Known Latin placeholder phrases used by page builders, themes, and demo packs.
	 *
	 * @var string[]
	 */
	private const PLACEHOLDER_PHRASES = array(
		'Lorem ipsum dolor sit amet',
		'consectetur adipiscing elit',
		'Pellentesque habitant morbi',
		'Quisque velit nisi',
		'Curabitur aliquet quam',
		'Nulla quis lorem ut libero',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Queries published posts and pages for any of the known Latin placeholder
	 * phrases. Reports every affected piece of content so the user knows
	 * exactly where to go.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		global $wpdb;

		/*
		 * Build a dynamic LIKE OR clause. Each placeholder is surrounded with
		 * wildcard % so it matches anywhere inside post_content.
		 */
		$clauses = array_map(
			static fn( string $p ) => $wpdb->prepare(
				'post_content LIKE %s',
				'%' . $wpdb->esc_like( $p ) . '%'
			),
			self::PLACEHOLDER_PHRASES
		);

		$where_or = implode( ' OR ', $clauses );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT ID, post_title, post_type, post_modified
			 FROM   {$wpdb->posts}
			 WHERE  post_status = 'publish'
			 AND    post_type   IN ('post', 'page')
			 AND    ( {$where_or} )
			 ORDER  BY post_modified DESC
			 LIMIT  200"
		);
		// phpcs:enable

		if ( empty( $rows ) ) {
			return null;
		}

		$affected = array();
		foreach ( array_slice( $rows, 0, 10 ) as $row ) {
			$affected[] = array(
				'post_id'    => (int) $row->ID,
				'post_title' => $row->post_title,
				'post_type'  => $row->post_type,
				'edit_url'   => get_edit_post_link( (int) $row->ID, 'raw' ),
			);
		}

		$total = count( $rows );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => 1 === $total
				? sprintf(
					/* translators: %s: post type (post or page) */
					__( 'One published %s still contains Lorem Ipsum or similar Latin placeholder text. Replace it with your real content before visitors arrive.', 'wpshadow' ),
					esc_html( $affected[0]['post_type'] )
				)
				: sprintf(
					/* translators: %d: number of posts/pages affected */
					_n(
						'%d published post or page still contains Lorem Ipsum or similar placeholder text.',
						'%d published posts and pages still contain Lorem Ipsum or similar placeholder text.',
						$total,
						'wpshadow'
					),
					$total
				),
			'severity'     => $total > 3 ? 'medium' : 'low',
			'threat_level' => $total > 3 ? 30 : 15,
			'kb_link'      => 'https://wpshadow.com/kb/remove-sample-wordpress-content?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'affected_count'  => $total,
				'affected_posts'  => $affected,
				'phrases_checked' => self::PLACEHOLDER_PHRASES,
				'fix'             => __( 'Open each affected post or page in the editor and replace every placeholder paragraph with your actual content.', 'wpshadow' ),
			),
		);
	}
}
