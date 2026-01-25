<?php
/**
 * Analysis Helpers for WPShadow
 *
 * Domain-specific analysis functions for mobile and accessibility.
 *
 * @package WPShadow
 * @subpackage Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Analyze HTML for mobile friendliness.
 *
 * @param string $html HTML content.
 * @return array Mobile friendliness checks.
 */
function wpshadow_analyze_mobile_html( $html ) {
	$checks = array();

	$viewport_present = (bool) preg_match( '/<meta[^>]+name=["\\\']viewport["\\\'][^>]*>/i', $html );
	$viewport_content = '';

	if ( $viewport_present && preg_match( '/<meta[^>]+name=["\\\']viewport["\\\'][^>]*content=["\\\']([^"\\\']+)["\\\'][^>]*>/i', $html, $match ) ) {
		$viewport_content = strtolower( $match[1] );
	}

	$has_device_width  = ( $viewport_content && strpos( $viewport_content, 'width=device-width' ) !== false );
	$has_initial_scale = ( $viewport_content && strpos( $viewport_content, 'initial-scale' ) !== false );
	$zoom_disabled     = ( $viewport_content && ( strpos( $viewport_content, 'user-scalable=no' ) !== false || preg_match( '/maximum-scale\s*=\s*1(\.0)?/i', $viewport_content ) ) );

	$checks[] = array(
		'id'      => 'viewport',
		'label'   => __( 'Viewport meta tag', 'wpshadow' ),
		'status'  => $viewport_present ? 'pass' : 'fail',
		'details' => $viewport_present
			? __( 'Viewport tag detected for responsive layouts.', 'wpshadow' )
			: __( 'Missing viewport meta tag; mobile browsers may render the desktop layout.', 'wpshadow' ),
	);

	$checks[] = array(
		'id'      => 'device-width',
		'label'   => __( 'Viewport width set to device width', 'wpshadow' ),
		'status'  => $has_device_width ? 'pass' : 'warn',
		'details' => $has_device_width
			? __( 'width=device-width is set.', 'wpshadow' )
			: __( 'Add width=device-width to the viewport content for proper scaling.', 'wpshadow' ),
	);

	$checks[] = array(
		'id'      => 'initial-scale',
		'label'   => __( 'Initial scale defined', 'wpshadow' ),
		'status'  => $has_initial_scale ? 'pass' : 'warn',
		'details' => $has_initial_scale
			? __( 'initial-scale is specified.', 'wpshadow' )
			: __( 'Set an initial-scale (typically 1.0) to avoid unexpected zoom.', 'wpshadow' ),
	);

	$checks[] = array(
		'id'      => 'zoom',
		'label'   => __( 'Zoom not disabled', 'wpshadow' ),
		'status'  => $zoom_disabled ? 'warn' : 'pass',
		'details' => $zoom_disabled
			? __( 'Zoom appears disabled via user-scalable or maximum-scale; allow zoom for accessibility.', 'wpshadow' )
			: __( 'Zoom is allowed for users who need it.', 'wpshadow' ),
	);

	// Look for very small font sizes in inline styles or stylesheets.
	$small_font_hits = 0;
	if ( preg_match_all( '/font-size\s*:\s*([0-9]+(?:\.[0-9]+)?)px/i', $html, $font_matches ) ) {
		foreach ( $font_matches[1] as $size ) {
			if ( (float) $size < 14.0 ) {
				++$small_font_hits;
			}
		}
	}

	$checks[] = array(
		'id'      => 'font-size',
		'label'   => __( 'Readable font sizes', 'wpshadow' ),
		'status'  => $small_font_hits > 0 ? 'warn' : 'pass',
		'details' => $small_font_hits > 0
			? sprintf( __( 'Found %d font declarations under 14px; consider increasing for readability.', 'wpshadow' ), (int) $small_font_hits )
			: __( 'No obvious undersized font declarations detected.', 'wpshadow' ),
	);

	// Check for rigid widths that may cause horizontal scroll on phones.
	$wide_tables = false;
	if ( preg_match( '/<table[^>]+width=["\\\']?(\d{3,})/i', $html, $table_match ) ) {
		$wide_tables = ( (int) $table_match[1] >= 960 );
	}

	$fixed_min_width = false;
	if ( preg_match( '/min-width\s*:\s*(\d{3,})px/i', $html, $min_width_match ) ) {
		$fixed_min_width = ( (int) $min_width_match[1] >= 960 );
	}

	$layout_rigid = $wide_tables || $fixed_min_width;

	$checks[] = array(
		'id'      => 'layout-flex',
		'label'   => __( 'Flexible layout widths', 'wpshadow' ),
		'status'  => $layout_rigid ? 'warn' : 'pass',
		'details' => $layout_rigid
			? __( 'Detected wide fixed widths that may force horizontal scrolling on small screens.', 'wpshadow' )
			: __( 'No obvious fixed-width layouts detected.', 'wpshadow' ),
	);

	return $checks;
}

/**
 * Run mobile friendliness scan programmatically for tools/workflows.
 *
 * @return array Findings array.
 */
function wpshadow_run_mobile_friendliness() {
	$response = wp_remote_get(
		home_url(),
		array(
			'timeout' => 10,
			'headers' => array( 'User-Agent' => 'WPShadow-Mobile-Check' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return array();
	}

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return array();
	}

	return wpshadow_analyze_mobile_html( $body );
}

/**
 * Analyze HTML for accessibility issues.
 *
 * @param string $html HTML content.
 * @return array Accessibility checks.
 */
function wpshadow_analyze_a11y_html( $html ) {
	$checks = array();

	// List for ARIA attributes
	$aria_attrs = array( 'aria-label', 'aria-labelledby', 'aria-describedby', 'aria-live' );
	$aria_count = 0;
	foreach ( $aria_attrs as $attr ) {
		$aria_count += substr_count( $html, $attr );
	}

	$checks[] = array(
		'id'      => 'aria',
		'label'   => __( 'ARIA attributes', 'wpshadow' ),
		'status'  => $aria_count > 0 ? 'pass' : 'warn',
		'details' => $aria_count > 0
			? sprintf( __( 'Found %d ARIA attributes for enhanced accessibility.', 'wpshadow' ), $aria_count )
			: __( 'Consider adding ARIA attributes to improve screen reader support.', 'wpshadow' ),
	);

	// Check for alt attributes on images
	$img_count = substr_count( $html, '<img' );
	$alt_count = substr_count( $html, 'alt=' );

	$checks[] = array(
		'id'      => 'alt-text',
		'label'   => __( 'Alt text on images', 'wpshadow' ),
		'status'  => $alt_count >= $img_count * 0.8 ? 'pass' : 'warn',
		'details' => sprintf(
			__( 'Found %1$d images, %2$d with alt text.', 'wpshadow' ),
			$img_count,
			$alt_count
		),
	);

	// Check for form labels
	$form_count  = substr_count( $html, '<form' );
	$label_count = substr_count( $html, '<label' );

	$checks[] = array(
		'id'      => 'form-labels',
		'label'   => __( 'Form labels', 'wpshadow' ),
		'status'  => $label_count > 0 ? 'pass' : 'warn',
		'details' => $label_count > 0
			? __( 'Form labels detected for better accessibility.', 'wpshadow' )
			: __( 'Consider adding labels to all form fields.', 'wpshadow' ),
	);

	return $checks;
}
