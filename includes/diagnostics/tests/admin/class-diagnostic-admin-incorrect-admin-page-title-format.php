<?php
/**
 * Admin Incorrect Admin Page Title Format Diagnostic
 *
 * Checks if admin pages have incorrect or malformed <title> format that doesn't follow WordPress standards.
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
 * Admin Incorrect Admin Page Title Format Diagnostic Class
 *
 * Detects admin pages with titles that have incorrect formatting patterns,
 * such as reversed order, double separators, or HTML entities in wrong places.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Incorrect_Admin_Page_Title_Format extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-incorrect-admin-page-title-format';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Incorrect Admin Page Title Format';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin pages have incorrect or malformed title format patterns';

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

		global $wp_filter;
		$site_name = get_bloginfo( 'name' );
		$issues = array();

		// Test title generation for common admin pages.
		$test_pages = array(
			'Dashboard'        => 'Dashboard',
			'Settings'         => 'General Settings',
			'Plugins'          => 'Plugins',
			'Appearance'       => 'Appearance',
			'Tools'            => 'Tools',
			'Media Library'    => 'Media Library',
		);

		foreach ( $test_pages as $page_title => $expected_page ) {
			// Simulate admin_title filter.
			$title = apply_filters( 'admin_title', $page_title, '' );

			// Detect incorrect patterns.

			// Pattern 1: Multiple consecutive separators.
			if ( preg_match( '/[‹—|\-]{2,}/', $title ) ) {
				$issues[] = sprintf(
					__( 'Page "%s" has multiple consecutive separator characters in title', 'wpshadow' ),
					esc_html( $expected_page )
				);
			}

			// Pattern 2: Site name appears before page name (reversed order).
			$title_parts = preg_split( '/[‹—|\-]/', $title );
			if ( count( $title_parts ) >= 2 ) {
				$first_part = trim( $title_parts[0] );
				if ( stripos( $first_part, $site_name ) !== false ) {
					$issues[] = sprintf(
						__( 'Page "%s" has site name before page name (incorrect order)', 'wpshadow' ),
						esc_html( $expected_page )
					);
				}
			}

			// Pattern 3: HTML entities not properly decoded in title.
			if ( preg_match( '/&[a-z]+;/', $title ) && $title === htmlentities( $title, ENT_QUOTES ) ) {
				$issues[] = sprintf(
					__( 'Page "%s" contains HTML entities that should be decoded', 'wpshadow' ),
					esc_html( $expected_page )
				);
			}

			// Pattern 4: Trailing or leading separator characters.
			$trimmed = trim( $title );
			if ( preg_match( '/^[‹—|\-\s]|[‹—|\-\s]$/', $trimmed ) ) {
				$issues[] = sprintf(
					__( 'Page "%s" has trailing or leading separator characters', 'wpshadow' ),
					esc_html( $expected_page )
				);
			}

			// Pattern 5: Missing "WordPress" suffix (core admin pages should have it).
			if ( false === stripos( $title, 'WordPress' ) && ! empty( $title ) && $title !== $page_title ) {
				// Only flag if filters have been applied but WordPress suffix is missing.
				if ( has_filter( 'admin_title' ) ) {
					$issues[] = sprintf(
						__( 'Page "%s" missing "WordPress" suffix in title', 'wpshadow' ),
						esc_html( $expected_page )
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues, %s: list of issues */
					_n(
						'%d admin page has incorrect title format: %s. Check for double separators, reversed order, or HTML entities.',
						'%d admin pages have incorrect title format: %s. Check for double separators, reversed order, or HTML entities.',
						count( $issues ),
						'wpshadow'
					),
					count( $issues ),
					implode( '; ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
				'meta'         => array(
					'issues'    => $issues,
					'site_name' => $site_name,
				),
			);
		}

		return null; // All admin page titles have correct format.
	}
}
