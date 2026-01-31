<?php
/**
 * Admin Input Fields Without Labels In Admin UI Diagnostic
 *
 * Detects form input fields that lack associated label elements.
 * All inputs must have labels for accessibility compliance.
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
 * Admin Input Fields Without Labels In Admin UI Diagnostic Class
 *
 * Scans admin form fields and detects inputs missing associated labels,
 * which breaks accessibility and semantic HTML structure.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Input_Fields_Without_Labels_In_Admin_Ui extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-input-fields-without-labels-in-admin-ui';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Input Fields Without Labels In Admin UI';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects form input fields lacking associated label elements';

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

		$unlabeled = array();

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

					// Check if the field has label information.
					$has_label = false;

					// Check in field args.
					if ( isset( $field['args']['label'] ) && ! empty( $field['args']['label'] ) ) {
						$has_label = true;
					}

					// Check if field structure suggests a label will be rendered.
					if ( isset( $field['args'] ) && is_array( $field['args'] ) ) {
						// Custom callback might handle labels.
						if ( isset( $field['callback'] ) && is_callable( $field['callback'] ) ) {
							$has_label = true; // Assume custom callbacks handle labels.
						}
					}

					if ( ! $has_label ) {
						$unlabeled[] = array(
							'field_id' => $field_id,
							'page'     => $page,
							'section'  => $section,
						);
					}
				}
			}
		}

		if ( empty( $unlabeled ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;
		foreach ( array_slice( $unlabeled, 0, $max_items ) as $field ) {
			$items_list .= sprintf(
				"\n- %s (page: %s, section: %s)",
				esc_html( $field['field_id'] ),
				esc_html( $field['page'] ),
				esc_html( $field['section'] )
			);
		}

		if ( count( $unlabeled ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: number of additional items */
				__( "\n...and %d more unlabeled fields", 'wpshadow' ),
				count( $unlabeled ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d input field(s) without labels. All form inputs must have associated labels for accessibility. Labels improve usability and are required for WCAG compliance.%2$s', 'wpshadow' ),
				count( $unlabeled ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-input-fields-without-labels-in-admin-ui',
			'meta'         => array(
				'unlabeled' => $unlabeled,
			),
		);
	}
}
