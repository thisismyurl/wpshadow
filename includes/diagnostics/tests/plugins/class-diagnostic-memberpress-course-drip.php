<?php
/**
 * MemberPress Course Drip Diagnostic
 *
 * MemberPress drip content accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.526.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Course Drip Diagnostic Class
 *
 * @since 1.526.0000
 */
class Diagnostic_MemberpressCourseDrip extends Diagnostic_Base {

	protected static $slug = 'memberpress-course-drip';
	protected static $title = 'MemberPress Course Drip';
	protected static $description = 'MemberPress drip content accessible';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Courses exist
		$course_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'memberpresscourse'
			)
		);
		
		if ( $course_count === 0 ) {
			return null;
		}
		
		// Check 2: Drip content configured
		$drip_enabled = get_option( 'mepregate_drip_enabled', false );
		if ( ! $drip_enabled ) {
			return null;
		}
		
		// Check 3: Drip bypass for admins
		$admin_bypass = get_option( 'mepregate_drip_admin_bypass', true );
		if ( $admin_bypass ) {
			$issues[] = __( 'Admins can bypass drip schedule (testing, but security risk)', 'wpshadow' );
		}
		
		// Check 4: REST API access control
		$protect_rest = get_option( 'mepregate_protect_rest_api', false );
		if ( ! $protect_rest ) {
			$issues[] = __( 'Course content accessible via REST API (drip bypass)', 'wpshadow' );
		}
		
		// Check 5: Direct URL access
		$protect_direct = get_option( 'mepregate_prevent_direct_access', true );
		if ( ! $protect_direct ) {
			$issues[] = __( 'Direct URL access not prevented (drip schedule bypass)', 'wpshadow' );
		}
		
		// Check 6: Progress tracking
		$track_progress = get_option( 'mepregate_track_lesson_progress', false );
		if ( ! $track_progress ) {
			$issues[] = __( 'Lesson progress not tracked (drip effectiveness unknown)', 'wpshadow' );
		}
		
		// Check 7: Enrollment verification
		$verify_enrollment = get_option( 'mepregate_verify_enrollment_status', true );
		if ( ! $verify_enrollment ) {
			$issues[] = __( 'Enrollment status not reverified on access (expired members)', 'wpshadow' );
		}
		
		// Check 8: Content snippet protection
		$protect_snippets = get_option( 'mepregate_protect_excerpts', false );
		if ( ! $protect_snippets ) {
			$issues[] = __( 'Course excerpts not protected (content preview leak)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 84;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 77;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'MemberPress course drip has %d security/access issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/memberpress-course-drip',
		);
	}
}
