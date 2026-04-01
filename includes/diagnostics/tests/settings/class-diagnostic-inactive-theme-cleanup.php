<?php
/**
 * Inactive Theme Cleanup Diagnostic
 *
 * Validates that unused and inactive themes are properly cleaned up
 * to reduce attack surface and storage bloat.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inactive Theme Cleanup Diagnostic Class
 *
 * Checks for inactive theme cleanup.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Inactive_Theme_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inactive-theme-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inactive Theme Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates inactive theme cleanup and removal';

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

		// Get active theme.
		$active_theme = wp_get_theme();
		$active_slug  = $active_theme->get_stylesheet();

		// Check if this is a child theme.
		$parent_theme = $active_theme->get_template();
		$required_themes = array( $active_slug );

		if ( $active_slug !== $parent_theme ) {
			$required_themes[] = $parent_theme;
		}

		// Get all installed themes.
		$all_themes = wp_get_themes();

		if ( empty( $all_themes ) ) {
			$issues[] = __( 'No themes installed (critical system error)', 'wpshadow' );
		}

		// Identify inactive themes.
		$inactive_themes = array();

		foreach ( $all_themes as $theme_slug => $theme ) {
			if ( ! in_array( $theme_slug, $required_themes, true ) ) {
				$inactive_themes[ $theme_slug ] = $theme;
			}
		}

		// Check for excessive inactive themes.
		$threshold = 5; // Warning if more than 5 inactive themes.

		if ( count( $inactive_themes ) > $threshold ) {
			$issues[] = sprintf(
				/* translators: 1: number of inactive themes, 2: threshold */
				__( '%1$d inactive themes installed (exceeds recommended maximum of %2$d)', 'wpshadow' ),
				count( $inactive_themes ),
				$threshold
			);
		}

		// Analyze inactive themes for security risks.
		$inactive_with_issues = array();

		foreach ( $inactive_themes as $theme_slug => $theme ) {
			$theme_issues = array();

			// Check if theme is outdated.
			$theme_version = $theme->get( 'Version' );
			$theme_uri     = $theme->get( 'ThemeURI' );

			// Check if theme can be updated.
			$theme_update_transient = get_transient( 'update_themes' );

			if ( ! empty( $theme_update_transient ) && isset( $theme_update_transient->response[ $theme_slug ] ) ) {
				$theme_issues[] = 'Outdated - updates available';
			}

			// Check if theme is from WordPress.org.
			$is_wordpress_org = false;

			if ( ! empty( $theme_uri ) ) {
				if ( false !== strpos( $theme_uri, 'wordpress.org' ) ) {
					$is_wordpress_org = true;
				}
			}

			if ( ! $is_wordpress_org ) {
				// External/commercial theme - harder to maintain.
				$theme_issues[] = 'External theme - harder to maintain updates';
			}

			// Check for theme code issues.
			$theme_dir = $theme->get_stylesheet_directory();

			if ( is_dir( $theme_dir ) ) {
				$php_files = glob( $theme_dir . '/*.php' );

				// Check for deprecated functions.
				foreach ( $php_files as $php_file ) {
					$content = file_get_contents( $php_file );

					// Check for PHP 7+ deprecated functions.
					if ( preg_match( '/mysql_|eregi\(|ereg\(|split\(|deprecated/i', $content ) ) {
						$theme_issues[] = 'Uses deprecated PHP functions';
						break;
					}
				}
			}

			if ( ! empty( $theme_issues ) ) {
				$inactive_with_issues[ $theme->get( 'Name' ) ] = $theme_issues;
			}
		}

		if ( ! empty( $inactive_with_issues ) ) {
			foreach ( $inactive_with_issues as $theme_name => $theme_issues ) {
				$issues[] = sprintf(
					/* translators: 1: theme name, 2: issue list */
					__( 'Inactive theme "%1$s": %2$s', 'wpshadow' ),
					$theme_name,
					implode( ', ', $theme_issues )
				);
			}
		}

		// Check storage used by inactive themes.
		$theme_root = get_theme_root();

		$total_inactive_size = 0;
		$largest_inactive    = array();

		foreach ( $inactive_themes as $theme_slug => $theme ) {
			$theme_dir = $theme->get_stylesheet_directory();

			if ( is_dir( $theme_dir ) ) {
				// Calculate directory size.
				$size = self::get_directory_size( $theme_dir );
				$total_inactive_size += $size;

				if ( $size > 10 * 1024 * 1024 ) { // 10MB.
					$largest_inactive[ $theme->get( 'Name' ) ] = $size;
				}
			}
		}

		// Sort by size.
		arsort( $largest_inactive );

		if ( ! empty( $largest_inactive ) ) {
			foreach ( array_slice( $largest_inactive, 0, 5 ) as $theme_name => $size ) {
				$issues[] = sprintf(
					/* translators: 1: theme name, 2: size in MB */
					__( 'Large inactive theme "%1$s": %2$sMB of storage', 'wpshadow' ),
					$theme_name,
					number_format( $size / 1024 / 1024, 1 )
				);
			}
		}

		if ( count( $inactive_themes ) > $threshold ) {
			$issues[] = sprintf(
				/* translators: 1: total size in MB, 2: number of themes */
				__( 'Inactive themes using %1$sMB total (%2$d themes can be safely removed)', 'wpshadow' ),
				number_format( $total_inactive_size / 1024 / 1024, 1 ),
				count( $inactive_themes )
			);
		}

		// Check for WordPress default themes (usually safe to keep one).
		$default_themes = array();

		foreach ( $inactive_themes as $theme_slug => $theme ) {
			if ( preg_match( '/^twenty/', $theme_slug ) ) {
				$default_themes[ $theme_slug ] = $theme;
			}
		}

		// Recommend which themes to remove.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Found %d inactive theme cleanup issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'issues'             => $issues,
					'total_themes'       => count( $all_themes ),
					'inactive_count'     => count( $inactive_themes ),
					'total_inactive_size' => number_format( $total_inactive_size / 1024 / 1024, 1 ) . 'MB',
					'recommendation'     => __( 'Keep active theme + 1 fallback (Twenty*). Remove outdated and unused themes. Keep themes updated or delete them.', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Calculate directory size recursively.
	 *
	 * @since 0.6093.1200
	 * @param  string $dir Directory path.
	 * @return int Total directory size in bytes.
	 */
	private static function get_directory_size( $dir ) {
		$size = 0;

		if ( is_dir( $dir ) ) {
			$files = scandir( $dir );

			foreach ( $files as $file ) {
				if ( '.' !== $file && '..' !== $file ) {
					$path = $dir . '/' . $file;

					if ( is_dir( $path ) ) {
						$size += self::get_directory_size( $path );
					} elseif ( is_file( $path ) ) {
						$size += filesize( $path );
					}
				}
			}
		}

		return $size;
	}
}
