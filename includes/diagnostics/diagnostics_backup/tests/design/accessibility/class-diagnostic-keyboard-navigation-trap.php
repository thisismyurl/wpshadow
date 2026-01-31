<?php
/**
 * Keyboard Navigation Trap Detection Diagnostic
 *
 * Detects modals, dropdowns, and interactive elements that trap keyboard focus,
 * violating WCAG 2.1 Level A success criterion 2.1.2.
 *
 * Focus traps prevent keyboard users from navigating away from interactive
 * components, creating a critical accessibility barrier.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since      1.6028.2054
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Keyboard_Navigation_Trap Class
 *
 * Scans theme and plugin JavaScript for modal/dropdown implementations
 * that may trap keyboard focus without escape mechanisms.
 *
 * @since 1.6028.2054
 */
class Diagnostic_Keyboard_Navigation_Trap extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'keyboard-navigation-trap';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Navigation Trap Detection';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects interactive elements that trap keyboard focus';

	/**
	 * Diagnostic family/category
	 *
	 * @var string
	 */
	protected static $family = 'accessibility-frontend';

	/**
	 * Run the keyboard trap diagnostic check.
	 *
	 * Analyzes theme and plugins for modal/dropdown implementations
	 * that may lack proper keyboard escape handlers.
	 *
	 * @since  1.6028.2054
	 * @return array|null Finding array if keyboard traps detected, null if compliant.
	 */
	public static function check() {
		$trap_analysis = self::analyze_potential_traps();

		if ( empty( $trap_analysis['risks'] ) ) {
			return null; // No obvious keyboard traps detected.
		}

		$risk_count = count( $trap_analysis['risks'] );
		$severity   = $risk_count > 3 ? 'high' : 'medium';

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of potential traps */
				_n(
					'Found %d potential keyboard focus trap. Interactive elements must allow keyboard users to navigate away.',
					'Found %d potential keyboard focus traps. Interactive elements must allow keyboard users to navigate away.',
					$risk_count,
					'wpshadow'
				),
				$risk_count
			),
			'severity'     => $severity,
			'threat_level' => $risk_count > 3 ? 75 : 60,
			'auto_fixable' => false,
			'solution'     => array(
				'free'     => array(
					'heading'     => __( 'Implement Keyboard Escape Handlers', 'wpshadow' ),
					'description' => __( 'Add event listeners for Escape key and proper focus management.', 'wpshadow' ),
					'steps'       => array(
						__( 'Modal: Add Escape key listener to close modal', 'wpshadow' ),
						__( 'document.addEventListener("keydown", (e) => { if (e.key === "Escape") closeModal(); })', 'wpshadow' ),
						__( 'Trap focus within modal: cycle Tab between first and last focusable elements', 'wpshadow' ),
						__( 'Return focus to trigger element on modal close', 'wpshadow' ),
						__( 'Dropdown: Allow Escape to close without submitting form', 'wpshadow' ),
						__( 'Test: Navigate with Tab and Escape keys only (no mouse)', 'wpshadow' ),
					),
				),
				'premium'  => array(
					'heading'     => __( 'Focus Management Library', 'wpshadow' ),
					'description' => __( 'Use focus-trap library (npm: focus-trap) for automatic focus management in modals and dialogs.', 'wpshadow' ),
				),
				'advanced' => array(
					'heading'     => __( 'ARIA Dialog Pattern Implementation', 'wpshadow' ),
					'description' => __( 'Follow W3C ARIA Authoring Practices Guide for proper dialog focus management with role="dialog" and aria-modal="true".', 'wpshadow' ),
				),
			),
			'details'      => array(
				'risks'            => $trap_analysis['risks'],
				'wcag_criterion'   => '2.1.2 No Keyboard Trap (Level A)',
				'testing_required' => __( 'Manual keyboard testing required to confirm', 'wpshadow' ),
				'affected_users'   => __( 'Keyboard users, screen reader users, motor impairments', 'wpshadow' ),
			),
			'resource_links' => array(
				array(
					'title' => __( 'WCAG 2.1.2 No Keyboard Trap', 'wpshadow' ),
					'url'   => 'https://www.w3.org/WAI/WCAG21/Understanding/no-keyboard-trap.html',
				),
				array(
					'title' => __( 'W3C ARIA Dialog Pattern', 'wpshadow' ),
					'url'   => 'https://www.w3.org/WAI/ARIA/apg/patterns/dialog-modal/',
				),
				array(
					'title' => __( 'Focus Trap Library', 'wpshadow' ),
					'url'   => 'https://github.com/focus-trap/focus-trap',
				),
			),
			'kb_link'      => 'https://wpshadow.com/kb/keyboard-navigation-accessibility',
		);
	}

	/**
	 * Analyze theme and plugins for potential keyboard traps.
	 *
	 * Scans JavaScript files for modal/dropdown patterns without
	 * obvious keyboard escape handlers.
	 *
	 * @since  1.6028.2054
	 * @return array {
	 *     Keyboard trap analysis results.
	 *
	 *     @type array $risks List of potential keyboard trap risks.
	 * }
	 */
	private static function analyze_potential_traps() {
		$risks = array();

		// Get active theme.
		$theme         = wp_get_theme();
		$template_dir  = $theme->get_template_directory();
		$stylesheet_dir = $theme->get_stylesheet_directory();

		// Check theme JavaScript files.
		$js_files = array_merge(
			glob( $template_dir . '/js/*.js' ) ?: array(),
			glob( $template_dir . '/assets/js/*.js' ) ?: array(),
			glob( $stylesheet_dir . '/js/*.js' ) ?: array(),
			glob( $stylesheet_dir . '/assets/js/*.js' ) ?: array()
		);

		foreach ( $js_files as $js_file ) {
			if ( ! file_exists( $js_file ) ) {
				continue;
			}

			$js_content  = file_get_contents( $js_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$file_name   = basename( $js_file );
			$has_modal   = preg_match( '/modal|dialog|popup|overlay/i', $js_content );
			$has_escape  = preg_match( '/keydown|keyup|keypress.*Escape|key.*===.*27/i', $js_content );
			$has_focus   = preg_match( '/focus\(\)|\.focus|focustrap|focus-trap/i', $js_content );

			// Modal without escape handler.
			if ( $has_modal && ! $has_escape ) {
				$risks[] = array(
					'type'        => __( 'Modal without Escape key handler', 'wpshadow' ),
					'file'        => $file_name,
					'description' => __( 'Modal/dialog detected without keyboard escape mechanism', 'wpshadow' ),
				);
			}

			// Focus manipulation without proper trap management.
			if ( $has_focus && ! preg_match( '/addEventListener.*keydown/i', $js_content ) ) {
				$risks[] = array(
					'type'        => __( 'Focus manipulation without keyboard handling', 'wpshadow' ),
					'file'        => $file_name,
					'description' => __( 'Focus management detected without keyboard event listeners', 'wpshadow' ),
				);
			}
		}

		// Check for common plugins with known keyboard trap issues.
		$plugin_risks = self::check_plugin_keyboard_issues();
		$risks        = array_merge( $risks, $plugin_risks );

		// Check template files for inline modal JavaScript.
		$template_files = array(
			$template_dir . '/header.php',
			$template_dir . '/footer.php',
			$stylesheet_dir . '/header.php',
			$stylesheet_dir . '/footer.php',
		);

		foreach ( $template_files as $template_file ) {
			if ( ! file_exists( $template_file ) ) {
				continue;
			}

			$template_content = file_get_contents( $template_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			// Check for inline modal scripts without escape.
			if ( preg_match( '/<script[^>]*>.*modal.*<\/script>/is', $template_content ) ) {
				if ( ! preg_match( '/keydown|Escape|key.*27/i', $template_content ) ) {
					$risks[] = array(
						'type'        => __( 'Inline modal script', 'wpshadow' ),
						'file'        => basename( $template_file ),
						'description' => __( 'Inline modal script without keyboard escape handler', 'wpshadow' ),
					);
				}
			}
		}

		return array(
			'risks' => $risks,
		);
	}

	/**
	 * Check active plugins for known keyboard trap issues.
	 *
	 * @since  1.6028.2054
	 * @return array List of plugin keyboard trap risks.
	 */
	private static function check_plugin_keyboard_issues() {
		$risks = array();

		// Plugins known to have keyboard trap issues.
		$problem_plugins = array(
			'popup-maker/popup-maker.php' => array(
				'name'        => 'Popup Maker',
				'description' => __( 'Older versions lack proper keyboard focus management', 'wpshadow' ),
				'version_fix' => '1.16.0',
			),
			'elementor/elementor.php' => array(
				'name'        => 'Elementor',
				'description' => __( 'Popup widgets may trap focus without proper escape', 'wpshadow' ),
				'version_fix' => '3.12.0',
			),
		);

		foreach ( $problem_plugins as $plugin_file => $plugin_data ) {
			if ( ! is_plugin_active( $plugin_file ) ) {
				continue;
			}

			$plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file, false, false );
			$version     = $plugin_info['Version'] ?? '';

			// Check if version is before fix.
			if ( ! empty( $plugin_data['version_fix'] ) && version_compare( $version, $plugin_data['version_fix'], '<' ) ) {
				$risks[] = array(
					'type'        => sprintf(
						/* translators: %s: plugin name */
						__( 'Plugin: %s', 'wpshadow' ),
						$plugin_data['name']
					),
					'file'        => $plugin_file,
					'description' => $plugin_data['description'],
					'fix'         => sprintf(
						/* translators: %s: version number */
						__( 'Update to version %s or later', 'wpshadow' ),
						$plugin_data['version_fix']
					),
				);
			}
		}

		return $risks;
	}
}
