<?php
/**
 * Viewport Meta Diagnostic
 *
 * The <meta name="viewport"> tag controls how browsers scale content on
 * mobile devices. When it includes user-scalable=no or maximum-scale values
 * less than 2, browser pinch-to-zoom is disabled, violating WCAG 1.4.4
 * (Resize Text) for users who depend on zoom to read content.
 *
 * @package WPShadow
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
 * Diagnostic_Viewport_Meta Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Viewport_Meta extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'viewport-meta';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Viewport Meta';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that the viewport meta tag does not disable user zoom (user-scalable=no or maximum-scale below 2), which would prevent users from enlarging text to meet WCAG 1.4.4.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans the active theme's header.php and functions.php (plus any PHP
	 * files inside theme include directories) for a viewport meta tag.
	 * If one is found, the content attribute is examined for user-scalable=no
	 * or a maximum-scale value less than 2.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$stylesheet_dir = get_stylesheet_directory();
		$template_dir   = get_template_directory();

		// Gather candidate files to scan.
		$candidates = array(
			$stylesheet_dir . '/header.php',
			$template_dir   . '/header.php',
			$stylesheet_dir . '/functions.php',
			$template_dir   . '/functions.php',
		);

		// Add any PHP files in common includes directories.
		$inc_dirs = array(
			$stylesheet_dir . '/inc',
			$template_dir   . '/inc',
			$stylesheet_dir . '/includes',
			$template_dir   . '/includes',
		);

		foreach ( $inc_dirs as $inc_dir ) {
			if ( is_dir( $inc_dir ) ) {
				$files = glob( $inc_dir . '/*.php' );
				if ( $files ) {
					$candidates = array_merge( $candidates, $files );
				}
			}
		}

		$candidates = array_unique( $candidates );

		foreach ( $candidates as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			$content = file_get_contents( $file );
			if ( false === $content || ! stripos( $content, 'viewport' ) ) {
				continue;
			}

			// Extract the viewport meta tag content attribute value.
			if ( ! preg_match( '/<meta[^>]+name=["\']viewport["\'][^>]+content=["\']([^"\']+)["\']|<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']viewport["\']/i', $content, $m ) ) {
				continue;
			}

			$viewport_content = ! empty( $m[1] ) ? $m[1] : $m[2];

			// Check for user-scalable=no or user-scalable=0.
			if ( preg_match( '/user-scalable\s*=\s*(no|0)/i', $viewport_content ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'The viewport meta tag uses user-scalable=no, which disables browser zoom. Users who need to enlarge text to read it cannot do so, violating WCAG 1.4.4 (Resize Text).', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 65,
					'kb_link'      => 'https://wpshadow.com/kb/viewport-meta?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'viewport_content' => $viewport_content,
						'issue'            => 'user-scalable=no',
						'fix'              => __( 'Remove user-scalable=no from the viewport meta tag. The recommended value is: width=device-width, initial-scale=1. This allows users to pinch-to-zoom without breaking responsive layouts.', 'wpshadow' ),
					),
				);
			}

			// Check for maximum-scale less than 2.
			if ( preg_match( '/maximum-scale\s*=\s*([\d.]+)/i', $viewport_content, $scale_match ) ) {
				$max_scale = (float) $scale_match[1];
				if ( $max_scale < 2.0 ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => sprintf(
							/* translators: %s: the maximum-scale value found */
							__( 'The viewport meta tag sets maximum-scale=%s, which prevents users from zooming the page beyond that level. A value of at least 2 is required to meet WCAG 1.4.4 (Resize Text).', 'wpshadow' ),
							esc_html( $scale_match[1] )
						),
						'severity'     => 'high',
						'threat_level' => 60,
						'kb_link'      => 'https://wpshadow.com/kb/viewport-meta?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
						'details'      => array(
							'viewport_content' => $viewport_content,
							'max_scale_found'  => $max_scale,
							'fix'              => __( 'Remove the maximum-scale limit from your viewport meta tag, or raise it to at least 5 to allow sufficient text enlargement. The recommended tag is: <meta name="viewport" content="width=device-width, initial-scale=1">.', 'wpshadow' ),
						),
					);
				}
			}
		}

		return null;
	}
}
