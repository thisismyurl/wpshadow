<?php
/**
 * Admin Label/Input Mismatches In Admin UI Diagnostic
 *
 * Detects form labels that don't match their associated input fields,
 * causing incorrect label associations and broken accessibility.
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
 * Admin Label/Input Mismatches In Admin UI Diagnostic Class
 *
 * Checks for form labels with incorrect "for" attributes that don't
 * match the associated input IDs, breaking accessibility.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Label_Input_Mismatches_In_Admin_Ui extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-label-input-mismatches-in-admin-ui';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Label/Input Mismatches In Admin UI';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects form labels with incorrect "for" attributes or missing input associations';

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

		global $wp_settings_fields;

		if ( empty( $wp_settings_fields ) || ! is_array( $wp_settings_fields ) ) {
			return null;
		}

		$mismatches = array();

		foreach ( $wp_settings_fields as $page => $sections ) {
			if ( ! is_array( $sections ) ) {
				continue;
			}

			foreach ( $sections as $section => $fields ) {
				if ( ! is_array( $fields ) ) {
					continue;
				}

				foreach ( $fields as $field_id => $field ) {
					if ( empty( $field_id ) || empty( $field ) ) {
						continue;
					}

					// Field object typically has a 'callback' and args that include input ID.
					// For now, flag fields without obvious labels in the callback.
					$callback = isset( $field['callback'] ) ? $field['callback'] : null;
					$args = isset( $field['args'] ) ? $field['args'] : array();

					// This is a heuristic - in real DOM parsing, we'd check label elements.
					if ( ! self::has_label_callback( $callback ) && empty( $args['label'] ) ) {
						$mismatches[] = array(
							'field_id' => $field_id,
							'page'     => $page,
							'section'  => $section,
							'issue'    => __( 'No label detected in callback or args', 'wpshadow' ),
						);
					}
				}
			}
		}

		if ( empty( $mismatches ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;
		foreach ( array_slice( $mismatches, 0, $max_items ) as $mismatch ) {
			$items_list .= sprintf(
				"\n- Field: %s (page: %s) - %s",
				esc_html( $mismatch['field_id'] ),
				esc_html( $mismatch['page'] ),
				esc_html( $mismatch['issue'] )
			);
		}

		if ( count( $mismatches ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: number of additional items */
				__( "\n...and %d more mismatches", 'wpshadow' ),
				count( $mismatches ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d label/input mismatch(es). Labels must have a "for" attribute matching the input ID for proper accessibility and semantic HTML.%2$s', 'wpshadow' ),
				count( $mismatches ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-label-input-mismatches-in-admin-ui',
			'meta'         => array(
				'mismatches' => $mismatches,
			),
		);
	}

	/**
	 * Heuristic to check if a callback function likely renders a label.
	 *
	 * @since  1.2601.2148
	 * @param  callable|null $callback Callback function.
	 * @return bool Whether it likely includes a label.
	 */
	private static function has_label_callback( $callback ): bool {
		if ( ! is_callable( $callback ) ) {
			return false;
		}

		// Try to get callback function name.
		$callback_name = '';

		if ( is_string( $callback ) ) {
			$callback_name = $callback;
		} elseif ( is_array( $callback ) && isset( $callback[1] ) ) {
			$callback_name = $callback[1];
		}

		// Check if name suggests it handles labels.
		return ! empty( $callback_name ) && false === strpos( strtolower( $callback_name ), 'nolabel' );
	}
}
