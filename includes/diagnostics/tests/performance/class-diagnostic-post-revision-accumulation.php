<?php
/**
 * Post Revision Accumulation Diagnostic
 *
 * Checks for excessive post revision accumulation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1354
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revision Accumulation Diagnostic Class
 *
 * Flags when post revisions exceed healthy thresholds.
 *
 * @since 1.5049.1354
 */
class Diagnostic_Post_Revision_Accumulation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revision-accumulation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revision Accumulation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive post revisions in the database';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1354
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$revision_count = (int) $wpdb->get_var(
			"SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		if ( $revision_count >= 1000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A large number of post revisions were found. Consider limiting or cleaning up revisions to improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'revision_count' => $revision_count,
				),
				'kb_link'      => 'https://wpshadow.com/kb/post-revision-accumulation',
			);
		}

		return null;
	}
}
