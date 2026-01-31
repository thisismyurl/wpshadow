<?php
/**
 * Orphaned Post Meta Cleanup Diagnostic
 *
 * Checks for meta entries referencing deleted posts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1401
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orphaned Post Meta Cleanup Diagnostic Class
 *
 * Detects post meta entries orphaned by deleted posts.
 *
 * @since 1.5049.1401
 */
class Diagnostic_Orphaned_Post_Meta_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-post-meta-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Post Meta Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for post metadata from deleted posts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$orphaned = (int) $wpdb->get_var(
			"SELECT COUNT(1) FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned >= 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Orphaned post metadata from deleted posts was found. Cleaning it up can improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'orphaned_count' => $orphaned,
				),
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-post-meta-cleanup',
			);
		}

		return null;
	}
}
