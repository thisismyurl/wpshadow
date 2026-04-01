<?php
/**
 * Permalink 404 Errors Diagnostic
 *
 * Detects 404 errors from broken permalinks and tests URL accessibility.
 * This diagnostic identifies common permalink configuration issues that can
 * cause broken links and poor user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink 404 Errors Diagnostic Class
 *
 * Checks for broken permalinks and URL accessibility issues.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Permalink_404_Errors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-404-errors';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink 404 Errors';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects 404 errors from broken permalinks and tests URL accessibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if permalinks are enabled.
		global $wp_rewrite;
		$using_permalinks = false;

		if ( ! $wp_rewrite ) {
			$issues[] = __( 'WordPress rewrite system not initialized', 'wpshadow' );
		} else {
			// Handle both real WP_Rewrite object and test mocks.
			if ( method_exists( $wp_rewrite, 'using_permalinks' ) ) {
				$using_permalinks = $wp_rewrite->using_permalinks();
			} elseif ( isset( $wp_rewrite->using_permalinks_value ) ) {
				// Test mock support.
				$using_permalinks = $wp_rewrite->using_permalinks_value;
			}

			if ( ! $using_permalinks ) {
				$issues[] = __( 'Pretty permalinks are not enabled (using default ?p=123 structure)', 'wpshadow' );
			}
		}

		// Check for .htaccess issues on Apache servers.
		if ( function_exists( 'apache_get_version' ) || ( isset( $_SERVER['SERVER_SOFTWARE'] ) && false !== stripos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'apache' ) ) ) {
			$htaccess_file = ABSPATH . '.htaccess';

			if ( ! file_exists( $htaccess_file ) ) {
				$issues[] = __( '.htaccess file is missing (permalinks may not work)', 'wpshadow' );
			} elseif ( ! is_writable( $htaccess_file ) ) {
				// @codingStandardsIgnoreLine - is_writable is needed for file permission check.
				$issues[] = __( '.htaccess file is not writable (cannot update rewrite rules)', 'wpshadow' );
			} else {
				// Check if .htaccess contains WordPress rewrite rules.
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file read.
				$htaccess_content = file_get_contents( $htaccess_file );
				if ( false === strpos( $htaccess_content, 'BEGIN WordPress' ) ) {
					$issues[] = __( '.htaccess lacks WordPress rewrite rules (permalinks may fail)', 'wpshadow' );
				}
			}
		}

		// Test a few sample URLs for accessibility.
		$test_urls   = self::get_test_urls();
		$broken_urls = array();

		foreach ( $test_urls as $url_type => $url ) {
			$response = Diagnostic_Request_Helper::head_result(
				$url,
				array(
					'timeout'     => 5,
					'redirection' => 0,
					'sslverify'   => false,
				)
			);

			if ( ! $response['success'] ) {
				$broken_urls[] = sprintf(
					/* translators: 1: URL type, 2: URL */
					__( '%1$s URL (%2$s) returned an error: %3$s', 'wpshadow' ),
					$url_type,
					esc_url( $url ),
					$response['error_message']
				);
			} elseif ( 404 === (int) $response['code'] ) {
				$broken_urls[] = sprintf(
					/* translators: 1: URL type, 2: URL */
					__( '%1$s URL (%2$s) returns 404 error', 'wpshadow' ),
					$url_type,
					esc_url( $url )
				);
			}
		}

		if ( ! empty( $broken_urls ) ) {
			$issues = array_merge( $issues, $broken_urls );
		}

		// Check for custom post type permalink issues.
		$cpt_issues = self::check_custom_post_type_permalinks();
		if ( ! empty( $cpt_issues ) ) {
			$issues = array_merge( $issues, $cpt_issues );
		}

		// Check permalink structure for common problems.
		$structure_issues = self::check_permalink_structure();
		if ( ! empty( $structure_issues ) ) {
			$issues = array_merge( $issues, $structure_issues );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of permalink issues */
					__( 'Found %d permalink-related issues that may cause 404 errors.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Visit Settings > Permalinks and click "Save Changes" to flush rewrite rules. Ensure .htaccess is writable on Apache servers.', 'wpshadow' ),
					'kb_link'        => 'https://wpshadow.com/kb/permalink-404-errors?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				),
			);
		}

		return null;
	}

	/**
	 * Get URLs to test for accessibility.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
