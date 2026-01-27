<?php
/**
 * Admin Missing Title Format Diagnostic
 *
 * Checks if admin pages have proper <title> format with page name + site name.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Missing Title Format Diagnostic Class
 *
 * Validates that WordPress admin pages have proper <title> tags
 * with correct format: "Page Name ‹ Site Name — WordPress"
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Missing_Title_Format extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-missing-title-format';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Page Title Format';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin pages have proper title format with page name and site name';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run in admin context.
		if ( ! is_admin() ) {
			return null;
		}

		// Load Admin_Page_Scanner helper.
		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		$pages_to_check = array(
			'index.php'              => 'Dashboard',
			'options-general.php'    => 'General Settings',
			'plugins.php'            => 'Plugins',
		);

		$site_name = get_bloginfo( 'name' );
		$incorrect_titles = array();

		foreach ( $pages_to_check as $page_slug => $page_name ) {
			$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( $page_slug );
			
			if ( false === $html ) {
				continue;
			}

			// Extract title.
			if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $matches ) ) {
				$title_content = strip_tags( $matches[1] );
				
				// Check if title contains site name.
				if ( false === stripos( $title_content, $site_name ) ) {
					$incorrect_titles[] = $page_name . ': "' . $title_content . '"';
				}
				
				// Check if title follows WordPress format (contains ‹ or |).
				if ( false === strpos( $title_content, '‹' ) && false === strpos( $title_content, '|' ) && false === strpos( $title_content, '-' ) ) {
					$incorrect_titles[] = $page_name . ': No separator found';
				}
			}
		}

		if ( ! empty( $incorrect_titles ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of pages, %s: list of pages */
					_n(
						'%d admin page has incorrect title format: %s. Proper format should be: "Page Name ‹ Site Name — WordPress"',
						'%d admin pages have incorrect title format: %s. Proper format should be: "Page Name ‹ Site Name — WordPress"',
						count( $incorrect_titles ),
						'wpshadow'
					),
					count( $incorrect_titles ),
					implode( '; ', $incorrect_titles )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-missing-title-format',
				'meta'         => array(
					'incorrect_pages' => $incorrect_titles,
					'site_name'       => $site_name,
				),
			);
		}

		return null;
	}
}
