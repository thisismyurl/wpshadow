<?php
/**
 * Treatment: Add Skip Links to Theme
 *
 * Adds skip-to-content link to theme header for keyboard navigation
 * to comply with WCAG 2.1 Level A.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_No_Skip_Links Class
 *
 * Adds skip navigation link via hook.
 *
 * @since 1.6093.1200
 */
class Treatment_No_Skip_Links extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 1.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'no-skip-links';
	}

	/**
	 * Apply the treatment.
	 *
	 * Adds skip link via WordPress hook and injects CSS for styling.
	 *
	 * @since 1.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		// Check if skip link is already added.
		$existing_hooks = has_action( 'wp_body_open', array( __CLASS__, 'add_skip_link' ) );
		if ( false !== $existing_hooks ) {
			return array(
				'success' => false,
				'message' => __( 'Skip link is already installed. No changes needed.', 'wpshadow' ),
			);
		}

		// Add skip link to wp_body_open hook (WordPress 5.2+).
		add_action( 'wp_body_open', array( __CLASS__, 'add_skip_link' ), 1 );

		// Add CSS for skip link styling.
		add_action( 'wp_head', array( __CLASS__, 'add_skip_link_css' ), 999 );

		// Store treatment application in options.
		update_option( 'wpshadow_skip_link_enabled', true );

		return array(
			'success' => true,
			'message' => __( 'Skip link added to theme! Keyboard users can now bypass navigation. Press Tab on any page to see it.', 'wpshadow' ),
			'details' => array(
				'hook'          => 'wp_body_open',
				'target'        => '#main',
				'wcag_standard' => 'WCAG 2.1 Level A (Success Criterion 2.4.1)',
				'test'          => __( 'Press Tab key on homepage to test skip link functionality.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Add skip link HTML.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function add_skip_link() {
		?>
		<a href="#main" class="wpshadow-skip-link screen-reader-text">
			<?php esc_html_e( 'Skip to content', 'wpshadow' ); ?>
		</a>
		<?php
	}

	/**
	 * Add skip link CSS.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function add_skip_link_css() {
		?>
		<style type="text/css">
			.wpshadow-skip-link {
				position: absolute;
				top: -40px;
				left: 0;
				z-index: 100000;
				padding: 8px 16px;
				background: #fff;
				color: #000;
				text-decoration: none;
				border: 2px solid #0073aa;
				border-radius: 3px;
				font-size: 14px;
				font-weight: 600;
				transition: top 0.2s ease-in-out;
			}
			.wpshadow-skip-link:focus {
				top: 10px;
				outline: 2px solid #0073aa;
				outline-offset: 2px;
			}
			.screen-reader-text {
				clip: rect(1px, 1px, 1px, 1px);
				position: absolute !important;
				height: 1px;
				width: 1px;
				overflow: hidden;
				word-wrap: normal !important;
			}
			.screen-reader-text:focus {
				background-color: #fff;
				border-radius: 3px;
				box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.6);
				clip: auto !important;
				color: #000;
				display: block;
				font-size: 14px;
				font-weight: 600;
				height: auto;
				left: 5px;
				line-height: normal;
				padding: 15px 23px 14px;
				text-decoration: none;
				top: 5px;
				width: auto;
				z-index: 100000;
			}
		</style>
		<?php
	}

	/**
	 * Remove the skip link treatment.
	 *
	 * @since 1.6093.1200
	 * @return bool Success status.
	 */
	public static function remove() {
		remove_action( 'wp_body_open', array( __CLASS__, 'add_skip_link' ), 1 );
		remove_action( 'wp_head', array( __CLASS__, 'add_skip_link_css' ), 999 );
		delete_option( 'wpshadow_skip_link_enabled' );
		return true;
	}
}
