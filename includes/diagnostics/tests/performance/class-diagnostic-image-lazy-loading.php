<?php
/**
 * Image Lazy Loading Diagnostic
 *
 * Verifies that WordPress native lazy loading is active and has not been
 * disabled by a theme or plugin, which would cause all images to be requested
 * on initial page load regardless of viewport position.
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
 * Diagnostic_Image_Lazy_Loading Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Image_Lazy_Loading extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-lazy-loading';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Lazy Loading';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that WordPress native image lazy loading has not been disabled, ensuring off-screen images are deferred to improve initial page load performance.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * PHP patterns that indicate lazy loading has been disabled.
	 *
	 * @var string[]
	 */
	private const DISABLE_PATTERNS = array(
		"add_filter( 'wp_lazy_loading_enabled', '__return_false'",
		"add_filter('wp_lazy_loading_enabled', '__return_false'",
		'wp_lazy_loading_enabled.*__return_false',
		"'wp_lazy_loading_enabled'.*false",
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Lazy loading has been on by default since WordPress 5.5. This check
	 * scans theme PHP files for patterns that remove or override the filter
	 * to return false, which would negate the native lazy load behaviour.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// WordPress < 5.5 does not have native lazy loading.
		if ( version_compare( get_bloginfo( 'version' ), '5.5', '<' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your WordPress version does not support native image lazy loading, which was introduced in WordPress 5.5. All images are loaded on page request regardless of viewport visibility, increasing initial load time and bandwidth usage.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'kb_link'      => 'https://wpshadow.com/kb/image-lazy-loading?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'wp_version' => get_bloginfo( 'version' ),
					'fix'        => __( 'Update WordPress to at least version 5.5 to gain native lazy loading support. Keeping WordPress current also delivers security patches and performance improvements.', 'wpshadow' ),
				),
			);
		}

		// Scan theme files for code that disables lazy loading.
		$template_dirs = array_filter(
			array_unique( array( get_stylesheet_directory(), get_template_directory() ) ),
			'is_dir'
		);

		$offending_file = null;

		foreach ( $template_dirs as $dir ) {
			try {
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
				);
				foreach ( $iterator as $file ) {
					if ( 'php' !== strtolower( $file->getExtension() ) ) {
						continue;
					}
					$contents = file_get_contents( $file->getPathname() );
					if ( false === $contents ) {
						continue;
					}
					foreach ( self::DISABLE_PATTERNS as $pattern ) {
						if ( 1 === preg_match( '/' . preg_quote( $pattern, '/' ) . '/i', $contents ) ||
						     1 === preg_match( '/' . $pattern . '/i', $contents ) ) {
							$offending_file = str_replace( $dir, '', $file->getPathname() );
							break 3;
						}
					}
				}
			} catch ( \Exception $e ) {
				continue;
			}
		}

		if ( null === $offending_file ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			/* translators: %s: theme file path */
			'description'  => sprintf(
				__( 'Native image lazy loading is being disabled in your theme (%s). All images load immediately regardless of scroll position, increasing initial page weight and slowing First Contentful Paint.', 'wpshadow' ),
				ltrim( $offending_file, '/\\' )
			),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => 'https://wpshadow.com/kb/image-lazy-loading?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'offending_file' => ltrim( $offending_file, '/\\' ),
				'fix'            => __( 'Remove the add_filter( \'wp_lazy_loading_enabled\', \'__return_false\' ) call from your theme. WordPress 5.5+ adds loading="lazy" to images automatically. If the LCP image is being deferred, use the wp_lazy_loading_enabled filter with context awareness to exclude only above-the-fold images rather than disabling globally.', 'wpshadow' ),
			),
		);
	}
}
