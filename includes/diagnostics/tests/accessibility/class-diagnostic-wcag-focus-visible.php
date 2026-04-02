<?php
/**
 * WCAG 2.4.7 Focus Visible Diagnostic
 *
 * Validates that keyboard focus indicators are visible.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Focus Visible Diagnostic Class
 *
 * Checks for visible keyboard focus indicators (WCAG 2.4.7 Level AA).
 *
 * @since 1.6093.1200
 */
class Diagnostic_WCAG_Focus_Visible extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-focus-visible';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Focus Visible (WCAG 2.4.7)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that keyboard focus indicators are visible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check theme CSS for focus outline removal.
		$theme_css = get_template_directory() . '/style.css';
		$css_files = array( $theme_css );

		// Also check common additional CSS files.
		$additional_files = array(
			get_template_directory() . '/assets/css/main.css',
			get_template_directory() . '/css/style.css',
			get_template_directory() . '/dist/style.css',
		);

		foreach ( $additional_files as $file ) {
			if ( file_exists( $file ) ) {
				$css_files[] = $file;
			}
		}

		$outline_none_found = false;
		$has_focus_styles   = false;

		foreach ( $css_files as $css_file ) {
			if ( ! file_exists( $css_file ) ) {
				continue;
			}

			$content = file_get_contents( $css_file );

			// Check for outline:none without replacement.
			if ( preg_match( '/:focus[^{]*{[^}]*outline\s*:\s*none/i', $content ) ) {
				$outline_none_found = true;

				// Check if there's a replacement focus style nearby.
				if ( preg_match( '/:focus[^{]*{[^}]*(?:border|box-shadow|background)[^}]+}/i', $content ) ) {
					$has_focus_styles = true;
				}
			}

			// Check for focus-visible support (modern approach).
			if ( strpos( $content, ':focus-visible' ) !== false ) {
				$has_focus_styles = true;
			}
		}

		if ( $outline_none_found && ! $has_focus_styles ) {
			$issues[] = __( 'Theme CSS removes focus outline (outline:none) without providing alternative focus indicators', 'wpshadow' );
		}

		// Check for accessibility-ready tag.
		$theme = wp_get_theme();
		$tags  = $theme->get( 'Tags' );

		if ( is_array( $tags ) && ! in_array( 'accessibility-ready', $tags, true ) ) {
			$issues[] = __( 'Theme is not tagged as "accessibility-ready" which suggests focus styles may not be optimized', 'wpshadow' );
		}

		// Check if there are any focus styles at all.
		$has_any_focus = false;
		foreach ( $css_files as $css_file ) {
			if ( ! file_exists( $css_file ) ) {
				continue;
			}
			$content = file_get_contents( $css_file );
			if ( preg_match( '/:focus/i', $content ) ) {
				$has_any_focus = true;
				break;
			}
		}

		if ( ! $has_any_focus ) {
			$issues[] = __( 'No :focus styles found in theme CSS. Interactive elements need visible focus indicators', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Keyboard focus indicators are like a highlighter that shows where you are on the page. About 16% of users have motor disabilities that make using a mouse difficult, so they navigate with Tab and Enter keys. Without visible focus indicators, they can\'t see which button or link is currently selected—like trying to use a TV remote with your eyes closed. Adding focus styles helps everyone navigate more confidently.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-focus-visible',
			);
		}

		return null;
	}
}
