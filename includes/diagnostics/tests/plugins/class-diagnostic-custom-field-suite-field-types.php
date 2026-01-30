<?php
/**
 * Custom Field Suite Field Types Diagnostic
 *
 * Custom Field Suite Field Types issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1058.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Field Suite Field Types Diagnostic Class
 *
 * @since 1.1058.0000
 */
class Diagnostic_CustomFieldSuiteFieldTypes extends Diagnostic_Base {

	protected static $slug = 'custom-field-suite-field-types';
	protected static $title = 'Custom Field Suite Field Types';
	protected static $description = 'Custom Field Suite Field Types issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'CFS' ) && ! function_exists( 'CFS' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify field groups are configured
		$field_groups = get_posts( array(
			'post_type'      => 'cfs',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );
		if ( empty( $field_groups ) ) {
			$issues[] = 'No custom field groups configured';
		}
		
		// Check 2: Check for field group assignments
		if ( ! empty( $field_groups ) ) {
			foreach ( $field_groups as $group ) {
				$rules = get_post_meta( $group->ID, 'cfs_rules', true );
				if ( empty( $rules ) ) {
					$issues[] = sprintf( 'Field group "%s" has no assignment rules', $group->post_title );
					break;
				}
			}
		}
		
		// Check 3: Verify field types are properly registered
		$registered_fields = apply_filters( 'cfs_field_types', array() );
		if ( empty( $registered_fields ) ) {
			$issues[] = 'No custom field types registered';
		}
		
		// Check 4: Check for repeater field usage
		if ( ! empty( $field_groups ) ) {
			foreach ( $field_groups as $group ) {
				$fields = get_post_meta( $group->ID, 'cfs_fields', true );
				if ( ! empty( $fields ) ) {
					foreach ( $fields as $field ) {
						if ( isset( $field['type'] ) && $field['type'] === 'loop' ) {
							if ( ! isset( $field['button_label'] ) || empty( $field['button_label'] ) ) {
								$issues[] = 'Repeater field missing button label';
								break 2;
							}
						}
					}
				}
			}
		}
		
		// Check 5: Verify database table exists
		global $wpdb;
		$table_name = $wpdb->prefix . 'cfs_values';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		if ( ! $table_exists ) {
			$issues[] = 'CFS database table not found';
		}
		
		// Check 6: Check for field validation
		if ( ! empty( $field_groups ) ) {
			$has_validation = false;
			foreach ( $field_groups as $group ) {
				$fields = get_post_meta( $group->ID, 'cfs_fields', true );
				if ( ! empty( $fields ) ) {
					foreach ( $fields as $field ) {
						if ( isset( $field['required'] ) && $field['required'] ) {
							$has_validation = true;
							break 2;
						}
					}
				}
			}
			if ( ! $has_validation ) {
				$issues[] = 'No required field validation configured';
			}
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Custom Field Suite issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/custom-field-suite-field-types',
			);
		}
		
		return null;
	}
}
