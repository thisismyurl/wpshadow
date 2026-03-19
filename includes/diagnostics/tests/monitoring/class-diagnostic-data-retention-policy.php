<?php
/**
 * Data Retention Policy Diagnostic
 *
 * Analyzes data retention policies and automated cleanup.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Retention Policy Diagnostic
 *
 * Evaluates data retention policies and cleanup mechanisms.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Data_Retention_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-retention-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Retention Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes data retention policies and automated cleanup';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for old user data
		$old_users = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->users} 
				WHERE user_registered < %s",
				gmdate( 'Y-m-d H:i:s', strtotime( '-3 years' ) )
			)
		);

		// Check for old comments (spam/trash)
		$old_comments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_approved IN ('spam', 'trash') 
				AND comment_date < %s",
				gmdate( 'Y-m-d H:i:s', strtotime( '-6 months' ) )
			)
		);

		// Check for old transients
		$old_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				'_transient_timeout_%',
				time() - ( 30 * DAY_IN_SECONDS )
			)
		);

		// Check for WooCommerce orders (if applicable)
		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$old_orders      = 0;

		if ( $has_woocommerce ) {
			$orders_table = $wpdb->prefix . 'posts';
			$old_orders   = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$orders_table} 
					WHERE post_type = 'shop_order' 
					AND post_date < %s",
					gmdate( 'Y-m-d H:i:s', strtotime( '-2 years' ) )
				)
			);
		}

		// Generate findings if old data accumulation detected
		if ( absint( $old_comments ) > 1000 || absint( $old_transients ) > 500 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Large volume of old data detected without retention policy. GDPR requires data minimization and automated cleanup.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-retention-policy',
				'meta'         => array(
					'old_users'      => absint( $old_users ),
					'old_comments'   => absint( $old_comments ),
					'old_transients' => absint( $old_transients ),
					'old_orders'     => absint( $old_orders ),
					'recommendation' => 'Implement automated data cleanup policy',
					'gdpr_article'   => 'Article 5(1)(e) - Storage limitation principle',
					'retention_guidelines' => array(
						'User data: Archive after 3 years inactivity',
						'Spam comments: Delete after 30 days',
						'Transients: Clean expired entries regularly',
						'Orders: Archive/anonymize after 2-7 years',
					),
					'automation_tools' => array(
						'WP-Optimize for comment cleanup',
						'Advanced Database Cleaner',
						'Custom cron jobs for old data',
					),
				),
			);
		}

		return null;
	}
}
