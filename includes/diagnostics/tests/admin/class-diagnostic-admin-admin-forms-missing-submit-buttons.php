<?php
/**
 * Admin Forms Missing Submit Buttons Diagnostic
 *
 * Detects admin forms that lack submit buttons.
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
 * Admin Forms Missing Submit Buttons Diagnostic Class
 *
 * Identifies forms on admin pages that lack submit buttons,
 * making them non-functional or confusing to users.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Admin_Forms_Missing_Submit_Buttons extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-admin-forms-missing-submit-buttons';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Forms Missing Submit Buttons';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects admin forms lacking submit buttons, making them non-functional';

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

		$forms_without_submit = array();

		// Check settings sections for submit buttons.
		global $wp_settings_fields;

		if ( ! empty( $wp_settings_fields ) && is_array( $wp_settings_fields ) ) {
			$pages_with_forms = array();

			foreach ( $wp_settings_fields as $page => $sections ) {
				if ( ! is_array( $sections ) ) {
					continue;
				}

				// Each page with settings should have a submit button.
				$pages_with_forms[ $page ] = array(
					'sections'      => count( $sections ),
					'has_submit'    => false,
					'submit_fields' => array(),
				);

				foreach ( $sections as $section => $fields ) {
					if ( ! is_array( $fields ) ) {
						continue;
					}

					foreach ( $fields as $field_id => $field ) {
						// Look for submit buttons.
						if ( strpos( (string) $field_id, 'submit' ) !== false ) {
							$pages_with_forms[ $page ]['has_submit']    = true;
							$pages_with_forms[ $page ]['submit_fields'][] = $field_id;
						}
					}
				}
			}

			// Identify pages without submit buttons.
			foreach ( $pages_with_forms as $page => $info ) {
				if ( ! $info['has_submit'] && $info['sections'] > 0 ) {
					$forms_without_submit[] = array(
						'page'     => $page,
						'sections' => $info['sections'],
						'reason'   => __( 'Form registered but no submit button found', 'wpshadow' ),
					);
				}
			}
		}

		// Check for forms in scripts without submit handlers.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for form tags without submit buttons.
					if ( strpos( $data, '<form' ) !== false ) {
						if ( strpos( $data, 'type="submit"' ) === false && strpos( $data, "type='submit'" ) === false ) {
							$forms_without_submit[] = array(
								'handle' => $handle,
								'type'   => 'localized_data',
								'reason' => __( 'Form element found without submit button', 'wpshadow' ),
							);
						}
					}
				}
			}
		}

		if ( empty( $forms_without_submit ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $forms_without_submit, 0, $max_items ) as $form ) {
			$label = isset( $form['page'] ) ? $form['page'] : $form['handle'];
			$items_list .= sprintf( "\n- %s", esc_html( $label ) );
		}

		if ( count( $forms_without_submit ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more forms without submit buttons", 'wpshadow' ),
				count( $forms_without_submit ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d form(s) without submit buttons. All forms must have visible submit buttons so users can save their changes or complete the form action.%2$s', 'wpshadow' ),
				count( $forms_without_submit ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-admin-forms-missing-submit-buttons',
			'meta'         => array(
				'forms_without_submit' => $forms_without_submit,
			),
		);
	}
}
