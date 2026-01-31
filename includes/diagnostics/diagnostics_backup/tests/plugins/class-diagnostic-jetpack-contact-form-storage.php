<?php
/**
 * Jetpack Contact Form Storage Diagnostic
 *
 * Jetpack Contact Form Storage issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1218.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Contact Form Storage Diagnostic Class
 *
 * @since 1.1218.0000
 */
class Diagnostic_JetpackContactFormStorage extends Diagnostic_Base {

	protected static $slug = 'jetpack-contact-form-storage';
	protected static $title = 'Jetpack Contact Form Storage';
	protected static $description = 'Jetpack Contact Form Storage issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check if Jetpack and contact form module are active
		if ( ! class_exists( 'Jetpack' ) || ! Jetpack::is_module_active( 'contact-form' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check for feedback post type (where Jetpack stores form submissions)
		$feedback_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'feedback'
			)
		);

		if ( $feedback_count === 0 ) {
			$issues[] = 'no_submissions_stored';
			$threat_level += 20;
		} elseif ( $feedback_count > 10000 ) {
			$issues[] = 'excessive_submissions';
			$threat_level += 25;
		}

		// Check for old submissions
		$old_submissions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type = %s 
				 AND post_date < DATE_SUB(NOW(), INTERVAL 2 YEAR)",
				'feedback'
			)
		);
		if ( $old_submissions > 1000 ) {
			$issues[] = 'old_submissions_not_cleaned';
			$threat_level += 20;
		}

		// Check export capability
		$export_enabled = get_option( 'jetpack_contact_form_export', false );
		if ( ! $export_enabled && $feedback_count > 0 ) {
			$issues[] = 'export_not_enabled';
			$threat_level += 15;
		}

		// Check spam submissions
		$spam_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type = %s AND post_status = %s",
				'feedback',
				'spam'
			)
		);
		if ( $spam_count > 500 ) {
			$issues[] = 'excessive_spam_submissions';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of storage issues */
				__( 'Jetpack Contact Form storage has issues: %s. Total submissions: %d. This wastes database space and slows queries.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) ),
				$feedback_count
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/jetpack-contact-form-storage',
			);
		}
		
		return null;
	}
}
