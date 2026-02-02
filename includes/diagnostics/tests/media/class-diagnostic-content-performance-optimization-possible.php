<?php
/**
 * Content Performance Optimization Possible Diagnostic
 *
 * Tests for content optimization opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Performance Optimization Possible Diagnostic Class
 *
 * Tests for content optimization opportunities.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Content_Performance_Optimization_Possible extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-performance-optimization-possible';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Performance Optimization Possible';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for content optimization opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for very long posts (may benefit from pagination).
		$long_posts = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND CHAR_LENGTH(post_content) > 10000" );

		if ( $long_posts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of long posts */
				__( '%d posts are very long (>10KB) - consider using page pagination or multi-page posts', 'wpshadow' ),
				$long_posts
			);
		}

		// Check for revisions cluttering database.
		$revisions_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );

		if ( $revisions_count > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of revisions */
				__( '%d post revisions stored - consider limiting post revisions to reduce database size', 'wpshadow' ),
				$revisions_count
			);
		}

		// Check post revision limit.
		if ( defined( 'WP_POST_REVISIONS' ) && WP_POST_REVISIONS === true ) {
			$issues[] = __( 'Post revisions are unlimited - should set WP_POST_REVISIONS to a limit like 5', 'wpshadow' );
		}

		// Check for auto-draft clutter.
		$drafts = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'auto-draft'" );

		if ( $drafts > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of auto-drafts */
				__( '%d auto-draft posts cluttering database - consider cleanup', 'wpshadow' ),
				$drafts
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/content-performance-optimization-possible',
			);
		}

		return null;
	}
}
