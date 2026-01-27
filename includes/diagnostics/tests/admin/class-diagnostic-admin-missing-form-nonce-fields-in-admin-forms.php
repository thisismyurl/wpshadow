<?php
/**
 * Admin Missing Form Nonce Fields In Admin Forms Diagnostic
 *
 * Detects admin forms that are missing required nonce fields.
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
 * Admin Missing Form Nonce Fields In Admin Forms Diagnostic Class
 *
 * Identifies admin forms that lack nonce fields, which breaks
 * CSRF protection and security verification.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Missing_Form_Nonce_Fields_In_Admin_Forms extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-missing-form-nonce-fields-in-admin-forms';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Form Nonce Fields In Admin Forms';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects admin forms lacking required nonce fields for CSRF protection';

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

		$pages_to_check = array(
			'options-general.php' => 'General Settings',
			'options-writing.php' => 'Writing Settings',
		);

		$forms_without_nonce = array();

		foreach ( $pages_to_check as $page_slug => $page_name ) {
			$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( $page_slug );
			
			if ( false === $html ) {
				continue;
			}

			preg_match_all( '/<form[^>]*>(.*?)<\/form>/is', $html, $form_matches );
			
			foreach ( $form_matches[0] as $form_html ) {
				$has_nonce = ( preg_match( '/name=["\']_wpnonce["\']/', $form_html ) ||
				               preg_match( '/name=["\'][^"\']*(nonce|token)[^"\']["\']/', $form_html ) );
				
				if ( ! $has_nonce && preg_match( '/<input[^>]*type=["\']submit["\']/', $form_html ) ) {
					$forms_without_nonce[] = $page_name;
					break;
				}
			}
		}

		if ( ! empty( $forms_without_nonce ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Forms missing nonce fields on: %s. This is a critical security vulnerability.', 'wpshadow' ),
					implode( ', ', array_unique( $forms_without_nonce ) )
				),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		// Check settings sections for nonce fields.
		global $wp_settings_fields;

		if ( ! empty( $wp_settings_fields ) && is_array( $wp_settings_fields ) ) {
			foreach ( $wp_settings_fields as $page => $sections ) {
				if ( ! is_array( $sections ) || empty( $sections ) ) {
					continue;
				}

				// Check if this page has any fields at all.
				$total_fields = 0;
				$has_nonce    = false;

				foreach ( $sections as $section => $fields ) {
					if ( ! is_array( $fields ) ) {
						continue;
					}

					$total_fields += count( $fields );

					// Check for nonce fields.
					foreach ( $fields as $field_id => $field ) {
						if ( strpos( (string) $field_id, 'nonce' ) !== false ) {
							$has_nonce = true;
						}
					}
				}

				// If form has fields but no nonce, it's a security issue.
				if ( $total_fields > 0 && ! $has_nonce ) {
					$forms_without_nonce[] = array(
						'page'   => $page,
						'fields' => $total_fields,
						'reason' => __( 'Form fields registered but no nonce field found', 'wpshadow' ),
					);
				}
			}
		}

		// Check for form-like script patterns without nonce verification.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// If script has form markup but no nonce reference.
					if ( strpos( $data, '<form' ) !== false ) {
						if ( strpos( $data, 'nonce' ) === false && strpos( $data, '_wpnonce' ) === false ) {
							$forms_without_nonce[] = array(
								'handle' => $handle,
								'type'   => 'form_in_localized_data',
								'reason' => __( 'Form element found without nonce field', 'wpshadow' ),
							);
						}
					}
				}
			}
		}

		if ( empty( $forms_without_nonce ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $forms_without_nonce, 0, $max_items ) as $form ) {
			$label = isset( $form['page'] ) ? $form['page'] : $form['handle'];
			$items_list .= sprintf( "\n- %s", esc_html( $label ) );
		}

		if ( count( $forms_without_nonce ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more forms without nonce fields", 'wpshadow' ),
				count( $forms_without_nonce ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d form(s) missing nonce fields. All forms that modify data must include nonce fields (using wp_nonce_field()) to prevent CSRF attacks. This is a critical security requirement.%2$s', 'wpshadow' ),
				count( $forms_without_nonce ),
				$items_list
			),
			'severity'     => 'high',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-missing-form-nonce-fields-in-admin-forms',
			'meta'         => array(
				'forms_without_nonce' => $forms_without_nonce,
			),
		);
	}
}
