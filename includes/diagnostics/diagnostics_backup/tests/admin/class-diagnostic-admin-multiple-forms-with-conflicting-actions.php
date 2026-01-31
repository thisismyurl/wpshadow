<?php
/**
 * Admin Multiple Forms With Conflicting Actions Diagnostic
 *
 * Detects multiple forms on the same admin page that have conflicting action handlers.
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
 * Admin Multiple Forms With Conflicting Actions Diagnostic Class
 *
 * Scans for multiple form elements on the same admin page that may have
 * conflicting or ambiguous action handlers, causing form submission issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Multiple_Forms_With_Conflicting_Actions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-multiple-forms-with-conflicting-actions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multiple Forms With Conflicting Actions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects multiple forms on the same page with conflicting or ambiguous action handlers';

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

		global $pagenow, $wp_settings_fields;

		if ( empty( $pagenow ) || empty( $wp_settings_fields ) || ! is_array( $wp_settings_fields ) ) {
			return null;
		}

		$page_forms = array();

		// Collect all form registrations for current page context.
		foreach ( $wp_settings_fields as $page => $sections ) {
			if ( ! is_array( $sections ) ) {
				continue;
			}

			foreach ( $sections as $section => $fields ) {
				if ( ! is_array( $fields ) ) {
					continue;
				}

				// Each section typically represents a form or form section.
				$form_key = $page . ':' . $section;

				if ( ! isset( $page_forms[ $page ] ) ) {
					$page_forms[ $page ] = array();
				}

				$page_forms[ $page ][] = array(
					'form_key' => $form_key,
					'section'  => $section,
					'count'    => count( $fields ),
				);
			}
		}

		$conflicts = array();

		// Check pages with multiple form sections.
		foreach ( $page_forms as $page => $forms ) {
			if ( count( $forms ) < 2 ) {
				continue;
			}

			// Check if forms share the same action target (potential conflict).
			$form_actions = array();

			foreach ( $forms as $form ) {
				$form_id = $form['form_key'];

				if ( ! isset( $form_actions[ $page ] ) ) {
					$form_actions[ $page ] = array();
				}

				// Both forms targeting the same page = potential conflict.
				$form_actions[ $page ][] = array(
					'form' => $form['form_key'],
					'page' => $page,
				);
			}

			// If multiple forms target the same action page, it's a conflict.
			foreach ( $form_actions as $action_page => $forms_list ) {
				if ( count( $forms_list ) > 1 ) {
					$conflicts[] = array(
						'page'   => $action_page,
						'count'  => count( $forms_list ),
						'forms'  => $forms_list,
						'reason' => __( 'Multiple forms on same page with same action target', 'wpshadow' ),
					);
				}
			}
		}

		if ( empty( $conflicts ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 3;

		foreach ( array_slice( $conflicts, 0, $max_items ) as $conflict ) {
			$items_list .= sprintf(
				"\n- Page %s has %d forms with conflicting actions",
				esc_html( $conflict['page'] ),
				$conflict['count']
			);
		}

		if ( count( $conflicts ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more pages with conflicting forms", 'wpshadow' ),
				count( $conflicts ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d location(s) where multiple forms target the same action. When forms conflict, submissions may fail or go to the wrong handler. Each form should have a unique action target or distinct handlers.%2$s', 'wpshadow' ),
				count( $conflicts ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-multiple-forms-with-conflicting-actions',
			'meta'         => array(
				'conflicts' => $conflicts,
			),
		);
	}
}
