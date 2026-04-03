<?php
/**
 * Orphaned Post Meta Diagnostic
 *
 * Detects postmeta rows whose parent posts no longer exist, inflating the
 * postmeta table and slowing down meta queries.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Orphaned_Post_Meta Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Orphaned_Post_Meta extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-post-meta';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Post Meta';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for postmeta rows whose parent posts have been deleted, which inflate the postmeta table and slow down meta queries.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Counts postmeta rows that reference a post_id with no corresponding entry
	 * in the posts table. Flags when any orphaned rows are found.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		global $wpdb;

		$orphaned_count = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			 FROM {$wpdb->postmeta} pm
			 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE p.ID IS NULL"
		);

		if ( $orphaned_count <= 0 ) {
			return null;
		}

		$severity     = $orphaned_count > 5000 ? 'medium' : 'low';
		$threat_level = $orphaned_count > 5000 ? 30 : 15;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of orphaned postmeta rows */
				__( '%d orphaned postmeta rows were found: these are metadata records whose parent posts have been deleted. They inflate the postmeta table without contributing any value, slowing meta queries. Use WP-Optimize or a custom cleanup routine to remove them.', 'wpshadow' ),
				$orphaned_count
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'kb_link'      => 'https://wpshadow.com/kb/orphaned-post-meta',
			'details'      => array(
				'orphaned_count' => $orphaned_count,
			),
		);
	}
}
