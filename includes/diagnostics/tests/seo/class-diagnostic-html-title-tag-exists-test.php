<?php
/**
 * Title Tag Presence Test Diagnostic
 *
 * Tests for title tag existence in admin pages and frontend pages.
 *
 * @package WPShadow\Diagnostics
 * @subpackage HTML
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Admin_Page_Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Title Tag Presence Test Diagnostic Class
 *
 * Checks if admin pages and frontend pages have proper <title> tags.
 * Uses Admin_Page_Scanner helper to capture and analyze admin page output.
 */
class Diagnostic_Html_Title_Tag_Exists_Test extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-title-tag-exists-test';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Title Tag Presence Test';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests title tag existence in admin and frontend pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check admin pages if we have captured output
		if ( is_admin() ) {
			return self::check_admin_pages();
		}

		// Check frontend pages
		return self::check_frontend_page();
	}

	/**
	 * Check admin pages for title tags
	 *
	 * @since  1.2601.2148
	 * @return array|null
	 */
	private static function check_admin_pages() {
		// Load helper if not already loaded
		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		$pages_to_check = array(
			'index.php'              => 'Dashboard',
			'options-general.php'    => 'General Settings',
			'options-writing.php'    => 'Writing Settings',
			'options-reading.php'    => 'Reading Settings',
			'plugins.php'            => 'Plugins Page',
			'themes.php'             => 'Themes Page',
		);

		$missing_titles = array();

		foreach ( $pages_to_check as $page_slug => $page_name ) {
			$html = Admin_Page_Scanner::capture_admin_page( $page_slug );
			
			if ( false === $html ) {
				continue; // Skip if capture failed
			}

			$analysis = Admin_Page_Scanner::analyze_html( $html );
			
			if ( ! $analysis['has_title_tag'] || $analysis['title_length'] === 0 ) {
				$missing_titles[] = $page_name;
			}
		}

		if ( ! empty( $missing_titles ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of admin pages, %s: comma-separated list of page names */
					_n(
						'%d admin page is missing a title tag: %s',
						'%d admin pages are missing title tags: %s',
						count( $missing_titles ),
						'wpshadow'
					),
					count( $missing_titles ),
					implode( ', ', $missing_titles )
				),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/html-title-tag-exists-test',
				'meta'        => array(
					'missing_pages' => $missing_titles,
					'checked_pages' => count( $pages_to_check ),
				),
			);
		}

		return null;
	}

	/**
	 * Check frontend page for title tag
	 *
	 * @since  1.2601.2148
	 * @return array|null
	 */
	private static function check_frontend_page() {
		global $post;

		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) {
			return null;
		}

		if ( empty( $post->post_title ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Page has no title. Add a title to this page in the editor.', 'wpshadow' ),
				'severity'    => 'critical',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/html-title-tag-exists-test',
				'meta'        => array(
					'post_id' => $post->ID,
				),
			);
		}

		return null;
	}
}
