<?php
/**
 * Homepage Page Published Diagnostic
 *
 * When the Reading Settings are configured to show a static page as the
 * homepage, that page must exist and be published. A draft or deleted
 * homepage page causes visitors to see a blank or fallback template
 * instead of the intended site front page.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Homepage_Page_Published Class
 *
 * @since 0.6095
 */
class Diagnostic_Homepage_Page_Published extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-page-published';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Page Published';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that when a static front page is configured in Reading Settings, the assigned page actually exists and has a published status.';

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
	 * Run the diagnostic check.
	 *
	 * Only runs when show_on_front = 'page'. Checks that page_on_front
	 * maps to a real published page. Returns null if the site is set to
	 * display latest posts (the page check is not applicable).
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$show_on_front = (string) get_option( 'show_on_front', 'posts' );

		// Not using a static front page — nothing to validate.
		if ( 'page' !== $show_on_front ) {
			return null;
		}

		$page_on_front = (int) get_option( 'page_on_front', 0 );

		if ( 0 === $page_on_front ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Reading Settings are configured to display a static homepage, but no page has been selected. The front page will be blank or show a fallback template.', 'thisismyurl-shadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'details'      => array(
					'fix' => __( 'Go to Settings &rsaquo; Reading, choose "A static page" under "Homepage displays", and select a published page as your Homepage.', 'thisismyurl-shadow' ),
				),
			);
		}

		$page = get_post( $page_on_front );

		if ( ! $page || 'publish' !== $page->post_status ) {
			$status_label = $page ? $page->post_status : 'not found';

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: page title, 2: page status */
					__( 'The static homepage is assigned to the page &#8220;%1$s&#8221; (status: %2$s). Visitors will not see the intended front page content until it is published.', 'thisismyurl-shadow' ),
					esc_html( $page ? $page->post_title : 'ID ' . $page_on_front ),
					esc_html( $status_label )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'details'      => array(
					'page_id'     => $page_on_front,
					'page_title'  => $page ? $page->post_title : '',
					'page_status' => $status_label,
					'edit_url'    => $page ? get_edit_post_link( $page_on_front, 'raw' ) : '',
					'fix'         => __( 'Open the assigned homepage in the editor and publish it, or go to Settings &rsaquo; Reading and select a different, already-published page as your Homepage.', 'thisismyurl-shadow' ),
				),
			);
		}

		return null;
	}
}
