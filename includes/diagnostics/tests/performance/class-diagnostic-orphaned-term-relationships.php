<?php
/**
 * Orphaned Term Relationships Diagnostic
 *
 * Detects term relationship rows whose object_id no longer corresponds to any
 * post, adding unnecessary overhead to taxonomy queries.
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
 * Diagnostic_Orphaned_Term_Relationships Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Orphaned_Term_Relationships extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-term-relationships';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Term Relationships';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for term relationship rows whose parent posts have been deleted, adding unnecessary overhead to taxonomy and archive queries.';

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
	 * Counts rows in term_relationships whose object_id references no existing
	 * post. Any non-zero count triggers a finding.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		global $wpdb;

		// Find term relationships whose object_id no longer maps to any post.
		$orphaned_count = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			 FROM {$wpdb->term_relationships} tr
			 LEFT JOIN {$wpdb->posts} p ON p.ID = tr.object_id
			 WHERE p.ID IS NULL"
		);

		if ( $orphaned_count <= 0 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of orphaned relationships */
				__( '%d orphaned term-relationship records were found: they reference posts that no longer exist. These rows add unnecessary overhead to taxonomy queries. Remove them using WP-Optimize, a custom DB cleanup routine, or WP-CLI.', 'wpshadow' ),
				$orphaned_count
			),
			'severity'     => 'low',
			'threat_level' => 15,
			'kb_link'      => 'https://wpshadow.com/kb/orphaned-term-relationships',
			'details'      => array(
				'orphaned_count' => $orphaned_count,
			),
		);
	}
}
