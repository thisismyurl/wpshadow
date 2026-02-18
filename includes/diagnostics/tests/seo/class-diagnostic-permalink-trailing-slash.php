<?php
/**
 * Permalink Trailing Slash Diagnostic
 *
 * Tests trailing slash consistency in permalink structure and detects
 * potential redirect loops caused by inconsistent trailing slash handling.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.0903
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Trailing Slash Diagnostic Class
 *
 * Detects trailing slash inconsistencies in permalink structure that can
 * cause redirect loops, duplicate content issues, and SEO problems.
 *
 * WordPress allows configuring permalinks with or without trailing slashes,
 * but inconsistent handling can cause:
 * - 301 redirect loops (server redirects /page to /page/ and back)
 * - Duplicate content indexed by search engines
 * - Broken canonical URLs
 * - Poor user experience with unnecessary redirects
 *
 * @since 1.6032.0903
 */
class Diagnostic_Permalink_Trailing_Slash extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-trailing-slash';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Trailing Slash';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests trailing slash consistency and detects redirect loops';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * 1. Trailing slash consistency in permalink structure
	 * 2. Potential redirect loops from slash mismatches
	 * 3. Conflicting permalink and category base configurations
	 *
	 * @since  1.6032.0903
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_rewrite;

		// Skip check if not using pretty permalinks
		if ( ! $wp_rewrite || ! $wp_rewrite->using_permalinks() ) {
			return null;
		}

		$permalink_structure = get_option( 'permalink_structure' );
		$category_base       = get_option( 'category_base' );
		$tag_base            = get_option( 'tag_base' );

		// Return if permalink structure is not set
		if ( ! $permalink_structure ) {
			return null;
		}

		// Check if permalink structure has trailing slash
		$has_trailing_slash = substr( $permalink_structure, -1 ) === '/';

		// Check for redirect loop potential
		$redirect_loop_risk = self::detect_redirect_loop_risk( $permalink_structure, $category_base, $tag_base, $has_trailing_slash );

		if ( $redirect_loop_risk ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: detailed description of the trailing slash issue */
					__( 'Trailing slash inconsistency detected: %s This can cause redirect loops, duplicate content, and SEO issues.', 'wpshadow' ),
					$redirect_loop_risk['message']
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/permalink-trailing-slash',
				'details'      => array(
					'permalink_structure' => $permalink_structure,
					'has_trailing_slash'  => $has_trailing_slash ? 'Yes' : 'No',
					'category_base'       => $category_base ? $category_base : 'category',
					'tag_base'            => $tag_base ? $tag_base : 'tag',
					'issue_type'          => $redirect_loop_risk['type'],
					'recommendation'      => $redirect_loop_risk['recommendation'],
				),
			);
		}

		return null;
	}

	/**
	 * Detect potential redirect loop risks from trailing slash inconsistencies.
	 *
	 * @since  1.6032.0903
	 * @param  string $permalink_structure Permalink structure.
	 * @param  string $category_base       Category base.
	 * @param  string $tag_base            Tag base.
	 * @param  bool   $has_trailing_slash  Whether permalink has trailing slash.
	 * @return array|null Risk details if found, null otherwise.
	 */
	private static function detect_redirect_loop_risk( $permalink_structure, $category_base, $tag_base, $has_trailing_slash ) {
		// Check for mixed trailing slash configuration
		$issues = array();

		// If permalink has trailing slash, check if bases are consistent
		if ( $has_trailing_slash ) {
			// Category base should also have trailing slash for consistency
			if ( ! empty( $category_base ) ) {
				$cat_has_slash = substr( $category_base, -1 ) === '/';
				if ( ! $cat_has_slash ) {
					$issues[] = array(
						'type'           => 'category_base_mismatch',
						'message'        => __( 'Your permalink structure ends with a trailing slash, but your category base does not. This inconsistency can cause redirect loops.', 'wpshadow' ),
						'recommendation' => __( 'Update your category base in Settings > Permalinks to end with a trailing slash, or remove the trailing slash from your permalink structure.', 'wpshadow' ),
					);
				}
			}

			// Tag base should also have trailing slash for consistency
			if ( ! empty( $tag_base ) ) {
				$tag_has_slash = substr( $tag_base, -1 ) === '/';
				if ( ! $tag_has_slash ) {
					$issues[] = array(
						'type'           => 'tag_base_mismatch',
						'message'        => __( 'Your permalink structure ends with a trailing slash, but your tag base does not. This inconsistency can cause redirect loops.', 'wpshadow' ),
						'recommendation' => __( 'Update your tag base in Settings > Permalinks to end with a trailing slash, or remove the trailing slash from your permalink structure.', 'wpshadow' ),
					);
				}
			}
		}

		// Check for permalink structures that commonly cause issues
		$problematic_patterns = array(
			'/%postname%' => array(
				'type'           => 'missing_trailing_slash',
				'message'        => __( 'Your permalink structure uses /%postname% without a trailing slash. This can cause issues with pagination and archives.', 'wpshadow' ),
				'recommendation' => __( 'Consider using /%postname%/ (with trailing slash) for better consistency with WordPress core behavior.', 'wpshadow' ),
			),
		);

		foreach ( $problematic_patterns as $pattern => $issue ) {
			if ( $permalink_structure === $pattern ) {
				$issues[] = $issue;
			}
		}

		// Check for potential conflicts with .htaccess trailing slash rules
		if ( $has_trailing_slash ) {
			$htaccess_file = ABSPATH . '.htaccess';
			if ( file_exists( $htaccess_file ) && is_readable( $htaccess_file ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$htaccess_content = file_get_contents( $htaccess_file );
				// Check for custom trailing slash rules that might conflict
				if ( false !== $htaccess_content ) {
					if ( preg_match( '/RewriteRule.*\$\s+[^\s]+[^\/]\s+\[/i', $htaccess_content ) ) {
						$issues[] = array(
							'type'           => 'htaccess_conflict',
							'message'        => __( 'Your .htaccess file may contain custom rewrite rules that conflict with trailing slash handling.', 'wpshadow' ),
							'recommendation' => __( 'Review your .htaccess file and ensure custom rewrite rules do not interfere with WordPress trailing slash redirects.', 'wpshadow' ),
						);
					}
				}
			}
		}

		// Return the first issue found
		if ( ! empty( $issues ) ) {
			return $issues[0];
		}

		return null;
	}
}
