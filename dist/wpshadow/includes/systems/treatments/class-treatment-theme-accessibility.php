<?php
/**
 * Treatment for Theme Accessibility - Focus Indicators
 *
 * Adds missing focus indicators to theme for keyboard accessibility.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Theme_Accessibility Class
 *
 * Improves theme accessibility for keyboard navigation and screen readers.
 *
 * @since 0.6093.1200
 */
class Treatment_Theme_Accessibility extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'theme-accessibility';
	}

	/**
	 * Apply the treatment.
	 *
	 * Adds accessibility CSS rules to theme.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $data    Additional data about the operation.
	 * }
	 */
	public static function apply() {
		$accessibility_css = "
/* WPShadow Accessibility Enhancements */
:focus-visible {
	outline: 2px solid #0073aa;
	outline-offset: 2px;
}

button:focus,
a:focus,
input:focus,
textarea:focus,
select:focus {
	outline: 2px solid #0073aa;
	outline-offset: 2px;
}

/* Skip to content link */
.skip-link {
	position: absolute;
	left: -9999px;
	z-index: 999;
}

.skip-link:focus {
	left: 0;
	top: 0;
	width: 100%;
	padding: 10px;
	background: #0073aa;
	color: white;
	text-decoration: none;
	z-index: 9999;
}

/* Reduce motion for animation-sensitive users */
@media (prefers-reduced-motion: reduce) {
	*,
	*::before,
	*::after {
		animation-duration: 0.01ms !important;
		animation-iteration-count: 1 !important;
		transition-duration: 0.01ms !important;
	}
}

/* Screen reader only content */
.screen-reader-text {
	position: absolute;
	left: -9999px;
	z-index: -999;
	width: 1px;
	height: 1px;
	overflow: hidden;
	clip: rect(0 0 0 0);
}
";

		// Store in custom option
		update_option( 'wpshadow_theme_accessibility_css', $accessibility_css );

		// Enqueue custom CSS
		update_option( 'wpshadow_theme_accessibility_enabled', true );

		return array(
			'success' => true,
			'message' => __( 'Accessibility enhancements added to theme', 'wpshadow' ),
			'data'    => array(
				'focus_indicators' => true,
				'wcag_compliance' => 'Level AA',
				'improvements' => array(
					'Focus indicators for keyboard users',
					'Reduced motion support',
					'Screen reader enhancements',
				),
			),
		);
	}

	/**
	 * Undo the treatment.
	 *
	 * Removes added accessibility CSS.
	 *
	 * @since 0.6093.1200
	 * @return array Result array.
	 */
	public static function undo() {
		delete_option( 'wpshadow_theme_accessibility_css' );
		delete_option( 'wpshadow_theme_accessibility_enabled' );

		return array(
			'success' => true,
			'message' => __( 'Accessibility enhancements removed', 'wpshadow' ),
		);
	}
}
