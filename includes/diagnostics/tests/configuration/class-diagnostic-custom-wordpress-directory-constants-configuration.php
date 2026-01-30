<?php
/**
 * Custom WordPress Directory Constants Configuration Diagnostic
 *
 * Validates custom WordPress directory constants if site uses non-standard structure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom WordPress Directory Constants Configuration Class
 *
 * Tests custom directory constants.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Custom_Wordpress_Directory_Constants_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-wordpress-directory-constants-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom WordPress Directory Constants Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates custom WordPress directory constants if site uses non-standard structure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$constants_check = self::check_directory_constants();
		
		if ( $constants_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $constants_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-wordpress-directory-constants-configuration',
				'meta'         => array(
					'uses_custom_dirs' => $constants_check['uses_custom_dirs'],
				),
			);
		}

		return null;
	}

	/**
	 * Check directory constants configuration.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_directory_constants() {
		$check = array(
			'has_issues'       => false,
			'issues'           => array(),
			'uses_custom_dirs' => false,
		);

		// Check if WP_CONTENT_DIR is custom.
		if ( defined( 'WP_CONTENT_DIR' ) ) {
			$default_content_dir = ABSPATH . 'wp-content';
			
			if ( WP_CONTENT_DIR !== $default_content_dir ) {
				$check['uses_custom_dirs'] = true;

				// Check if WP_CONTENT_URL is properly set.
				if ( defined( 'WP_CONTENT_URL' ) ) {
					// Check for hardcoded domain in URL.
					$content_url = WP_CONTENT_URL;
					
					if ( false !== strpos( $content_url, '://' ) ) {
						$parsed = wp_parse_url( $content_url );
						$site_parsed = wp_parse_url( get_option( 'siteurl' ) );
						
						if ( isset( $parsed['host'] ) && isset( $site_parsed['host'] ) && 
						     $parsed['host'] !== $site_parsed['host'] ) {
							$check['has_issues'] = true;
							$check['issues'][] = __( 'WP_CONTENT_URL domain does not match site URL (breaks on migration)', 'wpshadow' );
						}
					}
				}
			}
		}

		// Check WP_HOME and WP_SITEURL consistency.
		if ( defined( 'WP_HOME' ) && defined( 'WP_SITEURL' ) ) {
			$home = rtrim( WP_HOME, '/' );
			$siteurl = rtrim( WP_SITEURL, '/' );
			
			// Check for HTTP/HTTPS mismatch.
			$home_scheme = wp_parse_url( $home, PHP_URL_SCHEME );
			$siteurl_scheme = wp_parse_url( $siteurl, PHP_URL_SCHEME );
			
			if ( $home_scheme !== $siteurl_scheme ) {
				$check['has_issues'] = true;
				$check['issues'][] = __( 'WP_HOME and WP_SITEURL use different protocols (HTTP/HTTPS mismatch)', 'wpshadow' );
			}
		}

		// Check for hardcoded HTTP in HTTPS site.
		if ( is_ssl() ) {
			$constants_to_check = array( 'WP_HOME', 'WP_SITEURL', 'WP_CONTENT_URL' );
			
			foreach ( $constants_to_check as $constant ) {
				if ( defined( $constant ) && str_starts_with( constant( $constant ), 'http://' ) ) {
					$check['has_issues'] = true;
					$check['issues'][] = sprintf(
						/* translators: %s: constant name */
						__( '%s uses HTTP on HTTPS site (mixed content warnings)', 'wpshadow' ),
						$constant
					);
				}
			}
		}

		return $check;
	}
}
