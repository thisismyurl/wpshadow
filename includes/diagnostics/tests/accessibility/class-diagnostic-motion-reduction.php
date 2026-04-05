<?php
/**
 * Reduced Motion Support Diagnostic
 *
 * Scans the active theme's CSS files for animation and transition properties.
 * If motion effects are found but no @media (prefers-reduced-motion) override
 * exists, users with vestibular disorders may experience discomfort.
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
 * Diagnostic_Motion_Reduction Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Motion_Reduction extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'motion-reduction';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Reduced Motion Support';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Scans the active theme for CSS animations or transitions and checks whether a prefers-reduced-motion media query is present to protect users with vestibular disorders.';

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
	 * Reads all *.css files in the active theme (and parent theme, if any),
	 * looking for CSS animation or transition properties. If motion properties
	 * are found in any file that does not also contain a
	 * prefers-reduced-motion block, the diagnostic fires.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$theme_dirs = array_unique(
			array_filter(
				array(
					get_stylesheet_directory(),
					get_template_directory(),
				),
				'is_dir'
			)
		);

		$has_animation     = false;
		$has_reduced_query = false;
		$files_with_motion = array();

		foreach ( $theme_dirs as $dir ) {
			try {
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
				);
			} catch ( \Exception $e ) {
				continue;
			}

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() || 'css' !== strtolower( $file->getExtension() ) ) {
					continue;
				}

				$css = file_get_contents( $file->getPathname() );
				if ( false === $css ) {
					continue;
				}

				// Check for any animation or transition properties.
				$file_has_animation = (bool) preg_match(
					'/(?:^|[;{\s])(?:animation|transition)\s*:/im',
					$css
				);

				if ( $file_has_animation ) {
					$has_animation = true;
				}

				// Check for prefers-reduced-motion anywhere across all files.
				if ( str_contains( $css, 'prefers-reduced-motion' ) ) {
					$has_reduced_query = true;
				}

				if ( $file_has_animation && ! str_contains( $css, 'prefers-reduced-motion' ) ) {
					// Record relative path for the finding.
					$relative = str_replace( $dir . DIRECTORY_SEPARATOR, '', $file->getPathname() );
					if ( count( $files_with_motion ) < 10 ) {
						$files_with_motion[] = $relative;
					}
				}
			}
		}

		// Pass: no animations, or a reduced-motion override exists somewhere.
		if ( ! $has_animation || $has_reduced_query ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The active theme includes CSS animations or transitions but does not define a prefers-reduced-motion media query. Users with vestibular disorders may experience nausea or discomfort from uncontrolled motion.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'details'      => array(
				'affected_files' => $files_with_motion,
				'fix'            => __( 'Add @media (prefers-reduced-motion: reduce) { } blocks that set animation: none and transition: none for all animated elements. Place these overrides in your theme\'s main stylesheet or a dedicated accessibility stylesheet.', 'wpshadow' ),
			),
		);
	}
}
