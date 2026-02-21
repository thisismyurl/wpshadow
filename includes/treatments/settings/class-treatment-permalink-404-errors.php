<?php
/**
 * Permalink 404 Errors Treatment
 *
 * Detects 404 errors from broken permalinks and tests URL accessibility.
 * This treatment identifies common permalink configuration issues that can
 * cause broken links and poor user experience.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1401
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink 404 Errors Treatment Class
 *
 * Checks for broken permalinks and URL accessibility issues.
 *
 * @since 1.6032.1401
 */
class Treatment_Permalink_404_Errors extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-404-errors';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink 404 Errors';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects 404 errors from broken permalinks and tests URL accessibility';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Permalink_404_Errors' );
	}

	/**
	 * Get URLs to test for accessibility.
	 *
	 * @since  1.6032.1401
	 * @return array Array of test URLs keyed by type.
	 */
	private static function get_test_urls(): array {
		$urls = array();

		// Test homepage.
		$urls['Homepage'] = home_url( '/' );

		// Test a recent post.
		$recent_post = get_posts(
			array(
				'numberposts' => 1,
				'post_status' => 'publish',
				'post_type'   => 'post',
			)
		);

		if ( ! empty( $recent_post ) ) {
			$urls['Recent Post'] = get_permalink( $recent_post[0]->ID );
		}

		// Test a page.
		$recent_page = get_posts(
			array(
				'numberposts' => 1,
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);

		if ( ! empty( $recent_page ) ) {
			$urls['Page'] = get_permalink( $recent_page[0]->ID );
		}

		// Test archive page.
		$urls['Archive'] = get_post_type_archive_link( 'post' );

		return array_filter( $urls );
	}

	/**
	 * Check custom post type permalink configuration.
	 *
	 * @since  1.6032.1401
	 * @return array Array of issues found.
	 */
	private static function check_custom_post_type_permalinks(): array {
		$issues     = array();
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		foreach ( $post_types as $post_type ) {
			if ( ! $post_type->has_archive ) {
				continue;
			}

			// Check if archive link is accessible.
			$archive_link = get_post_type_archive_link( $post_type->name );
			if ( ! $archive_link ) {
				$issues[] = sprintf(
					/* translators: %s: post type name */
					__( 'Custom post type "%s" has no archive link configured', 'wpshadow' ),
					$post_type->label
				);
			}
		}

		return $issues;
	}

	/**
	 * Check permalink structure for common problems.
	 *
	 * @since  1.6032.1401
	 * @return array Array of issues found.
	 */
	private static function check_permalink_structure(): array {
		$issues = array();
		global $wp_rewrite;

		if ( ! $wp_rewrite ) {
			return $issues;
		}

		// Check if permalinks are enabled.
		$using_permalinks = false;
		if ( method_exists( $wp_rewrite, 'using_permalinks' ) ) {
			$using_permalinks = $wp_rewrite->using_permalinks();
		} elseif ( isset( $wp_rewrite->using_permalinks_value ) ) {
			$using_permalinks = $wp_rewrite->using_permalinks_value;
		}

		if ( ! $using_permalinks ) {
			return $issues;
		}

		$permalink_structure = get_option( 'permalink_structure' );
		if ( ! $permalink_structure || ! is_string( $permalink_structure ) ) {
			return $issues;
		}

		// Check for trailing slash consistency.
		$trailing_slash = $wp_rewrite->use_trailing_slashes;
		if ( $trailing_slash && '/' !== substr( $permalink_structure, -1 ) ) {
			$issues[] = __( 'Permalink structure is inconsistent with trailing slash setting', 'wpshadow' );
		}

		// Warn about using %postname% only (can cause conflicts).
		if ( '/%postname%/' === $permalink_structure ) {
			/* translators: %postname% and %category% are WordPress permalink structure tags */
			$issues[] = __( 'Using /%postname%/ can cause conflicts with pages and archives (consider adding %category% or date)', 'wpshadow' );
		}

		return $issues;
	}
}
