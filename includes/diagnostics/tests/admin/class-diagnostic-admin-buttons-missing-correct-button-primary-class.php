<?php
/**
 * Admin Buttons Missing Correct Button Primary Class Diagnostic
 *
 * Detects primary submit buttons missing the "button-primary" class.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Buttons Missing Correct Button Primary Class Diagnostic Class
 *
 * Scans admin forms and identifies primary submit buttons that lack
 * the required "button-primary" class for proper styling.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Buttons_Missing_Correct_Button_Primary_Class extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-buttons-missing-correct-button-primary-class';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Buttons Missing "button-primary" Class';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects primary submit buttons missing the required button-primary class';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( 'options-general.php' );
		
		if ( false === $html ) {
			return null;
		}

		preg_match_all( '/<input[^>]*type=["\']submit["\'][^>]*>/i', $html, $submit_matches );
		$buttons_without_class = 0;

		foreach ( $submit_matches[0] as $button_html ) {
			if ( false === strpos( $button_html, 'button-primary' ) && false === strpos( $button_html, 'button-secondary' ) ) {
				$buttons_without_class++;
			}
		}

		if ( $buttons_without_class > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d submit button(s) missing WordPress button classes. This affects UI consistency.', 'wpshadow' ),
					$buttons_without_class
				),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		$missing_class_buttons = array();

		// Check for inline button markup patterns in scripts.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for submit buttons without button-primary.
					if ( preg_match( '/type\s*=\s*["\']submit["\']/', $data ) ) {
						if ( strpos( $data, 'button-primary' ) === false ) {
							$missing_class_buttons[] = array(
								'handle' => $handle,
								'type'   => 'localized_data',
								'reason' => __( 'Submit button found without button-primary class', 'wpshadow' ),
							);
						}
					}
				}
			}
		}

		// Check settings fields for button markup.
		global $wp_settings_fields;

		if ( ! empty( $wp_settings_fields ) && is_array( $wp_settings_fields ) ) {
			foreach ( $wp_settings_fields as $page => $sections ) {
				if ( ! is_array( $sections ) ) {
					continue;
				}

				foreach ( $sections as $section => $fields ) {
					if ( ! is_array( $fields ) ) {
						continue;
					}

					foreach ( $fields as $field_id => $field ) {
						if ( isset( $field['callback'] ) && is_callable( $field['callback'] ) ) {
							// We cannot inspect callback content directly,
							// but we can flag fields that should have primary buttons.
							if ( strpos( (string) $field_id, 'submit' ) !== false ) {
								$missing_class_buttons[] = array(
									'field_id' => $field_id,
									'page'     => $page,
									'section'  => $section,
									'reason'   => __( 'Field may be a submit button; verify button-primary class', 'wpshadow' ),
								);
							}
						}
					}
				}
			}
		}

		if ( empty( $missing_class_buttons ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $missing_class_buttons, 0, $max_items ) as $button ) {
			$label = isset( $button['handle'] ) ? $button['handle'] : $button['field_id'];
			$items_list .= sprintf( "\n- %s", esc_html( $label ) );
		}

		if ( count( $missing_class_buttons ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more buttons to check", 'wpshadow' ),
				count( $missing_class_buttons ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d button(s) that may be missing the "button-primary" class. Primary action buttons must have the "button-primary" class for correct styling and consistency with WordPress design standards.%2$s', 'wpshadow' ),
				count( $missing_class_buttons ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-buttons-missing-correct-button-primary-class',
			'meta'         => array(
				'missing_buttons' => $missing_class_buttons,
			),
		);
	}
}
