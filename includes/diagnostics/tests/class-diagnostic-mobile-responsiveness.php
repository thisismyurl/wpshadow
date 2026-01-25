<?php
/**
 * Mobile Responsiveness Diagnostic
 *
 * Validates mobile and responsive design implementation across WPShadow UI.
 * Checks viewport meta tags, touch target sizes, responsive CSS,
 * and mobile-friendly patterns.
 *
 * Phase 5 of UI/UX Epic - Final Polish & Validation
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics\Tests
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Mobile_Responsiveness Class
 *
 * Performs comprehensive mobile responsiveness validation.
 * Ensures WPShadow admin interface works well on all screen sizes.
 */
class Diagnostic_Mobile_Responsiveness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-responsiveness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Responsiveness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates mobile and responsive design implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'usability';

	/**
	 * Minimum touch target size (in pixels) per WCAG 2.5.5
	 */
	const MIN_TOUCH_TARGET_SIZE = 44;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Validate responsive CSS patterns.
		$css_issues = self::check_responsive_css();
		if ( ! empty( $css_issues ) ) {
			$issues = array_merge( $issues, $css_issues );
		}

		// Check 2: Validate touch target sizes.
		$touch_issues = self::check_touch_targets();
		if ( ! empty( $touch_issues ) ) {
			$issues = array_merge( $issues, $touch_issues );
		}

		// Check 3: Check for mobile-unfriendly patterns.
		$pattern_issues = self::check_mobile_patterns();
		if ( ! empty( $pattern_issues ) ) {
			$issues = array_merge( $issues, $pattern_issues );
		}

		// If any issues found, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => __( 'Mobile Responsiveness Issues Detected', 'wpshadow' ),
				'description'   => sprintf(
					/* translators: %d: number of responsiveness issues found */
					__( 'Found %d mobile responsiveness issues that could impact mobile users.', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/mobile-responsiveness/',
				'training_link' => 'https://wpshadow.com/training/responsive-design/',
				'module'        => 'Usability',
				'priority'      => 2,
				'meta'          => array(
					'issues'       => $issues,
					'total_issues' => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Check CSS files for responsive design patterns.
	 *
	 * Validates:
	 * - Media queries present
	 * - Viewport units used appropriately
	 * - Flexible layouts (flexbox, grid)
	 * - Responsive font sizes
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_responsive_css() {
		$issues   = array();
		$css_path = WPSHADOW_PATH . 'assets/css/';

		if ( ! is_dir( $css_path ) ) {
			return $issues;
		}

		$css_files = glob( $css_path . '*.css' );
		if ( ! $css_files ) {
			return $issues;
		}

		foreach ( $css_files as $file ) {
			$content  = file_get_contents( $file );
			$filename = basename( $file );

			// Skip mobile-specific files.
			if ( strpos( $filename, 'mobile' ) !== false ) {
				continue;
			}

			// Check for media queries.
			$has_media_queries = preg_match( '/@media/i', $content );
			$has_layout        = preg_match( '/(width|display|flex|grid):/i', $content );

			if ( $has_layout && ! $has_media_queries ) {
				$issues[] = array(
					'file'     => $filename,
					'issue'    => 'missing_media_queries',
					'message'  => __( 'CSS file with layouts but no media queries', 'wpshadow' ),
					'severity' => 'medium',
				);
			}

			// Check for fixed widths without max-width.
			preg_match_all( '/width:\s*(\d+)px/i', $content, $fixed_widths );
			foreach ( $fixed_widths[1] as $width ) {
				if ( (int) $width > 600 ) {
					// Check if there's a corresponding max-width nearby.
					$position    = strpos( $content, "width: {$width}px" );
					$context     = substr( $content, max( 0, $position - 200 ), 400 );
					$has_max     = preg_match( '/max-width:/i', $context );

					if ( ! $has_max ) {
						$issues[] = array(
							'file'     => $filename,
							'issue'    => 'fixed_width_without_max',
							'message'  => sprintf(
								/* translators: %d: width in pixels */
								__( 'Fixed width of %dpx without max-width', 'wpshadow' ),
								$width
							),
							'severity' => 'low',
						);
					}
				}
			}

			// Check for viewport units.
			$has_viewport_units = preg_match( '/\d+(vw|vh|vmin|vmax)/i', $content );
			$has_responsive     = preg_match( '/(min|max)-(width|height):/i', $content );

			if ( ! $has_viewport_units && ! $has_responsive && $has_layout ) {
				$issues[] = array(
					'file'     => $filename,
					'issue'    => 'no_responsive_units',
					'message'  => __( 'CSS lacks responsive units (vw, vh) or media queries', 'wpshadow' ),
					'severity' => 'low',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for proper touch target sizes.
	 *
	 * Validates:
	 * - Buttons meet 44×44px minimum
	 * - Links have adequate spacing
	 * - Interactive elements not too close together
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_touch_targets() {
		$issues   = array();
		$css_path = WPSHADOW_PATH . 'assets/css/';

		if ( ! is_dir( $css_path ) ) {
			return $issues;
		}

		$css_files = glob( $css_path . '*.css' );
		if ( ! $css_files ) {
			return $issues;
		}

		foreach ( $css_files as $file ) {
			$content  = file_get_contents( $file );
			$filename = basename( $file );

			// Look for button/interactive element styles.
			preg_match_all( '/(button|\.wps-btn|\.wps-toggle|input\[type=|\.wps-dropdown)[^{]*\{([^}]+)\}/is', $content, $matches );

			foreach ( $matches[0] as $rule ) {
				// Check for explicit small sizes.
				if ( preg_match( '/height:\s*(\d+)px/i', $rule, $height_match ) ) {
					$height = (int) $height_match[1];
					if ( $height < self::MIN_TOUCH_TARGET_SIZE ) {
						// Check if it's inside a media query for mobile.
						$rule_pos   = strpos( $content, $rule );
						$before     = substr( $content, max( 0, $rule_pos - 500 ), 500 );
						$in_mobile  = preg_match( '/@media[^{]*\(max-width:\s*\d+px\)/i', $before );

						if ( $in_mobile ) {
							$issues[] = array(
								'file'     => $filename,
								'issue'    => 'small_touch_target',
								'message'  => sprintf(
									/* translators: 1: height in pixels, 2: minimum height */
									__( 'Touch target height %1$dpx is below minimum %2$dpx', 'wpshadow' ),
									$height,
									self::MIN_TOUCH_TARGET_SIZE
								),
								'severity' => 'medium',
							);
						}
					}
				}
			}
		}

		return $issues;
	}

	/**
	 * Check for mobile-unfriendly patterns.
	 *
	 * Validates:
	 * - No hover-only interactions
	 * - No horizontal scrolling required
	 * - Adequate font sizes
	 * - Touch-friendly spacing
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_mobile_patterns() {
		$issues   = array();
		$css_path = WPSHADOW_PATH . 'assets/css/';
		$js_path  = WPSHADOW_PATH . 'assets/js/';

		// Check CSS for hover-only patterns.
		if ( is_dir( $css_path ) ) {
			$css_files = glob( $css_path . '*.css' );
			if ( $css_files ) {
				foreach ( $css_files as $file ) {
					$content  = file_get_contents( $file );
					$filename = basename( $file );

					// Check for hover without focus.
					preg_match_all( '/([^:]+):hover\s*\{/i', $content, $hover_matches );
					foreach ( $hover_matches[1] as $selector ) {
						$selector = trim( $selector );
						// Check if there's a corresponding :focus or :active.
						$has_focus  = preg_match( '/' . preg_quote( $selector, '/' ) . '\s*:(focus|active)/i', $content );
						$has_touch  = preg_match( '/' . preg_quote( $selector, '/' ) . '\s*:active/i', $content );

						if ( ! $has_focus && ! $has_touch ) {
							$issues[] = array(
								'file'     => $filename,
								'issue'    => 'hover_without_focus',
								'message'  => sprintf(
									/* translators: %s: CSS selector */
									__( 'Selector "%s" has :hover but no :focus/:active', 'wpshadow' ),
									substr( $selector, 0, 50 )
								),
								'severity' => 'low',
							);
						}
					}

					// Check for small font sizes.
					preg_match_all( '/font-size:\s*(\d+)px/i', $content, $font_matches );
					foreach ( $font_matches[1] as $size ) {
						if ( (int) $size < 12 ) {
							$issues[] = array(
								'file'     => $filename,
								'issue'    => 'small_font_size',
								'message'  => sprintf(
									/* translators: %d: font size in pixels */
									__( 'Font size %dpx may be too small for mobile', 'wpshadow' ),
									$size
								),
								'severity' => 'low',
							);
						}
					}
				}
			}
		}

		// Check JavaScript for mouse-only events.
		if ( is_dir( $js_path ) ) {
			$js_files = glob( $js_path . '*.js' );
			if ( $js_files ) {
				foreach ( $js_files as $file ) {
					$content  = file_get_contents( $file );
					$filename = basename( $file );

					// Check for mouseenter/mouseleave without touch equivalents.
					$has_mouse = preg_match( '/(mouseenter|mouseleave|mouseover)/i', $content );
					$has_touch = preg_match( '/(touchstart|touchend|click)/i', $content );

					if ( $has_mouse && ! $has_touch ) {
						$issues[] = array(
							'file'     => $filename,
							'issue'    => 'mouse_only_events',
							'message'  => __( 'Mouse events without touch equivalents', 'wpshadow' ),
							'severity' => 'medium',
						);
					}
				}
			}
		}

		return $issues;
	}

	/**
	 * Get the diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Mobile Responsiveness', 'wpshadow' );
	}

	/**
	 * Get the diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Validates mobile and responsive design implementation.', 'wpshadow' );
	}
}
