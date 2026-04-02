<?php
/**
 * Posts Page Published Diagnostic
 *
 * When a static front page is configured and a Posts Page is also
 * assigned, that page must exist and be published. A draft or missing
 * posts page causes the blog index to silently fall back to the
 * front page or produce a 404, breaking the content discovery path.
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
 * Diagnostic_Posts_Page_Published Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Posts_Page_Published extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'posts-page-published';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Posts Page Published';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that when a Posts Page is assigned in Reading Settings, the page exists and is published so the blog archive is accessible to visitors.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * Only runs when a page_for_posts ID is configured. Checks the page
	 * exists and is in "publish" state. Returns null when no posts page is
	 * set (many sites don't have a blog) or when it is properly published.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$page_for_posts = (int) get_option( 'page_for_posts', 0 );

		// No posts page assigned — not applicable (many sites have no blog).
		if ( 0 === $page_for_posts ) {
			return null;
		}

		$page = get_post( $page_for_posts );

		if ( ! $page || 'publish' !== $page->post_status ) {
			$status_label = $page ? $page->post_status : 'not found';

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: page title, 2: page status */
					__( 'The Posts Page is assigned to &#8220;%1$s&#8221; (status: %2$s). The blog archive will not display until this page is published.', 'wpshadow' ),
					esc_html( $page ? $page->post_title : 'ID ' . $page_for_posts ),
					esc_html( $status_label )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/posts-page-published?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'page_id'     => $page_for_posts,
					'page_title'  => $page ? $page->post_title : '',
					'page_status' => $status_label,
					'edit_url'    => $page ? get_edit_post_link( $page_for_posts, 'raw' ) : '',
					'fix'         => __( 'Open the Posts Page in the editor and publish it, or go to Settings &rsaquo; Reading and select a different, already-published page as your Posts Page.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
