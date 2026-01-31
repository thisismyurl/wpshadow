<?php
/**
 * Data Retention and Disposal Diagnostic
 *
 * Verifies appropriate retention limits and secure disposal procedures under
 * PIPEDA Principle 4.5. Organizations must not keep personal information longer than necessary.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Retention and Disposal Diagnostic Class
 *
 * PIPEDA requires that personal information be retained only as long as necessary
 * for the fulfillment of identified purposes, then securely disposed.
 *
 * @since 1.6032.1530
 */
class Diagnostic_Data_Retention_Disposal extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-retention-disposal';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Retention and Disposal';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify appropriate retention limits and secure disposal';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1530
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		
		// Check for retention policy documentation
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		$has_retention_policy = false;
		
		if ( $privacy_page_id ) {
			$privacy_page = get_post( $privacy_page_id );
			if ( $privacy_page ) {
				$content = strtolower( $privacy_page->post_content );
				$has_retention_policy = stripos( $content, 'retention' ) !== false ||
									   stripos( $content, 'how long' ) !== false ||
									   stripos( $content, 'keep your data' ) !== false;
			}
		}
		
		if ( ! $has_retention_policy ) {
			$issues[] = 'no_retention_policy';
		}
		
		// Check for automated data cleanup plugins
		$cleanup_plugins = array(
			'wp-sweep/wp-sweep.php',
			'advanced-database-cleaner/advanced-db-cleaner.php',
		);
		
		$has_cleanup_automation = false;
		foreach ( $cleanup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cleanup_automation = true;
				break;
			}
		}
		
		// Check for old user accounts (potential retention issue)
		$old_users = get_users( array(
			'number' => 1,
			'date_query' => array(
				array(
					'column' => 'user_registered',
					'before' => '3 years ago',
				),
			),
		) );
		
		if ( count( $old_users ) > 0 ) {
			// Check if these users have recent activity
			$inactive_users = get_users( array(
				'number' => 10,
				'meta_query' => array(
					array(
						'key' => 'last_login',
						'compare' => 'NOT EXISTS',
					),
				),
			) );
			
			if ( count( $inactive_users ) > 5 ) {
				$issues[] = 'inactive_users_not_deleted';
			}
		}
		
		// Check for old comments (spam, unapproved)
		$old_comments_count = get_comments( array(
			'status' => 'spam',
			'count' => true,
		) );
		
		if ( $old_comments_count > 100 ) {
			$issues[] = 'old_spam_comments_retained';
		}
		
		// Check for old transients
		$transient_count = 0;
		$all_options = wp_load_alloptions();
		foreach ( $all_options as $option_name => $option_value ) {
			if ( strpos( $option_name, '_transient_' ) === 0 ) {
				$transient_count++;
			}
		}
		
		if ( $transient_count > 500 ) {
			$issues[] = 'excessive_transients_not_cleaned';
		}
		
		// Check for backup retention policy
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'duplicator/duplicator.php',
		);
		
		$has_backup_plugin = false;
		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_backup_plugin = true;
				break;
			}
		}
		
		if ( $has_backup_plugin && ! $has_retention_policy ) {
			$issues[] = 'backup_retention_not_documented';
		}
		
		if ( count( $issues ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Website lacks proper data retention and disposal procedures', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-retention-disposal',
				'details'      => array(
					'issues_found'      => $issues,
					'pipeda_principle'  => 'Principle 4.5 - Limiting Use, Disclosure, and Retention',
					'requirements'      => array(
						'time_limit'    => 'Retain only as long as necessary for identified purposes',
						'secure_disposal' => 'Use secure methods to dispose of data',
						'documentation' => 'Document retention periods for different data types',
						'backups'       => 'Retention limits apply to backups as well',
					),
					'risks'             => __( 'Retaining data unnecessarily increases breach risk and ongoing violation', 'wpshadow' ),
					'detection_rate'    => '80% of organizations lack documented retention policies',
				),
				'meta'         => array(
					'diagnostic_class' => __CLASS__,
					'timestamp'        => current_time( 'mysql' ),
					'wpdb_avoidance'   => 'Uses get_option(), get_post(), get_users(), get_comments()',
				),
				'solution'     => array(
					'free'     => __( 'Create retention policy and schedule manual cleanup of old data quarterly', 'wpshadow' ),
					'premium'  => __( 'Install automated cleanup plugin and document retention periods by data type', 'wpshadow' ),
					'advanced' => __( 'Implement automated retention schedule with secure disposal procedures and audit logs', 'wpshadow' ),
				),
			);
		}
		
		return null;
	}
}
