<?php
/**
 * Skip Link Present Diagnostic
 *
 * A skip link lets keyboard and screen-reader users jump past repeated
 * navigation blocks directly to the main content. Without one, every page
 * load forces keyboard users to tab through the entire nav before reaching
 * the content they actually came for (WCAG 2.4.1 - Bypass Blocks).
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Skip_Link_Present Class
 *
 * @since 0.6095
 */
class Diagnostic_Skip_Link_Present extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'skip-link-present';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Skip Link Present';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that the active theme includes a skip link pointing to the main content area so keyboard users can bypass repeated navigation.';

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
	 * Skip-link anchor patterns commonly used by accessible themes.
	 * Keys are fragment IDs; values are display labels for the finding.
	 */
	private const SKIP_TARGETS = array(
		'#main'            => '#main',
		'#content'         => '#content',
		'#primary'         => '#primary',
		'#site-content'    => '#site-content',
		'#main-content'    => '#main-content',
		'#page-content'    => '#page-content',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the active theme's header.php (child or parent) and looks for
	 * an anchor element whose href points to a main-content landmark.
	 * Themes that skip-link via a filter or inline block templates also pass
	 * if the pattern is found anywhere in index.php or front-page.php.
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// Build list of template files to scan, child theme first.
		$stylesheet_dir = get_stylesheet_directory();
		$template_dir   = get_template_directory();

		$candidates = array(
			$stylesheet_dir . '/header.php',
			$template_dir   . '/header.php',
			$stylesheet_dir . '/index.php',
			$template_dir   . '/index.php',
			$stylesheet_dir . '/front-page.php',
			$template_dir   . '/front-page.php',
		);

		// Also include parts directories for block themes.
		$parts_dirs = array(
			$stylesheet_dir . '/parts',
			$template_dir   . '/parts',
			$stylesheet_dir . '/templates',
			$template_dir   . '/templates',
		);

		foreach ( $parts_dirs as $parts_dir ) {
			if ( is_dir( $parts_dir ) ) {
				$php_files = glob( $parts_dir . '/*.php' );
				$html_files = glob( $parts_dir . '/*.html' );
				if ( $php_files ) {
					$candidates = array_merge( $candidates, $php_files );
				}
				if ( $html_files ) {
					$candidates = array_merge( $candidates, $html_files );
				}
			}
		}

		$candidates = array_unique( $candidates );

		foreach ( $candidates as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			$content = file_get_contents( $file );
			if ( false === $content ) {
				continue;
			}

			// Check for any anchor pointing to a known main-content target.
			foreach ( array_keys( self::SKIP_TARGETS ) as $target ) {
				if ( str_contains( $content, 'href="' . $target . '"' )
					|| str_contains( $content, "href='" . $target . "'" ) ) {
					return null;
				}
			}

			// Also accept generic "skip" class or text patterns.
			if ( preg_match( '/class=["\'][^"\']*skip[^"\']*["\'].*?href|href.*?class=["\'][^"\']*skip/i', $content )
				|| preg_match( '/<a[^>]+href=["\']#[^"\']+["\'][^>]*>.*?skip/is', $content ) ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No skip-to-content link was detected in the active theme\'s header templates. Keyboard users must tab through the entire navigation on every page before reaching the main content.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 60,
			'details'      => array(
				'fix' => __( 'Add <a class="skip-link screen-reader-text" href="#main">Skip to content</a> as the very first element inside the <body> tag in your theme\'s header.php. Then ensure the main content wrapper has id="main". Style the link to appear on focus using CSS.', 'wpshadow' ),
			),
		);
	}
}
