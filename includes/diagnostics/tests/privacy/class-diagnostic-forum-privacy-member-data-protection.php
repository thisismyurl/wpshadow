<?php
/**
 * Forum Privacy and Member Data Protection Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Forum_Privacy_Member_Data_Protection extends Diagnostic_Base {
	protected static $slug = 'forum-privacy-member-data-protection';
	protected static $title = 'Forum Privacy and Member Data Protection';
	protected static $description = 'Ensures forum member data protected and GDPR-compliant';
	protected static $family = 'privacy';

	public static function check() {
		// Check for common forum plugins.
		$forum_plugins = array(
			'bbPress'      => class_exists( 'bbPress' ),
			'BuddyPress'   => class_exists( 'BuddyPress' ),
			'wpForo'       => class_exists( 'wpForo' ),
			'Simple:Press' => class_exists( 'spcCore' ),
			'Asgaros'      => class_exists( 'AsgarosForum' ),
		);

		$active_forums = array_filter( $forum_plugins );

		if ( empty( $active_forums ) ) {
			return null; // No forum plugins active.
		}

		$issues = array();

		// Check for data export capability.
		if ( ! has_filter( 'wp_privacy_personal_data_exporters' ) ) {
			$issues[] = array(
				'issue'       => 'no_data_export',
				'description' => __( 'Forum does not support GDPR data export for members', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check for data erasure capability.
		if ( ! has_filter( 'wp_privacy_personal_data_erasers' ) ) {
			$issues[] = array(
				'issue'       => 'no_data_erasure',
				'description' => __( 'Forum does not support GDPR data erasure for members', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check for privacy policy.
		$privacy_page = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === $privacy_page ) {
			$issues[] = array(
				'issue'       => 'no_privacy_policy',
				'description' => __( 'No privacy policy configured for forum member data', 'wpshadow' ),
				'severity'    => 'critical',
			);
		}

		// Check if IP addresses are being logged.
		global $wpdb;
		$ip_logging = false;
		
		if ( class_exists( 'bbPress' ) ) {
			$ip_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_bbp_author_ip' LIMIT 1" );
			$ip_logging = $ip_count > 0;
		}

		if ( $ip_logging ) {
			$issues[] = array(
				'issue'       => 'ip_logging_not_disclosed',
				'description' => __( 'Forum logs IP addresses - ensure this is disclosed in privacy policy', 'wpshadow' ),
				'severity'    => 'medium',
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d forum privacy compliance issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/forum-privacy-member-data-protection',
		);
	}
}
