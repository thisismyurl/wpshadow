<?php
/**
 * BookingPress Custom Fields Diagnostic
 *
 * BookingPress custom fields not sanitized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.462.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingPress Custom Fields Diagnostic Class
 *
 * @since 1.462.0000
 */
class Diagnostic_BookingpressCustomFields extends Diagnostic_Base {

	protected static $slug = 'bookingpress-custom-fields';
	protected static $title = 'BookingPress Custom Fields';
	protected static $description = 'BookingPress custom fields not sanitized';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'BOOKINGPRESS_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Custom form fields exist
		$custom_fields = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}bookingpress_form_fields WHERE bookingpress_field_type IN (%s, %s, %s)",
				'text',
				'textarea',
				'email'
			)
		);
		
		if ( empty( $custom_fields ) ) {
			return null;
		}
		
		// Check 2: Field validation rules
		foreach ( $custom_fields as $field ) {
			$meta_data = maybe_unserialize( $field->bookingpress_field_meta_key );
			if ( empty( $meta_data ) || ! isset( $meta_data['validation'] ) ) {
				$issues[] = sprintf( __( 'Field "%s" lacks validation rules', 'wpshadow' ), $field->bookingpress_field_label );
			}
		}
		
		// Check 3: Sanitization on submission
		$sanitize_enabled = get_option( 'bookingpress_sanitize_custom_fields', false );
		if ( ! $sanitize_enabled ) {
			$issues[] = __( 'Custom field sanitization not explicitly enabled', 'wpshadow' );
		}
		
		// Check 4: HTML allowed in fields
		$unsanitized_count = 0;
		foreach ( $custom_fields as $field ) {
			$meta_data = maybe_unserialize( $field->bookingpress_field_meta_key );
			if ( isset( $meta_data['allow_html'] ) && $meta_data['allow_html'] === true ) {
				$unsanitized_count++;
			}
		}
		
		if ( $unsanitized_count > 0 ) {
			$issues[] = sprintf( __( '%d fields allow HTML input (XSS risk)', 'wpshadow' ), $unsanitized_count );
		}
		
		// Check 5: Check submitted data in database
		$submitted_data = $wpdb->get_results(
			"SELECT bookingpress_customer_meta FROM {$wpdb->prefix}bookingpress_customers LIMIT 100"
		);
		
		$unescaped_data = 0;
		foreach ( $submitted_data as $data ) {
			$meta = maybe_unserialize( $data->bookingpress_customer_meta );
			if ( is_array( $meta ) ) {
				foreach ( $meta as $value ) {
					if ( is_string( $value ) && ( strpos( $value, '<script' ) !== false || strpos( $value, 'javascript:' ) !== false ) ) {
						$unescaped_data++;
						break;
					}
				}
			}
		}
		
		if ( $unescaped_data > 0 ) {
			$issues[] = sprintf( __( '%d customer records contain potentially malicious data', 'wpshadow' ), $unescaped_data );
		}
		
		// Check 6: CSRF protection on form submission
		$nonce_enabled = get_option( 'bookingpress_form_nonce_enabled', false );
		if ( ! $nonce_enabled ) {
			$issues[] = __( 'CSRF protection not enabled for booking forms', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 65;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 80;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 72;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'BookingPress custom fields have %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/bookingpress-custom-fields',
		);
	}
}
