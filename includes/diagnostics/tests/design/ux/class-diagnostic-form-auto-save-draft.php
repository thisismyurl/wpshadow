<?php
/**
 * Form Auto-Save Draft Diagnostic
 *
 * Detects when long forms don't automatically save user progress as a draft.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since      1.6035.2301
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\UX;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Auto-Save Draft Diagnostic Class
 *
 * Checks if long forms have auto-save functionality to prevent data loss.
 *
 * @since 1.6035.2301
 */
class Diagnostic_Form_Auto_Save_Draft extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-auto-save-draft';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Long Forms Don\'t Auto-Save Draft Progress';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when long forms lack auto-save functionality to protect user input';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2301
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		global $wp_scripts;

		$has_autosave = false;
		$long_forms   = array();

		// Check for auto-save JavaScript.
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( empty( $script->src ) ) {
					continue;
				}

				// Check for common auto-save patterns in script names.
				if ( preg_match( '/auto[-_]?save|draft[-_]?save|form[-_]?persist/i', $handle ) ) {
					$has_autosave = true;
					break;
				}
			}
		}

		// Check for plugins with auto-save functionality.
		$autosave_plugins = array(
			'wp-autosave-trigger/wp-autosave-trigger.php',
			'better-autosave/better-autosave.php',
			'form-saver/form-saver.php',
		);

		foreach ( $autosave_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_autosave = true;
				break;
			}
		}

		// Check for long forms (complex forms likely to cause data loss).
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php'       => 'Contact Form 7',
			'ninja-forms/ninja-forms.php'                => 'Ninja Forms',
			'wpforms-lite/wpforms.php'                   => 'WPForms Lite',
			'wpforms/wpforms.php'                        => 'WPForms',
			'gravityforms/gravityforms.php'              => 'Gravity Forms',
			'formidable/formidable.php'                  => 'Formidable Forms',
			'woocommerce/woocommerce.php'                => 'WooCommerce (Checkout)',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
		);

		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$long_forms[] = $name;
			}
		}

		// If no long forms detected or auto-save already exists, no issue.
		if ( empty( $long_forms ) || $has_autosave ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your forms don\'t save progress automatically. If visitors accidentally close their browser or lose connection, they lose all their work', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/form-auto-save',
			'context'      => array(
				'detected_forms'    => $long_forms,
				'has_autosave'      => $has_autosave,
				'impact'            => __( 'Users who spend 10+ minutes filling forms lose all progress if browser crashes or connection drops. This causes frustration and form abandonment.', 'wpshadow' ),
				'recommendation'    => array(
					__( 'Add auto-save functionality to forms with 5+ fields', 'wpshadow' ),
					__( 'Save draft data to localStorage every 30-60 seconds', 'wpshadow' ),
					__( 'Offer to restore saved draft when user returns', 'wpshadow' ),
					__( 'Show visual indicator when draft is saved', 'wpshadow' ),
					__( 'Consider plugins like WPForms (Pro) with built-in save & resume', 'wpshadow' ),
				),
				'estimated_savings' => __( 'Reducing form abandonment by 15-30% by protecting user input', 'wpshadow' ),
			),
		);
	}
}
