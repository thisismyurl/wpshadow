<?php
/**
 * Theme Mobile Performance Diagnostic
 *
 * Checks if the active theme is optimized for mobile devices including
 * viewport meta tags, mobile-responsive stylesheets, and touch-friendly
 * navigation elements.
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
 * Theme Mobile Performance Diagnostic Class
 *
 * Validates mobile optimization features in the active theme.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Mobile_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-mobile-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Mobile Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates mobile optimization in active theme';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme        = wp_get_theme();
		$template_dir = get_template_directory();
		$issues       = array();

		// Check for viewport meta tag in header.php.
		$header_file = $template_dir . '/header.php';
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			if ( false === stripos( $header_content, 'viewport' ) ) {
				$issues[] = __( 'Missing viewport meta tag in header.php', 'wpshadow' );
			}
		}

		// Check if theme is mobile responsive (via theme support).
		if ( ! current_theme_supports( 'responsive-embeds' ) && ! current_theme_supports( 'custom-logo' ) ) {
			// Not definitive, but combined absence suggests old theme.
			$issues[] = __( 'Theme may lack modern responsive features', 'wpshadow' );
		}

		// Check for excessive stylesheet size (mobile performance).
		$stylesheet_file = get_stylesheet_directory() . '/style.css';
		if ( file_exists( $stylesheet_file ) ) {
			$size_kb = filesize( $stylesheet_file ) / 1024;
			if ( $size_kb > 500 ) {
				$issues[] = sprintf(
					/* translators: %s: stylesheet size in KB */
					__( 'Theme stylesheet is very large (%s KB)', 'wpshadow' ),
					number_format( $size_kb, 1 )
				);
			}
		}

		// Check for mobile menu script.
		$functions_file = $template_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$functions_content = file_get_contents( $functions_file );
			if ( false === stripos( $functions_content, 'mobile' ) && false === stripos( $functions_content, 'responsive' ) ) {
				$issues[] = __( 'No mobile-specific functionality detected in functions.php', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of mobile performance issues */
					__( 'Found %d mobile performance concerns in your theme.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'     => $issues,
					'theme_name' => $theme->get( 'Name' ),
					'theme_version' => $theme->get( 'Version' ),
				),
			);
		}

		return null;
	}
}
