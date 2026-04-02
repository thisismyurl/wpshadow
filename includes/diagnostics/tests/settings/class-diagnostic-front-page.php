<?php
/**
 * Front Page Configured Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Front_Page_Configured Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Front_Page_extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'front-page';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Front Page';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for Front Page';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check show_on_front/page_on_front/page_for_posts.
	 *
	 * TODO Fix Plan:
	 * - Assign homepage/posts page.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$display = WP_Settings::get_front_page_display();

		// Showing latest posts is a valid, intentional choice (blogs, news sites).
		if ( 'latest_posts' === $display ) {
			return null;
		}

		// Configured to show a static page — verify the page is assigned and published.
		$front_id = WP_Settings::get_front_page_id();
		if ( $front_id > 0 ) {
			$page = get_post( $front_id );
			if ( $page instanceof \WP_Post && 'publish' === $page->post_status ) {
				return null;
			}
		}

		$issue = $front_id > 0
			? sprintf(
				/* translators: %d: page ID */
				__( 'Front page is set to display a static page but post ID %d is not published.', 'wpshadow' ),
				$front_id
			)
			: __( 'Front page is configured to show a static page but no page has been selected.', 'wpshadow' );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your reading settings say the front page should show a static page, but the selected page is either missing or not published. Visitors will see an empty or unexpected front page.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/front-page-configured',
			'details'      => array(
				'issue'            => $issue,
				'show_on_front'    => 'page',
				'page_on_front_id' => $front_id,
			),
		);
	}
}
