<?php
/**
 * Link Distinction Diagnostic
 *
 * WCAG 1.4.1 requires that links within body text are distinguishable from
 * surrounding non-link text by more than color alone. This diagnostic scans
 * the active theme's CSS for rules that remove text underlines from anchor
 * elements without providing an alternative visual indicator such as a
 * border-bottom, font-weight change, or background highlight.
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
 * Diagnostic_Underlines_Or_Link_Distinction Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Underlines_Or_Link_Distinction extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'underlines-or-link-distinction';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Link Distinction';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that links in body text remain distinguishable from surrounding text by more than color alone, as required by WCAG 1.4.1.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * CSS properties that serve as acceptable non-color link distinctions.
	 */
	private const DISTINCTION_PROPS = array(
		'border-bottom',
		'border-top',
		'box-shadow',
		'font-weight',
		'font-style',
		'background',
		'background-color',
		'outline',
		'text-decoration-style',
		'text-decoration-line',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Scans each CSS file in the active (and parent) theme for rules that
	 * target bare anchor elements and explicitly remove text-decoration.
	 * In each such rule block the method also checks whether an alternative
	 * visual indicator is declared alongside the removal. A finding is only
	 * raised when removal with no alternative is found.
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

		$violations = array();

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
				if ( false === $css || ! str_contains( $css, 'text-decoration' ) ) {
					continue;
				}

				// Find all rule blocks whose selector targets bare anchor elements.
				// We look for: "a", "a:link", "a:visited", "a:hover", etc.
				$pattern = '/(?:^|[\s,}])(?:a|a:[a-z-]+)\s*\{([^}]+)\}/ms';
				if ( ! preg_match_all( $pattern, $css, $matches, PREG_SET_ORDER ) ) {
					continue;
				}

				foreach ( $matches as $match ) {
					$block = $match[1];

					// Only care about blocks that strip underlines.
					if ( ! preg_match( '/text-decoration\s*:\s*none/i', $block ) ) {
						continue;
					}

					// Check whether an alternative distinction is present.
					$has_alternative = false;
					foreach ( self::DISTINCTION_PROPS as $prop ) {
						if ( preg_match( '/' . preg_quote( $prop, '/' ) . '\s*:/i', $block ) ) {
							$has_alternative = true;
							break;
						}
					}

					if ( ! $has_alternative && count( $violations ) < 10 ) {
						$relative     = str_replace( $dir . DIRECTORY_SEPARATOR, '', $file->getPathname() );
						$violations[] = $relative;
					}
				}
			}
		}

		if ( empty( $violations ) ) {
			return null;
		}

		$violations = array_unique( $violations );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The active theme removes the underline from anchor elements in one or more CSS files without providing an alternative visual distinction. Users who cannot distinguish links by color alone will not be able to identify them (WCAG 1.4.1).', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/link-distinction?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'affected_files' => array_values( $violations ),
				'fix'            => __( 'Either restore text-decoration: underline on body-copy links, or add a clearly visible alternative such as border-bottom or font-weight: bold to the same rule. The :hover and :focus states should also maintain or enhance the distinction.', 'wpshadow' ),
			),
		);
	}
}
