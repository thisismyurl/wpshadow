<?php
/**
 * WP All Import Custom Field Mapping Diagnostic
 *
 * Custom field mapping vulnerable to injection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.277.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP All Import Custom Field Mapping Diagnostic Class
 *
 * @since 1.277.0000
 */
class Diagnostic_WpAllImportCustomFieldMapping extends Diagnostic_Base {

	protected static $slug = 'wp-all-import-custom-field-mapping';
	protected static $title = 'WP All Import Custom Field Mapping';
	protected static $description = 'Custom field mapping vulnerable to injection';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'PMXI_Plugin' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Field mapping validation
		$validation = get_option( 'pmxi_field_mapping_validation_enabled', 0 );
		if ( ! $validation ) {
			$issues[] = 'Field mapping validation not enabled';
		}

		// Check 2: Input sanitization
		$sanitize = get_option( 'pmxi_field_mapping_sanitization_enabled', 0 );
		if ( ! $sanitize ) {
			$issues[] = 'Field mapping sanitization not enabled';
		}

		// Check 3: Injection prevention
		$injection = get_option( 'pmxi_injection_prevention_enabled', 0 );
		if ( ! $injection ) {
			$issues[] = 'Injection prevention not enabled';
		}

		// Check 4: Field type checking
		$type_check = get_option( 'pmxi_field_type_validation_enabled', 0 );
		if ( ! $type_check ) {
			$issues[] = 'Field type validation not enabled';
		}

		// Check 5: Custom callback security
		$callback = get_option( 'pmxi_custom_callback_security_enabled', 0 );
		if ( ! $callback ) {
			$issues[] = 'Custom callback security not configured';
		}

		// Check 6: Regular expression validation
		$regex = get_option( 'pmxi_regex_validation_enabled', 0 );
		if ( ! $regex ) {
			$issues[] = 'Regular expression validation not enabled';
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
					'Found %d field mapping security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-all-import-custom-field-mapping',
			);
		}

		return null;
	}
}
