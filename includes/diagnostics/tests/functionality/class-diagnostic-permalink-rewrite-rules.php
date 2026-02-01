<?php
/**
 * Permalink Rewrite Rules Diagnostic
 *
 * Tests if rewrite rules are generated correctly and validates .htaccess on Apache.
 * Ensures permalinks are properly configured for SEO and functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1410
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Rewrite Rules Diagnostic Class
 *
 * Validates permalink structure and rewrite rule configuration.
 *
 * @since 1.26032.1410
 */
class Diagnostic_Permalink_Rewrite_Rules extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-rewrite-rules';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Rewrite Rules';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if rewrite rules are generated correctly. Validates .htaccess on Apache.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1410
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_rewrite;

		$issues = array();

		// Check if permalinks are enabled (not using default ?p=123 structure).
		if ( ! $wp_rewrite || ! $wp_rewrite->using_permalinks() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Permalinks are set to default (plain). Pretty permalinks are recommended for better SEO and user experience.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'details'      => array(
					'current_structure' => 'plain',
					/* translators: Recommendation text for enabling pretty permalinks with example permalink structures */
					'recommendation'    => __( 'Enable pretty permalinks in Settings > Permalinks. Recommended: /%postname%/ or /%category%/%postname%/', 'wpshadow' ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/permalink-rewrite-rules',
			);
		}

		// Check if rewrite rules are empty or not properly generated.
		$rules = $wp_rewrite->rewrite_rules();
		if ( empty( $rules ) || ! is_array( $rules ) ) {
			$issues[] = __( 'Rewrite rules are not generated. Permalinks may not work correctly.', 'wpshadow' );
		}

		// On Apache, validate .htaccess file.
		if ( self::is_apache() ) {
			$htaccess_path   = self::get_htaccess_path();
			$htaccess_issues = self::validate_htaccess( $htaccess_path );

			if ( ! empty( $htaccess_issues ) ) {
				$issues = array_merge( $issues, $htaccess_issues );
			}
		}

		// Check if rewrite rules are outdated (flush needed).
		if ( self::needs_rewrite_flush() ) {
			$issues[] = __( 'Rewrite rules may be outdated. Flush permalinks to regenerate rules.', 'wpshadow' );
		}

		// If issues found, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues found with permalinks */
					__( 'Found %d issue(s) with permalink rewrite rules.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'details'      => array(
					'issues'              => $issues,
					'permalink_structure' => get_option( 'permalink_structure', '' ),
					'server_software'     => self::get_server_software(),
					'recommendation'      => __( 'Fix .htaccess permissions, ensure mod_rewrite is enabled, and flush permalinks in Settings > Permalinks.', 'wpshadow' ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/permalink-rewrite-rules',
			);
		}

		return null;
	}

	/**
	 * Check if server is running Apache.
	 *
	 * @since  1.26032.1410
	 * @return bool True if Apache, false otherwise.
	 */
	private static function is_apache(): bool {
		$server_software = self::get_server_software();
		return false !== stripos( $server_software, 'apache' ) || false !== stripos( $server_software, 'litespeed' );
	}

	/**
	 * Get server software string.
	 *
	 * @since  1.26032.1410
	 * @return string Server software identifier.
	 */
	private static function get_server_software(): string {
		return isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
	}

	/**
	 * Get .htaccess file path.
	 *
	 * @since  1.26032.1410
	 * @return string Path to .htaccess file.
	 */
	private static function get_htaccess_path(): string {
		return ABSPATH . '.htaccess';
	}

	/**
	 * Validate .htaccess file and return issues.
	 *
	 * @since  1.26032.1410
	 * @param  string $htaccess_path Path to .htaccess file.
	 * @return array Array of issues found.
	 */
	private static function validate_htaccess( string $htaccess_path ): array {
		$issues = array();

		// Check if .htaccess exists.
		if ( ! file_exists( $htaccess_path ) ) {
			$issues[] = __( '.htaccess file does not exist. Create it or ensure server supports rewrite rules.', 'wpshadow' );
			return $issues;
		}

		// Check if .htaccess is readable.
		if ( ! is_readable( $htaccess_path ) ) {
			$issues[] = __( '.htaccess file is not readable. Check file permissions.', 'wpshadow' );
			return $issues;
		}

		// Read .htaccess content.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading local file, not remote URL
		$htaccess_content = file_get_contents( $htaccess_path );
		if ( false === $htaccess_content ) {
			$issues[] = __( 'Unable to read .htaccess file content.', 'wpshadow' );
			return $issues;
		}

		// Check if .htaccess contains WordPress rewrite rules.
		if ( false === stripos( $htaccess_content, '# BEGIN WordPress' ) ) {
			$issues[] = __( '.htaccess file does not contain WordPress rewrite rules. Permalinks may not work.', 'wpshadow' );
		}

		// Check for RewriteEngine directive.
		if ( false === stripos( $htaccess_content, 'RewriteEngine' ) ) {
			$issues[] = __( '.htaccess file is missing RewriteEngine directive. mod_rewrite may not be active.', 'wpshadow' );
		}

		// Check for RewriteBase directive (common issue).
		if ( false === stripos( $htaccess_content, 'RewriteBase' ) ) {
			$issues[] = __( '.htaccess file is missing RewriteBase directive. This may cause issues with subdirectory installations.', 'wpshadow' );
		}

		// Check if .htaccess is writable (needed for WordPress to update rules).
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Checking file permissions, not writing
		if ( ! is_writable( $htaccess_path ) ) {
			$issues[] = __( '.htaccess file is not writable. WordPress cannot update rewrite rules automatically.', 'wpshadow' );
		}

		return $issues;
	}

	/**
	 * Check if rewrite rules need to be flushed.
	 *
	 * This is a heuristic check based on common indicators.
	 *
	 * @since  1.26032.1410
	 * @return bool True if flush may be needed, false otherwise.
	 */
	private static function needs_rewrite_flush(): bool {
		global $wp_rewrite;

		// Get current permalink structure.
		$structure = get_option( 'permalink_structure', '' );

		// If no custom structure, no flush needed.
		if ( empty( $structure ) ) {
			return false;
		}

		// Check if rewrite rules are empty (definite flush needed).
		$rules = $wp_rewrite->rewrite_rules();
		if ( empty( $rules ) ) {
			return true;
		}

		// Check for common post type registration without flush.
		// This is a basic heuristic - in production, plugins would set a flag.
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		foreach ( $post_types as $post_type ) {
			// Check if custom post type has rewrite rules.
			if ( ! empty( $post_type->rewrite ) && is_array( $post_type->rewrite ) ) {
				$slug = isset( $post_type->rewrite['slug'] ) ? $post_type->rewrite['slug'] : $post_type->name;

				// Look for this slug in rewrite rules.
				$found = false;
				foreach ( $rules as $pattern => $rewrite ) {
					if ( false !== strpos( $pattern, $slug ) ) {
						$found = true;
						break;
					}
				}

				// If custom post type not found in rules, may need flush.
				if ( ! $found ) {
					// This is a soft indicator, not definitive.
					// In practice, this would need more sophisticated checks.
					continue;
				}
			}
		}

		return false;
	}
}
