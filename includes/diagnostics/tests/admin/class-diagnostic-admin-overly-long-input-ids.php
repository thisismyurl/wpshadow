<?php
/**
 * Admin Overly Long Input IDs Diagnostic
 *
 * Detects form input IDs that are excessively long. Long IDs can cause
 * performance issues and are harder to maintain.
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
 * Admin Overly Long Input IDs Diagnostic Class
 *
 * Scans admin form inputs for ID attributes that exceed reasonable length limits,
 * which can impact HTML size and maintainability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Overly_Long_Input_Ids extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-overly-long-input-ids';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Overly Long Input IDs';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects form input IDs that are excessively long (>128 characters)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Maximum reasonable length for an input ID.
	 *
	 * @var int
	 */
	const MAX_ID_LENGTH = 128;

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

		// This diagnostic would ideally parse the DOM to check input IDs.
		// For foundation, we can check for patterns in registered settings/fields
		// that might generate long IDs.

		// Check WordPress settings API for overly long option keys.
		global $wp_settings_sections, $wp_settings_fields;

		$long_ids = array();

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
						// Generated IDs are often {$option_name}[{$field_id}]
						if ( strlen( $field_id ) > self::MAX_ID_LENGTH ) {
							$long_ids[] = array(
								'id'   => $field_id,
								'page' => $page,
								'section' => $section,
								'length' => strlen( $field_id ),
							);
						}
					}
				}
			}
		}

		if ( empty( $long_ids ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;
		foreach ( array_slice( $long_ids, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s (%d chars)",
				esc_html( substr( $item['id'], 0, 60 ) . ( strlen( $item['id'] ) > 60 ? '...' : '' ) ),
				$item['length']
			);
		}

		if ( count( $long_ids ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: number of additional items */
				__( "\n...and %d more", 'wpshadow' ),
				count( $long_ids ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list, 3: max length */
				__( 'Found %1$d form field(s) with overly long IDs (>%3$d chars). Long IDs increase HTML size and can impact maintainability. Use shorter, semantic field names.%2$s', 'wpshadow' ),
				count( $long_ids ),
				$items_list,
				self::MAX_ID_LENGTH
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-overly-long-input-ids',
			'meta'         => array(
				'long_ids' => $long_ids,
			),
		);
	}
}
