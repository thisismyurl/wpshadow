<?php
/**
 * LifterLMS Membership Security Diagnostic
 *
 * LifterLMS memberships not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.365.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Membership Security Diagnostic Class
 *
 * @since 1.365.0000
 */
class Diagnostic_LifterlmsMembershipSecurity extends Diagnostic_Base {

	protected static $slug = 'lifterlms-membership-security';
	protected static $title = 'LifterLMS Membership Security';
	protected static $description = 'LifterLMS memberships not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'LLMS' ) ) {
			return null;
		}
		
		// Check if LifterLMS is active
		if ( ! function_exists( 'LLMS' ) && ! class_exists( 'LifterLMS' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check membership levels
		$memberships = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'llms_membership'"
		);

		if ( $memberships > 0 ) {
			// Check content restriction
			$restriction_enabled = get_option( 'llms_enable_content_restrictions', 'yes' );
			if ( $restriction_enabled === 'no' ) {
				$issues[] = 'content_restrictions_disabled';
				$threat_level += 40;
			}

			// Check for unrestricted content
			$unrestricted = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts}
					 WHERE post_type IN (%s, %s)
					 AND ID NOT IN (
						SELECT post_id FROM {$wpdb->postmeta}
						WHERE meta_key = '_llms_restricted_levels'
					 )",
					'course',
					'lesson'
				)
			);
			if ( $unrestricted > 10 ) {
				$issues[] = 'lessons_without_restrictions';
				$threat_level += 30;
			}
		}

		// Check enrollment validation
		$enrollment_check = get_option( 'llms_validate_enrollment', 'yes' );
		if ( $enrollment_check === 'no' ) {
			$issues[] = 'enrollment_validation_disabled';
			$threat_level += 35;
		}

		// Check drip content security
		$drip_enabled = get_option( 'llms_drip_content_enabled', 'yes' );
		if ( $drip_enabled === 'yes' ) {
			$drip_bypass = get_option( 'llms_drip_bypass_enabled', 'no' );
			if ( $drip_bypass === 'yes' ) {
				$issues[] = 'drip_content_bypass_enabled';
				$threat_level += 25;
			}
		}

		// Check access plan security
		$open_enrollment = get_option( 'llms_open_enrollment', 'no' );
		if ( $open_enrollment === 'yes' ) {
			$issues[] = 'open_enrollment_enabled';
			$threat_level += 20;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of security issues */
				__( 'LifterLMS memberships have security vulnerabilities: %s. This allows unauthorized access to paid content.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/lifterlms-membership-security',
			);
		}
		
		return null;
	}
}
