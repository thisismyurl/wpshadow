<?php
/**
 * Treatment: Accessibility Compliance (WCAG AA)
 *
 * Adds missing accessibility features to meet WCAG AA standards.
 * Enables skip links, ARIA landmarks, keyboard navigation support.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Accessibility_Compliance Class
 *
 * Implements WCAG AA compliance fixes including skip links,
 * ARIA landmarks, and keyboard navigation support.
 *
 * @since 0.6093.1200
 */
class Treatment_Accessibility_Compliance extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'accessibility-compliance';
	}

	/**
	 * Apply the treatment.
	 *
	 * Creates a mu-plugin to add missing accessibility features.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 * }
	 */
	public static function apply() {
		// Create mu-plugin for accessibility enhancements.
		$mu_plugin_code = self::get_accessibility_mu_plugin_code();
		$mu_plugin_path = WPMU_PLUGIN_DIR . '/wpshadow-accessibility-compliance.php';

		// Create mu-plugins directory if it doesn't exist.
		if ( ! is_dir( WPMU_PLUGIN_DIR ) ) {
			wp_mkdir_p( WPMU_PLUGIN_DIR );
		}

		// Write the mu-plugin file.
		$result = file_put_contents( $mu_plugin_path, $mu_plugin_code );

		if ( $result === false ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to create accessibility compliance mu-plugin', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Added WCAG AA accessibility compliance features', 'wpshadow' ),
		);
	}

	/**
	 * Undo the treatment.
	 *
	 * Removes the accessibility mu-plugin.
	 *
	 * @since 0.6093.1200
	 * @return array Result array.
	 */
	public static function undo() {
		$mu_plugin_path = WPMU_PLUGIN_DIR . '/wpshadow-accessibility-compliance.php';

		if ( file_exists( $mu_plugin_path ) ) {
			unlink( $mu_plugin_path );
			return array(
				'success' => true,
				'message' => __( 'Removed accessibility compliance mu-plugin', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Accessibility compliance mu-plugin already removed', 'wpshadow' ),
		);
	}

	/**
	 * Get the mu-plugin code for accessibility compliance.
	 *
	 * @since 0.6093.1200
	 * @return string PHP code for the mu-plugin.
	 */
	private static function get_accessibility_mu_plugin_code() {
		return '<?php
/**
 * WPShadow Accessibility Compliance
 *
 * Adds WCAG AA accessibility features including skip links,
 * ARIA landmarks, and keyboard navigation support.
 *
 * @package WPShadow
 */

// Add skip-to-content link if not present in theme.
add_action( \'wp_body_open\', function() {
	echo \'<a href="#content" class="skip-link screen-reader-text">\' .
		esc_html__( \"Skip to content\", \"wpshadow\" ) .
		\'</a>\';
} );

// Ensure wp_body_open is called in theme.
if ( ! did_action( \'wp_body_open\' ) ) {
	add_action( \'wp_head\', function() {
		if ( ! doing_action( \'wp_body_open\' ) ) {
			do_action( \'wp_body_open\' );
		}
	} );
}

// Add keyboard focus styles for accessibility.
add_action( \'wp_enqueue_scripts\', function() {
	$css = ":focus, :focus-within {
		outline: 2px solid #4A90E2;
		outline-offset: 2px;
	}

	.skip-link {
		position: absolute;
		top: -9999px;
		left: -9999px;
		z-index: 999;
	}

	.skip-link:focus {
		top: 0;
		left: 0;
		right: auto;
		width: auto;
		height: auto;
		overflow: visible;
		clip: auto;
		background: #000;
		color: #fff;
		padding: 8px 16px;
		text-decoration: none;
	}";

	wp_add_inline_style( \'wp-components\', $css );
} );

// Add ARIA landmarks to theme structure.
add_action( \'template_redirect\', function() {
	// Add role=\"main\" to main content area.
	add_filter( \'the_content\', function( $content ) {
		static $added = false;
		if ( ! $added && is_main_query() ) {
			$content = \'<main id="content" role="main">\' . $content . \'</main>\';
			$added = true;
		}
		return $content;
	}, 999 );
} );
?>" . \'<?php\';
	}
}
