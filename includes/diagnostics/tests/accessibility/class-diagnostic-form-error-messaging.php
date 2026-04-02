<?php
/**
 * Form Error Messaging Diagnostic
 *
 * Checks whether the active form plugin(s) are configured to display
 * inline, accessible error messages that are programmatically associated
 * with their form fields (WCAG 1.3.1, 3.3.1, 3.3.3).
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Form_Error_Messaging Class
 *
 * Identifies active form plugins and flags any whose known default
 * configuration does not include accessible inline error messaging.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Form_Error_Messaging extends Diagnostic_Base {

	/** @var string */
	protected static $slug = 'form-error-messaging';

	/** @var string */
	protected static $title = 'Form Error Messaging';

	/** @var string */
	protected static $description = 'Checks whether active form plugins support accessible inline error messaging that is programmatically associated with the relevant input fields.';

	/** @var string */
	protected static $family = 'accessibility';

	/**
	 * Known form plugins. Value = true when the plugin ships with accessible
	 * error messaging by default, false when it does not.
	 *
	 * @var array<string,bool>
	 */
	private const FORM_PLUGINS = array(
		'contact-form-7/wp-contact-form-7.php' => true,  // CF7 v5+ uses aria-describedby.
		'wpforms-lite/wpforms.php'              => true,  // WPForms accessible since 1.7.9.
		'wpforms/wpforms.php'                   => true,
		'gravityforms/gravityforms.php'         => true,  // GF 2.5+ accessibility mode.
		'ninja-forms/ninja-forms.php'           => true,  // Ninja Forms 3.6+.
		'formidable/formidable.php'             => true,  // Formidable v6+.
		'fluentform/fluentform.php'             => false, // Older versions lack aria-describedby.
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active = get_option( 'active_plugins', array() );
		$issues = array();

		foreach ( self::FORM_PLUGINS as $plugin_file => $is_accessible ) {
			if ( ! in_array( $plugin_file, $active, true ) ) {
				continue;
			}

			// A known-accessible plugin is active — no issue.
			if ( $is_accessible ) {
				return null;
			}

			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file, false, false );
			$issues[]    = $plugin_data['Name'] ?? basename( dirname( $plugin_file ) );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: plugin name */
				__( 'The active form plugin "%s" is not known to include accessible inline error messaging by default. Validation errors may not be programmatically associated with their fields, failing WCAG 3.3.1.', 'wpshadow' ),
				esc_html( implode( ', ', $issues ) )
			),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => 'https://wpshadow.com/kb/accessible-form-error-messaging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'flagged_plugins' => $issues,
				'fix'             => __( 'Review your form plugin\'s documentation for WCAG or accessibility settings. Ensure every validation error is displayed inline next to its field and associated via aria-describedby or a matching label.', 'wpshadow' ),
			),
		);
	}
}
