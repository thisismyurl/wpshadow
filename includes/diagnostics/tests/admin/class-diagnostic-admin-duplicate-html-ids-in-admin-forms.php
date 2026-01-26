<?php
/**
 * Admin Duplicate HTML IDs In Admin Forms Diagnostic
 *
 * Detects duplicate HTML ID attributes in admin forms. IDs must be unique
 * for proper element selection and label association.
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
 * Admin Duplicate HTML IDs In Admin Forms Diagnostic Class
 *
 * Checks the WordPress settings API and registered fields for duplicate IDs,
 * which can break label associations and JavaScript selectors.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Duplicate_Html_Ids_In_Admin_Forms extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-duplicate-html-ids-in-admin-forms';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate HTML IDs In Admin Forms';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects duplicate HTML ID attributes in admin forms';

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

		$id_map = array();
		$duplicates = array();

		foreach ( $wp_settings_fields as $page => $sections ) {
			if ( ! is_array( $sections ) ) {
				continue;
			}

			foreach ( $sections as $section => $fields ) {
				if ( ! is_array( $fields ) ) {
					continue;
				}

				foreach ( $fields as $field_id => $field ) {
					if ( empty( $field_id ) ) {
						continue;
					}

					if ( isset( $id_map[ $field_id ] ) ) {
						// Duplicate found.
						$duplicates[] = array(
							'id'      => $field_id,
							'page'    => $page,
							'section' => $section,
							'first'   => $id_map[ $field_id ],
						);
					} else {
						$id_map[ $field_id ] = array(
							'page'    => $page,
							'section' => $section,
						);
					}
				}
			}
		}

		if ( empty( $duplicates ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;
		foreach ( array_slice( $duplicates, 0, $max_items ) as $dup ) {
			$items_list .= sprintf(
				"\n- ID: %s (page: %s, section: %s)",
				esc_html( $dup['id'] ),
				esc_html( $dup['page'] ),
				esc_html( $dup['section'] )
			);
		}

		if ( count( $duplicates ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: number of additional items */
				__( "\n...and %d more duplicates", 'wpshadow' ),
				count( $duplicates ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d duplicate HTML ID(s) in admin forms. Duplicate IDs break label associations, JavaScript selectors, and CSS styling. Each ID must be unique on the page.%2$s', 'wpshadow' ),
				count( $duplicates ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-duplicate-html-ids-in-admin-forms',
			'meta'         => array(
				'duplicates' => $duplicates,
			),
		);
	}
}
