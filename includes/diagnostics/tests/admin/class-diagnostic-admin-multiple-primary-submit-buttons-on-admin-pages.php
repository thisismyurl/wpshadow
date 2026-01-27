<?php
/**
 * Admin Multiple Primary Submit Buttons On Admin Pages Diagnostic
 *
 * Detects multiple primary submit buttons on the same admin page.
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
 * Admin Multiple Primary Submit Buttons On Admin Pages Diagnostic Class
 *
 * Identifies pages with multiple primary (button-primary) submit buttons,
 * which confuses users about the primary action.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Multiple_Primary_Submit_Buttons_On_Admin_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-multiple-primary-submit-buttons-on-admin-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multiple Primary Submit Buttons On Admin Pages';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects multiple primary action buttons on the same admin page';

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

		$primary_button_count = preg_match_all( '/class=["\'][^"\']button-primary[^"\']["\']/', $html, $matches );

		if ( $primary_button_count > 1 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d primary submit buttons on one page. Only one primary action should be highlighted.', 'wpshadow' ),
					$primary_button_count
				),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		global $pagenow, $wp_scripts;

		$multiple_primary = array();

		// Scan scripts for button-primary class patterns.
		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			$primary_count = 0;
			$primary_refs  = array();

			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Count occurrences of button-primary + submit.
					$matches = substr_count( $data, 'button-primary' );

					if ( $matches > 0 ) {
						$primary_refs[] = array(
							'handle'  => $handle,
							'count'   => $matches,
							'context' => 'localized_data',
						);
						$primary_count += $matches;
					}
				}
			}

			// If we find multiple primary buttons on current page.
			if ( $primary_count > 1 ) {
				$multiple_primary[] = array(
					'page'      => $pagenow,
					'count'     => $primary_count,
					'locations' => $primary_refs,
					'reason'    => __( 'Multiple primary action buttons detected on same page', 'wpshadow' ),
				);
			}
		}

		// Check settings fields for multiple submit buttons.
		global $wp_settings_fields;

		if ( ! empty( $wp_settings_fields ) && is_array( $wp_settings_fields ) ) {
			foreach ( $wp_settings_fields as $page => $sections ) {
				if ( ! is_array( $sections ) ) {
					continue;
				}

				$submit_count = 0;

				foreach ( $sections as $section => $fields ) {
					if ( ! is_array( $fields ) ) {
						continue;
					}

					foreach ( $fields as $field_id => $field ) {
						if ( strpos( (string) $field_id, 'submit' ) !== false ) {
							++$submit_count;
						}
					}
				}

				if ( $submit_count > 1 ) {
					$multiple_primary[] = array(
						'page'     => $page,
						'count'    => $submit_count,
						'location' => 'settings_fields',
						'reason'   => __( 'Multiple submit buttons in settings fields', 'wpshadow' ),
					);
				}
			}
		}

		if ( empty( $multiple_primary ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 3;

		foreach ( array_slice( $multiple_primary, 0, $max_items ) as $issue ) {
			$items_list .= sprintf(
				"\n- %s has %d primary button(s)",
				esc_html( $issue['page'] ),
				$issue['count']
			);
		}

		if ( count( $multiple_primary ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more pages with multiple primary buttons", 'wpshadow' ),
				count( $multiple_primary ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d page(s) with multiple primary submit buttons. Only one button per form should have the "button-primary" class to clearly indicate the main action to users.%2$s', 'wpshadow' ),
				count( $multiple_primary ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-multiple-primary-submit-buttons-on-admin-pages',
			'meta'         => array(
				'multiple_primary' => $multiple_primary,
			),
		);
	}
}
