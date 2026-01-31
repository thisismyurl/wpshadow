<?php
/**
 * Contact Form 7 Database Storage Diagnostic
 *
 * Contact Form 7 Database Storage issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1200.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Form 7 Database Storage Diagnostic Class
 *
 * @since 1.1200.0000
 */
class Diagnostic_ContactForm7DatabaseStorage extends Diagnostic_Base {

	protected static $slug = 'contact-form-7-database-storage';
	protected static $title = 'Contact Form 7 Database Storage';
	protected static $description = 'Contact Form 7 Database Storage issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check if Flamingo plugin is active (for CF7 storage)
		$using_storage = class_exists( 'Flamingo_Contact' ) || defined( 'CFDB7_VERSION' );

		if ( ! $using_storage ) {
			$issues[] = 'no_submission_storage';
			$threat_level += 40;
		} else {
			global $wpdb;

			// Check Flamingo submissions table size
			if ( class_exists( 'Flamingo_Contact' ) ) {
				$submissions = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
						'flamingo_contact'
					)
				);
				if ( $submissions > 10000 ) {
					$issues[] = 'excessive_stored_submissions';
					$threat_level += 20;
				}

				// Check for old submissions (over 2 years)
				$old_submissions = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->posts} 
						 WHERE post_type = %s 
						 AND post_date < DATE_SUB(NOW(), INTERVAL 2 YEAR)",
						'flamingo_contact'
					)
				);
				if ( $old_submissions > 1000 ) {
					$issues[] = 'old_submissions_not_cleaned';
					$threat_level += 15;
				}
			}

			// Check CFDB7 database size
			if ( defined( 'CFDB7_VERSION' ) ) {
				$table_name = $wpdb->prefix . 'db7_forms';
				$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name;
				if ( $table_exists ) {
					$table_size = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb
							 FROM information_schema.TABLES
							 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
							DB_NAME,
							$table_name
						)
					);
					if ( $table_size > 100 ) {
						$issues[] = 'database_table_too_large';
						$threat_level += 15;
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of database storage issues */
				__( 'Contact Form 7 database storage has issues: %s. This can cause data loss or slow database performance.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/contact-form-7-database-storage',
			);
		}
		
		return null;
	}
}
