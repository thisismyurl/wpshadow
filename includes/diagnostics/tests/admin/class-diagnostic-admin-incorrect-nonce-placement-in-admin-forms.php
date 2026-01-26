<?php
/**
 * Admin Incorrect Nonce Placement In Admin Forms Diagnostic
 *
 * Detects nonce fields with incorrect placement or configuration.
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
 * Admin Incorrect Nonce Placement In Admin Forms Diagnostic Class
 *
 * Identifies admin forms with nonce fields that are improperly placed,
 * named, or configured, which can break security verification.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Incorrect_Nonce_Placement_In_Admin_Forms extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-incorrect-nonce-placement-in-admin-forms';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Incorrect Nonce Placement In Admin Forms';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects nonce fields with incorrect placement or configuration in forms';

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

		$nonce_issues = array();

		// Check settings fields for nonce field configuration.
		global $wp_settings_fields;

		if ( ! empty( $wp_settings_fields ) && is_array( $wp_settings_fields ) ) {
			foreach ( $wp_settings_fields as $page => $sections ) {
				if ( ! is_array( $sections ) ) {
					continue;
				}

				$has_nonce = false;

				foreach ( $sections as $section => $fields ) {
					if ( ! is_array( $fields ) ) {
						continue;
					}

					foreach ( $fields as $field_id => $field ) {
						// Look for nonce fields.
						if ( strpos( (string) $field_id, 'nonce' ) !== false ) {
							$has_nonce = true;

							// Check placement (should be at top or bottom of form).
							// If nonce is in middle sections, flag it.
							$section_count = count( $sections );
							$section_index = array_search( $section, array_keys( $sections ), true );

							if ( $section_index !== 0 && $section_index !== $section_count - 1 ) {
								$nonce_issues[] = array(
									'page'              => $page,
									'field_id'          => $field_id,
									'issue'             => __( 'Nonce placed in middle of form (should be at top or bottom)', 'wpshadow' ),
									'section'           => $section,
									'threat_level'      => 40,
									'security_critical' => true,
								);
							}
						}
					}
				}

				// If form has no nonce, flag it.
				if ( ! $has_nonce && count( $sections ) > 0 ) {
					$section_names = array_keys( $sections );
					$nonce_issues[] = array(
						'page'              => $page,
						'issue'             => __( 'Form has no nonce field', 'wpshadow' ),
						'sections'          => count( $sections ),
						'threat_level'      => 40,
						'security_critical' => true,
					);
				}
			}
		}

		// Check for nonce fields with incorrect names (should be _wpnonce or _nonce_action).
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for nonce handling without standard naming.
					if ( strpos( $data, 'nonce' ) !== false ) {
						// If it has nonce but with non-standard name.
						if ( strpos( $data, '_wpnonce' ) === false && strpos( $data, '_nonce' ) === false ) {
							$nonce_issues[] = array(
								'handle'            => $handle,
								'issue'             => __( 'Non-standard nonce field naming detected', 'wpshadow' ),
								'context'           => 'localized_data',
								'threat_level'      => 40,
								'security_critical' => true,
							);
						}
					}
				}
			}
		}

		if ( empty( $nonce_issues ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $nonce_issues, 0, $max_items ) as $issue ) {
			$label = isset( $issue['page'] ) ? $issue['page'] : $issue['handle'];
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $label ),
				esc_html( $issue['issue'] )
			);
		}

		if ( count( $nonce_issues ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more nonce issues found", 'wpshadow' ),
				count( $nonce_issues ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d nonce configuration issue(s). Nonce fields must be correctly named, placed, and configured to ensure CSRF protection works. Improper nonce handling is a security vulnerability.%2$s', 'wpshadow' ),
				count( $nonce_issues ),
				$items_list
			),
			'severity'     => 'high',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-incorrect-nonce-placement-in-admin-forms',
			'meta'         => array(
				'nonce_issues' => $nonce_issues,
			),
		);
	}
}
