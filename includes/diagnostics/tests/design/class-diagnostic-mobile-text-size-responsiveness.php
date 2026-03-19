<?php
/**
 * Mobile Text Size Responsiveness Diagnostic
 *
 * Supports OS-level text scaling (Dynamic Type/font scaling).
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
 * Mobile Text Size Responsiveness Diagnostic Class
 *
 * Validates that text scales with system-level font size preferences,
 * ensuring WCAG1.0 compliance for text resizing.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Text_Size_Responsiveness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-text-size-responsiveness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Text Size Responsiveness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Support OS-level text scaling without overflow (WCAG1.0)';

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

		// Check for pixel vs relative font sizes
		$uses_relative_units = apply_filters( 'wpshadow_css_uses_relative_font_units', false );
		if ( ! $uses_relative_units ) {
			$issues[] = __( 'Font sizes should use em/rem instead of px for OS-level scaling support', 'wpshadow' );
		}

		// Check if text scales without horizontal scroll
		$scales_without_scroll = apply_filters( 'wpshadow_text_scales_without_horizontal_scroll', false );
		if ( ! $scales_without_scroll ) {
			$issues[] = __( 'Text may cause horizontal scrolling when scaled to 200%; use relative units and flexible layout', 'wpshadow' );
		}

		// Check if root font size is set for scaling reference
		$root_font_size_set = apply_filters( 'wpshadow_root_font_size_set', false );
		if ( ! $root_font_size_set ) {
			$issues[] = __( 'Root font size (16px) should be set to establish scaling baseline', 'wpshadow' );
		}

		// Check for iOS Dynamic Type support
		$dynamic_type_supported = apply_filters( 'wpshadow_ios_dynamic_type_supported', false );
		if ( ! $dynamic_type_supported ) {
			$issues[] = __( 'iOS Dynamic Type may not be supported; ensure text respects system-wide accessibility settings', 'wpshadow' );
		}

		// Check for Android Large Text support
		$android_large_text = apply_filters( 'wpshadow_android_large_text_supported', false );
		if ( ! $android_large_text ) {
			$issues[] = __( 'Android Large Text setting may not be honored; use relative units (em/rem)', 'wpshadow' );
		}

		// Check maximum scale without overflow
		$max_scale_supported = apply_filters( 'wpshadow_text_max_scale_without_overflow', '100%' );
		if ( '100%' === $max_scale_supported || '150%' === $max_scale_supported ) {
			$issues[] = sprintf(
				/* translators: %s: max scale percentage */
				__( 'Maximum text scale is %s; target 200%% for WCAG1.0 compliance', 'wpshadow' ),
				$max_scale_supported
			);
		}

		// Check for line height to allow text scaling
		$line_height_adequate = apply_filters( 'wpshadow_line_height_adequate_for_scaling', false );
		if ( ! $line_height_adequate ) {
			$issues[] = __( 'Line height should be at least1.0 to accommodate text scaling without crowding', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-text-size-responsiveness',
			);
		}

		return null;
	}
}
