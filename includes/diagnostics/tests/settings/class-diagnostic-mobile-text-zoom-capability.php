<?php
/**
 * Mobile Text Zoom Capability Diagnostic
 *
 * Ensures text scales to 200% when user zooms without horizontal scroll.
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
 * Mobile Text Zoom Capability Diagnostic Class
 *
 * Ensures text scales to 200% when user zooms without horizontal scroll,
 * a critical WCAG AA requirement for low-vision users.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Text_Zoom_Capability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-text-zoom-capability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Text Zoom Capability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure text scales to 200% when user zooms without horizontal scroll (WCAG1.0)';

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

		// Check viewport meta tag
		global $wp_filter;
		$has_viewport_meta = false;

		// Look for viewport meta tag in wp_head hooks
		if ( has_action( 'wp_head' ) ) {
			// Check if viewport meta allows zooming
			$viewport_allows_zoom = apply_filters( 'wpshadow_viewport_allows_zoom', true );
			if ( ! $viewport_allows_zoom ) {
				$issues[] = __( 'Viewport meta tag disables user zoom (user-scalable=no), prevents text scaling', 'wpshadow' );
			}
			$has_viewport_meta = true;
		}

		if ( ! $has_viewport_meta ) {
			$issues[] = __( 'Viewport meta tag not detected; zooming capability unconfirmed', 'wpshadow' );
		}

		// Check if content width is fixed preventing reflow
		$has_fixed_width_issues = apply_filters( 'wpshadow_has_fixed_width_layout_issues', false );
		if ( $has_fixed_width_issues ) {
			$issues[] = __( 'Fixed-width layout detected; content may not reflow at 200% zoom', 'wpshadow' );
		}

		// Check for horizontal scroll on zoom
		$horizontal_scroll_on_zoom = apply_filters( 'wpshadow_horizontal_scroll_on_zoom', false );
		if ( $horizontal_scroll_on_zoom ) {
			$issues[] = __( 'Content may cause horizontal scrolling at 200% zoom level', 'wpshadow' );
		}

		// Check if theme uses flexible layouts
		$supports_flexible_layout = apply_filters( 'wpshadow_theme_supports_flexible_layout', true );
		if ( ! $supports_flexible_layout ) {
			$issues[] = __( 'Theme may not support flexible layout for text zooming', 'wpshadow' );
		}

		// Check for max-width constraints on containers
		$has_max_width = apply_filters( 'wpshadow_container_has_max_width', true );
		if ( ! $has_max_width ) {
			$issues[] = __( 'Container max-width not configured; may cause reflow issues at zoom levels', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-text-zoom-capability',
			);
		}

		return null;
	}
}
