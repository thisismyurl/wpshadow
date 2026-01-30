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

		// Check if admin_title filter has been modified improperly.
		global $wp_filter;
		$site_name = get_bloginfo( 'name' );
		$issues = array();

		// Test title generation for common admin pages.
		$test_titles = array(
			'Dashboard' => 'Dashboard',
			'Settings' => 'General Settings',
			'Plugins' => 'Plugins',
		);

		foreach ( $test_titles as $page_title => $expected_page ) {
			// Simulate admin_title filter.
			$title = apply_filters( 'admin_title', $page_title, '' );

			// Check if title contains site name.
			if ( false === stripos( $title, $site_name ) ) {
				$issues[] = sprintf(
					__( 'Page "%s" title does not include site name', 'wpshadow' ),
					esc_html( $expected_page )
				);
			}

			// Check if title has proper separators (WordPress uses ‹ or — or | or -).
			$has_separator = (
				false !== strpos( $title, '‹' ) ||
				false !== strpos( $title, '—' ) ||
				false !== strpos( $title, '|' ) ||
				false !== strpos( $title, '-' )
			);

			if ( ! $has_separator && $site_name !== $page_title ) {
				$issues[] = sprintf(
					__( 'Page "%s" title missing separator character', 'wpshadow' ),
					esc_html( $expected_page )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues, %s: list of issues */
					_n(
						'%d admin page has incorrect title format: %s. Proper format should be: "Page Name ‹ Site Name — WordPress"',
						'%d admin pages have incorrect title format: %s. Proper format should be: "Page Name ‹ Site Name — WordPress"',
						count( $issues ),
						'wpshadow'
					),
					count( $issues ),
					implode( '; ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-missing-title-format',
				'meta'         => array(
					'issues'    => $issues,
					'site_name' => $site_name,
				),
			);
		}

		return null; // All admin page titles have proper format.
	}
}
