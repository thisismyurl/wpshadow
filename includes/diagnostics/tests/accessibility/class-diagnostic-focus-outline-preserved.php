<?php
/**
 * Focus Outline Preserved Diagnostic
 *
 * Scans the active theme's CSS files for rules that suppress the browser's
 * default focus outline on interactive elements without providing a visible
 * replacement, which breaks keyboard navigation for WCAG 2.4.7.
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
 * Diagnostic_Focus_Outline_Preserved Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Focus_Outline_Preserved extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'focus-outline-preserved';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Focus Outline Preserved';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Scans the active theme\'s CSS files for rules that remove the browser focus outline on interactive elements, which prevents keyboard users from knowing which element has focus.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads every .css file in the active (and child) theme directory and
	 * looks for CSS rules that set `outline: none` or `outline: 0` on a
	 * selector that includes `:focus`. Ignores occurrences where the same
	 * rule block also sets `box-shadow` or `outline-offset` as an explicit
	 * replacement indicator.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$theme          = wp_get_theme();
		$theme_dir      = get_theme_root() . '/' . $theme->get_stylesheet();
		$parent_dir     = $theme->parent() ? get_theme_root() . '/' . $theme->get_template() : null;

		$dirs_to_scan = array_filter( array( $theme_dir, $parent_dir ), 'is_dir' );

		if ( empty( $dirs_to_scan ) ) {
			return null;
		}

		$violations = array();

		foreach ( $dirs_to_scan as $dir ) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( 'css' !== strtolower( $file->getExtension() ) ) {
					continue;
				}

				$path    = $file->getPathname();
				$content = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( false === $content ) {
					continue;
				}

				// Find CSS rule blocks that mention :focus and contain outline removal.
				// Regex: capture selector-block pairs where :focus appears in selector
				// and outline:none/0 appears in the block without a replacement.
				$block_pattern = '/([^{}]*:focus[^{}]*)\{([^{}]*)\}/i';
				if ( ! preg_match_all( $block_pattern, $content, $matches, PREG_SET_ORDER ) ) {
					continue;
				}

				foreach ( $matches as $match ) {
					$selector   = trim( $match[1] );
					$block_body = $match[2];

					// Check for outline suppression.
					if ( ! preg_match( '/outline\s*:\s*(?:none|0)\b/i', $block_body ) ) {
						continue;
					}

					// Ignore if a visible replacement is provided in the same block.
					if ( preg_match( '/(?:box-shadow|outline-offset|border)\s*:/i', $block_body ) ) {
						continue;
					}

					$rel_path = ltrim( str_replace( get_theme_root(), '', $path ), '/' );

					$violations[] = array(
						'file'     => $rel_path,
						'selector' => $selector,
					);

					if ( count( $violations ) >= 10 ) {
						break 2;
					}
				}
			}
		}

		if ( empty( $violations ) ) {
			return null;
		}

		$count       = count( $violations );
		$theme_name  = $theme->get( 'Name' );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of violations, 2: theme name */
				_n(
					'%1$d CSS rule in the "%2$s" theme removes the focus outline without providing a visible replacement. Keyboard-only users will not be able to see which element is active.',
					'%1$d CSS rules in the "%2$s" theme remove the focus outline without providing visible replacements. Keyboard-only users will not be able to track focus across these elements.',
					$count,
					'wpshadow'
				),
				$count,
				esc_html( $theme_name )
			),
			'severity'     => 'high',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/focus-outline-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'affected_count' => $count,
				'violations'     => $violations,
				'theme'          => $theme_name,
				'fix'            => __( 'In each flagged CSS rule, replace `outline: none` with a visible focus style, for example `outline: 2px solid currentColor; outline-offset: 2px;` or an equivalent `box-shadow` alternative.', 'wpshadow' ),
			),
		);
	}
}
