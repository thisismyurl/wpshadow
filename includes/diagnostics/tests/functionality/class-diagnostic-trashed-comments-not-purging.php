<?php
/**
 * Trashed Comments Not Purging Diagnostic
 *
 * Checks if trashed comments are being automatically purged.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2309
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trashed Comments Not Purging Diagnostic Class
 *
 * Checks if trash purging is functioning.
 *
 * @since 1.2601.2309
 */
class Diagnostic_Trashed_Comments_Not_Purging extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'trashed-comments-not-purging';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Trashed Comments Not Purging';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if trashed comments are being automatically purged';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2309
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get WordPress trash settings
		$empty_trash_days = defined( 'EMPTY_TRASH_DAYS' ) ? EMPTY_TRASH_DAYS : 30;

		if ( $empty_trash_days === 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Trash auto-purging is disabled (EMPTY_TRASH_DAYS = 0)', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/trashed-comments-not-purging',
			);
		}

		// Find oldest trashed comment
		$oldest_trash = $wpdb->get_row(
			"SELECT comment_date, DATEDIFF(NOW(), comment_date) as days_old
			 FROM {$wpdb->comments}
			 WHERE comment_approved = 'trash'
			 ORDER BY comment_date ASC
			 LIMIT 1"
		);

		if ( $oldest_trash && $oldest_trash->days_old > ( $empty_trash_days + 7 ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of days */
					__( 'Oldest trashed comment is %d days old (should be purged)', 'wpshadow' ),
					$oldest_trash->days_old
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/trashed-comments-not-purging',
			);
		}

		// Check if WordPress scheduled event for trash purging exists
		$next_trash_purge = wp_next_scheduled( 'delete_expired_transients' );
		if ( ! $next_trash_purge ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WordPress scheduled trash cleanup event may not be running', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/trashed-comments-not-purging',
			);
		}

		return null;
	}
}
