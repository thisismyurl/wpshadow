<?php
/**
 * Theme Installation Source Verification Diagnostic
 *
 * Verifies that themes are installed from legitimate sources (WordPress.org or verified)
 *
 * @since   1.26032.1021
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Theme_Installation_Source Class
 *
 * Checks if themes are from verified/legitimate sources.
 *
 * @since 1.26032.1021
 */
class Diagnostic_Theme_Installation_Source extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-installation-source';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Installation Source';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies themes are from legitimate sources';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.26032.1021
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$active_theme_issue = self::check_active_theme();
		$suspicious_themes = self::check_all_themes();

		if ( $active_theme_issue ) {
			$issues[] = $active_theme_issue;
		}

		if ( ! empty( $suspicious_themes ) ) {
			foreach ( $suspicious_themes as $theme_issue ) {
				$issues[] = $theme_issue;
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		// If multiple issues, report high severity
		$severity = count( $issues ) > 1 ? 'high' : 'medium';
		$threat_level = count( $issues ) > 1 ? 65 : 45;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of suspicious themes */
				_n(
					'Found %d theme with suspicious installation source',
					'Found %d themes with suspicious installation sources',
					count( $issues ),
					'wpshadow'
				),
				count( $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/verify-theme-source',
			'details'      => array(
				'suspicious_themes' => $issues,
				'active_theme'      => wp_get_theme()->get_stylesheet(),
			),
		);
	}

	/**
	 * Check active theme source
	 *
	 * @since  1.26032.1021
	 * @return array|null
	 */
	private static function check_active_theme(): ?array {
		$theme = wp_get_theme();
		$stylesheet = $theme->get_stylesheet();

		// Check if theme is from WordPress.org
		if ( self::is_wordpress_org_theme( $stylesheet ) ) {
			return null;
		}

		// Check if theme has valid license/metadata
		if ( ! self::has_valid_metadata( $theme ) ) {
			return array(
				'theme'     => $theme->get( 'Name' ),
				'slug'      => $stylesheet,
				'reason'    => __( 'Active theme missing WordPress.org metadata', 'wpshadow' ),
				'severity'  => 'high',
				'is_active' => true,
			);
		}

		return null;
	}

	/**
	 * Check all themes for suspicious sources
	 *
	 * @since  1.26032.1021
	 * @return array
	 */
	private static function check_all_themes(): array {
		$issues = array();
		$themes = wp_get_themes();

		foreach ( $themes as $stylesheet => $theme ) {
			// Skip default WP themes
			if ( in_array( $stylesheet, array( 'twentytwenty', 'twentytwentyone', 'twentytwentytwo', 'twentytwentythree', 'twentytwentyfour' ), true ) ) {
				continue;
			}

			// Skip active theme (already checked)
			if ( $stylesheet === wp_get_theme()->get_stylesheet() ) {
				continue;
			}

			// Check if from WordPress.org
			if ( self::is_wordpress_org_theme( $stylesheet ) ) {
				continue;
			}

			// Check for suspicious indicators
			if ( self::has_suspicious_indicators( $theme ) ) {
				$issues[] = array(
					'theme'     => $theme->get( 'Name' ),
					'slug'      => $stylesheet,
					'reason'    => __( 'Theme lacks identification/verification markers', 'wpshadow' ),
					'severity'  => 'medium',
					'is_active' => false,
				);
			}
		}

		return $issues;
	}

	/**
	 * Check if theme is from WordPress.org
	 *
	 * @since  1.26032.1021
	 * @param  string $stylesheet Theme stylesheet.
	 * @return bool
	 */
	private static function is_wordpress_org_theme( string $stylesheet ): bool {
		// Try to detect via theme directory location
		$theme_path = get_theme_root( $stylesheet ) . '/' . $stylesheet;

		// Check if there's a readme.txt (WordPress.org requirement)
		$readme_file = $theme_path . '/readme.txt';
		if ( file_exists( $readme_file ) ) {
			return true;
		}

		// Check theme header for common WordPress.org indicators
		$theme = wp_get_theme( $stylesheet );
		$author_uri = $theme->get( 'AuthorURI' );

		// Common WordPress.org theme author URIs
		if ( ! empty( $author_uri ) ) {
			$wordpress_patterns = array(
				'wordpress.org',
				'wporg-themes',
				'wptavern.com',
			);

			foreach ( $wordpress_patterns as $pattern ) {
				if ( stripos( $author_uri, $pattern ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if theme has valid metadata
	 *
	 * @since  1.26032.1021
	 * @param  \WP_Theme $theme Theme object.
	 * @return bool
	 */
	private static function has_valid_metadata( $theme ): bool {
		$required_fields = array(
			'Name'        => $theme->get( 'Name' ),
			'Description' => $theme->get( 'Description' ),
			'Author'      => $theme->get( 'Author' ),
			'Version'     => $theme->get( 'Version' ),
		);

		foreach ( $required_fields as $field => $value ) {
			if ( empty( $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check for suspicious indicators
	 *
	 * @since  1.26032.1021
	 * @param  \WP_Theme $theme Theme object.
	 * @return bool
	 */
	private static function has_suspicious_indicators( $theme ): bool {
		// Incomplete metadata
		if ( empty( $theme->get( 'Name' ) ) || empty( $theme->get( 'Author' ) ) ) {
			return true;
		}

		// Suspicious author URIs
		$author_uri = $theme->get( 'AuthorURI' );
		if ( ! empty( $author_uri ) ) {
			$suspicious_patterns = array(
				'.ru',
				'.cn',
				'bit.ly',
				'tinyurl',
				'shorte.st',
				// Add more patterns as needed
			);

			foreach ( $suspicious_patterns as $pattern ) {
				if ( stripos( $author_uri, $pattern ) !== false ) {
					return true;
				}
			}
		}

		// Check for obfuscated code
		$stylesheet = $theme->get_stylesheet_directory() . '/style.css';
		if ( file_exists( $stylesheet ) ) {
			$content = file_get_contents( $stylesheet );
			if ( false !== $content ) {
				// Look for base64 or hex encoding (obfuscation)
				if ( preg_match( '/base64_decode|eval\(|assert\(/i', $content ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
