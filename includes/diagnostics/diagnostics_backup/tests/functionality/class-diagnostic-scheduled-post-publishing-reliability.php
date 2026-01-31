<?php
/**
 * Scheduled Post Publishing Reliability Diagnostic
 *
 * Checks if scheduled posts reliably publish.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2320
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scheduled Post Publishing Reliability Diagnostic Class
 *
 * Detects unreliable scheduled publishing.
 *
 * @since 1.2601.2320
 */
class Diagnostic_Scheduled_Post_Publishing_Reliability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'scheduled-post-publishing-reliability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Scheduled Post Publishing Reliability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if scheduled posts are reliable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2320
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for scheduled posts
		$scheduled_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'future'"
		);

		if ( $scheduled_posts > 0 ) {
			// Check if loopback requests can access site
			if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'WordPress cron is disabled but scheduled posts exist. Configure external cron or enable WP-Cron.', 'wpshadow' ),
					'severity'      => 'high',
					'threat_level'  => 60,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/scheduled-post-publishing-reliability',
				);
			}
		}

		return null;
	}
}
