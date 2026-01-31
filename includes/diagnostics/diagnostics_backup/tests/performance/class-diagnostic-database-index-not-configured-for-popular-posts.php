<?php
/**
 * Database Index Not Configured For Popular Posts Diagnostic
 *
 * Checks if database indexes are configured for popular posts query.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2346
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Index Not Configured For Popular Posts Diagnostic Class
 *
 * Detects missing database indexes.
 *
 * @since 1.2601.2346
 */
class Diagnostic_Database_Index_Not_Configured_For_Popular_Posts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-index-not-configured-for-popular-posts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Index Not Configured For Popular Posts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database indexes exist for popular posts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2346
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if popular posts index exists
		$indexes = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW INDEX FROM {$wpdb->posts} WHERE KEY_NAME = %s",
				'popular_posts_index'
			)
		);

		if ( empty( $indexes ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database index for popular posts is not configured. Add indexes to improve query performance on post_content and post_date columns.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-index-not-configured-for-popular-posts',
			);
		}

		return null;
	}
}
