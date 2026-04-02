<?php
/**
 * Theme Author Support Status Diagnostic
 *
 * Validates that the active theme has an identifiable author and
 * checks if the theme is actively maintained and supported.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Author Support Status Diagnostic Class
 *
 * Checks theme author and support status.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Author_Support_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-author-support-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Author Support Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme author information and support';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get active theme.
		$theme = wp_get_theme();

		// Check for theme name.
		$theme_name = $theme->get( 'Name' );
		if ( empty( $theme_name ) ) {
			$issues[] = __( 'Theme has no name declared', 'wpshadow' );
		}

		// Check for theme author.
		$theme_author = $theme->get( 'Author' );
		if ( empty( $theme_author ) ) {
			$issues[] = __( 'Theme has no author information', 'wpshadow' );
		} else {
			// Check if it's a generic/suspicious author.
			if ( in_array( strtolower( $theme_author ), array( 'unknown', 'anonymous', 'admin', 'administrator' ), true ) ) {
				$issues[] = sprintf(
					/* translators: %s: author name */
					__( 'Theme has suspicious author: "%s"', 'wpshadow' ),
					$theme_author
				);
			}
		}

		// Check for theme author URL.
		$theme_author_url = $theme->get( 'AuthorURI' );
		if ( empty( $theme_author_url ) ) {
			$issues[] = __( 'Theme has no author URL (cannot verify or contact author)', 'wpshadow' );
		} else {
			// Validate URL format.
			if ( ! filter_var( $theme_author_url, FILTER_VALIDATE_URL ) ) {
				$issues[] = sprintf(
					/* translators: %s: URL */
					__( 'Theme author URL is invalid: %s', 'wpshadow' ),
					$theme_author_url
				);
			}
		}

		// Check for theme description.
		$theme_description = $theme->get( 'Description' );
		if ( empty( $theme_description ) ) {
			$issues[] = __( 'Theme has no description', 'wpshadow' );
		}

		// Check for theme version.
		$theme_version = $theme->get( 'Version' );
		if ( empty( $theme_version ) ) {
			$issues[] = __( 'Theme version not declared', 'wpshadow' );
		}

		// Check if theme is from WordPress.org.
		$theme_source = 'unknown';
		$theme_uri    = $theme->get( 'ThemeURI' );

		if ( ! empty( $theme_uri ) ) {
			if ( false !== strpos( $theme_uri, 'wordpress.org' ) ) {
				$theme_source = 'wordpress.org';
			} elseif ( false !== strpos( $theme_uri, 'github.com' ) ) {
				$theme_source = 'github';
			} elseif ( false !== strpos( $theme_uri, 'themeforest' ) ) {
				$theme_source = 'themeforest';
			} else {
				$theme_source = 'external';
			}
		}

		// Check theme last update.
		$stylesheet_path = get_stylesheet_directory();
		if ( is_dir( $stylesheet_path ) ) {
			$mod_time = filemtime( $stylesheet_path );
			$days_ago = ( time() - $mod_time ) / DAY_IN_SECONDS;

			if ( $days_ago > 365 * 2 ) { // 2 years.
				$issues[] = sprintf(
					/* translators: %d: number of days */
					__( 'Theme has not been updated in %d days (possibly abandoned)', 'wpshadow' ),
					absint( $days_ago )
				);
			}
		}

		// Check if theme has a changelog or update information.
		$readme_files = array( 'readme.txt', 'README.md', 'CHANGELOG.md', 'CHANGELOG.txt' );
		$has_changelog = false;

		foreach ( $readme_files as $readme ) {
			if ( file_exists( $stylesheet_path . '/' . $readme ) ) {
				$has_changelog = true;
				break;
			}
		}

		if ( ! $has_changelog && $theme_source === 'external' ) {
			$issues[] = __( 'Theme has no visible changelog or update documentation', 'wpshadow' );
		}

		// Check theme license.
		$theme_license = $theme->get( 'License' );
		if ( empty( $theme_license ) ) {
			$issues[] = __( 'Theme license not declared', 'wpshadow' );
		}

		// Check theme license URI.
		$theme_license_uri = $theme->get( 'LicenseURI' );
		if ( empty( $theme_license_uri ) ) {
			$issues[] = __( 'Theme license URI not declared', 'wpshadow' );
		}

		// Check for child theme without parent.
		$template_directory = get_template_directory();
		$stylesheet_directory = get_stylesheet_directory();

		if ( $template_directory !== $stylesheet_directory ) {
			// This is a child theme - check if parent exists.
			if ( ! is_dir( $template_directory ) ) {
				$issues[] = __( 'Child theme has missing parent theme', 'wpshadow' );
			}
		}

		// Check for theme domains.
		$theme_domains = $theme->get( 'DomainPath' );
		if ( ! empty( $theme_domains ) ) {
			if ( ! is_dir( $stylesheet_directory . '/' . $theme_domains ) ) {
				$issues[] = sprintf(
					/* translators: %s: domain path */
					__( 'Theme declares domain path that does not exist: %s', 'wpshadow' ),
					$theme_domains
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of theme author/support issues */
					__( 'Found %d theme author and support status issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'        => $issues,
					'theme_name'    => $theme_name,
					'theme_author'  => $theme_author,
					'theme_source'  => $theme_source,
					'recommendation' => __( 'Ensure theme has complete metadata: author, version, description, license. Keep theme updated from verified sources.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
