<?php
/**
 * Profile Builder Custom Fields Validation Diagnostic
 *
 * Profile Builder Custom Fields Validation issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1225.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profile Builder Custom Fields Validation Diagnostic Class
 *
 * @since 1.1225.0000
 */
class Diagnostic_ProfileBuilderCustomFieldsValidation extends Diagnostic_Base {

	protected static $slug = 'profile-builder-custom-fields-validation';
	protected static $title = 'Profile Builder Custom Fields Validation';
	protected static $description = 'Profile Builder Custom Fields Validation issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! get_option( 'profile_builder_enabled', '' ) && ! class_exists( 'Wppb_Main' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Field validation enabled
		$validation = get_option( 'wppb_field_validation_enabled', 0 );
		if ( ! $validation ) {
			$issues[] = 'Field validation not enabled';
		}

		// Check 2: Email validation
		$email_val = get_option( 'wppb_email_validation', 0 );
		if ( ! $email_val ) {
			$issues[] = 'Email field validation not enabled';
		}

		// Check 3: Required fields enforcement
		$required = get_option( 'wppb_required_fields_enforcement', 0 );
		if ( ! $required ) {
			$issues[] = 'Required fields enforcement not configured';
		}

		// Check 4: Input sanitization
		$sanitize = get_option( 'wppb_input_sanitization', 0 );
		if ( ! $sanitize ) {
			$issues[] = 'Input sanitization not enabled';
		}

		// Check 5: Field length validation
		$length = get_option( 'wppb_field_length_validation', 0 );
		if ( ! $length ) {
			$issues[] = 'Field length validation not enabled';
		}

		// Check 6: AJAX validation
		$ajax = get_option( 'wppb_ajax_validation_enabled', 0 );
		if ( ! $ajax ) {
			$issues[] = 'AJAX validation not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d field validation issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/profile-builder-custom-fields-validation',
			);
		}

		return null;
	}
}
