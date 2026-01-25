<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Database Optimization Needed
 *
 * Detects opportunities to optimize WordPress database (revisions, trash, spam, etc).
 * Database bloat slows down queries and makes backups larger.
 *
 * @since 1.2.0
 */
class Test_Database_Optimization_Needed extends Diagnostic_Base {


	/**
	 * Check for database optimization opportunities
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$cleanup_items = self::get_cleanup_items();

		if ( empty( $cleanup_items ) || $cleanup_items['total_savings'] < 1000000 ) {
			return null; // Less than 1MB savings available
		}

		$threat = min( 60, count( $cleanup_items['items'] ) * 10 );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'yellow',
			'passed'        => false,
			'issue'         => sprintf(
				'Database can be optimized - save %s',
				self::format_bytes( $cleanup_items['total_savings'] )
			),
			'metadata'      => array(
				'cleanup_items'     => $cleanup_items['items'],
				'total_savings'     => $cleanup_items['total_savings'],
				'formatted_savings' => self::format_bytes( $cleanup_items['total_savings'] ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/database-optimization/',
			'training_link' => 'https://wpshadow.com/training/wordpress-database-maintenance/',
		);
	}

	/**
	 * Guardian Sub-Test: Post revisions cleanup
	 *
	 * @return array Test result
	 */
	public static function test_post_revisions(): array {
		global $wpdb;

		$revision_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );
		$savings        = self::estimate_revisions_savings( (int) $revision_count );

		return array(
			'test_name'         => 'Post Revisions',
			'revision_count'    => (int) $revision_count,
			'potential_savings' => self::format_bytes( $savings ),
			'passed'            => $revision_count < 50,
			'description'       => sprintf( '%d revisions - can save %s', $revision_count, self::format_bytes( $savings ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Deleted posts in trash
	 *
	 * @return array Test result
	 */
	public static function test_trash_posts(): array {
		global $wpdb;

		$trash_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'" );
		$savings     = self::estimate_trash_savings( (int) $trash_count );

		return array(
			'test_name'         => 'Trashed Posts',
			'trash_count'       => (int) $trash_count,
			'potential_savings' => self::format_bytes( $savings ),
			'passed'            => $trash_count < 10,
			'description'       => sprintf( '%d posts in trash - can save %s', $trash_count, self::format_bytes( $savings ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Spam comments cleanup
	 *
	 * @return array Test result
	 */
	public static function test_spam_comments(): array {
		global $wpdb;

		$spam_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
		$savings    = self::estimate_spam_savings( (int) $spam_count );

		return array(
			'test_name'         => 'Spam Comments',
			'spam_count'        => (int) $spam_count,
			'potential_savings' => self::format_bytes( $savings ),
			'passed'            => $spam_count < 100,
			'description'       => sprintf( '%d spam comments - can save %s', $spam_count, self::format_bytes( $savings ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Orphaned postmeta
	 *
	 * @return array Test result
	 */
	public static function test_orphaned_postmeta(): array {
		global $wpdb;

		$orphan_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})" );
		$savings      = self::estimate_orphaned_savings( (int) $orphan_count );

		return array(
			'test_name'         => 'Orphaned Post Meta',
			'orphan_count'      => (int) $orphan_count,
			'potential_savings' => self::format_bytes( $savings ),
			'passed'            => $orphan_count < 100,
			'description'       => sprintf( '%d orphaned post meta entries - can save %s', $orphan_count, self::format_bytes( $savings ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Total optimization summary
	 *
	 * @return array Test result
	 */
	public static function test_optimization_summary(): array {
		$items = self::get_cleanup_items();

		return array(
			'test_name'     => 'Optimization Summary',
			'cleanup_items' => $items['items'],
			'total_savings' => self::format_bytes( $items['total_savings'] ),
			'item_count'    => count( $items['items'] ),
			'passed'        => $items['total_savings'] < 1000000,
			'description'   => sprintf( '%d optimization opportunities found - save %s', count( $items['items'] ), self::format_bytes( $items['total_savings'] ) ),
		);
	}

	/**
	 * Get cleanup opportunities
	 *
	 * @return array Cleanup items and total savings
	 */
	private static function get_cleanup_items(): array {
		global $wpdb;

		$items         = array();
		$total_savings = 0;

		// Post revisions
		$revision_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );
		if ( $revision_count > 50 ) {
			$savings        = self::estimate_revisions_savings( (int) $revision_count );
			$items[]        = array(
				'type'    => 'Post Revisions',
				'count'   => $revision_count,
				'savings' => $savings,
			);
			$total_savings += $savings;
		}

		// Trash posts
		$trash_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'" );
		if ( $trash_count > 10 ) {
			$savings        = self::estimate_trash_savings( (int) $trash_count );
			$items[]        = array(
				'type'    => 'Trashed Posts',
				'count'   => $trash_count,
				'savings' => $savings,
			);
			$total_savings += $savings;
		}

		// Spam comments
		$spam_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
		if ( $spam_count > 100 ) {
			$savings        = self::estimate_spam_savings( (int) $spam_count );
			$items[]        = array(
				'type'    => 'Spam Comments',
				'count'   => $spam_count,
				'savings' => $savings,
			);
			$total_savings += $savings;
		}

		// Orphaned postmeta
		$orphan_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})" );
		if ( $orphan_count > 100 ) {
			$savings        = self::estimate_orphaned_savings( (int) $orphan_count );
			$items[]        = array(
				'type'    => 'Orphaned Post Meta',
				'count'   => $orphan_count,
				'savings' => $savings,
			);
			$total_savings += $savings;
		}

		return array(
			'items'         => $items,
			'total_savings' => $total_savings,
		);
	}

	/**
	 * Estimate savings from post revisions cleanup
	 *
	 * @param int $count Number of revisions
	 * @return int Estimated bytes saved
	 */
	private static function estimate_revisions_savings( int $count ): int {
		return $count * 50000; // ~50KB per revision
	}

	/**
	 * Estimate savings from trash cleanup
	 *
	 * @param int $count Number of trash posts
	 * @return int Estimated bytes saved
	 */
	private static function estimate_trash_savings( int $count ): int {
		return $count * 100000; // ~100KB per post
	}

	/**
	 * Estimate savings from spam cleanup
	 *
	 * @param int $count Number of spam comments
	 * @return int Estimated bytes saved
	 */
	private static function estimate_spam_savings( int $count ): int {
		return $count * 5000; // ~5KB per spam comment
	}

	/**
	 * Estimate savings from orphaned postmeta
	 *
	 * @param int $count Number of orphaned entries
	 * @return int Estimated bytes saved
	 */
	private static function estimate_orphaned_savings( int $count ): int {
		return $count * 2000; // ~2KB per meta entry
	}

	/**
	 * Format bytes as human-readable
	 *
	 * @param int $bytes Byte count
	 * @return string Formatted size
	 */
	private static function format_bytes( int $bytes ): string {
		$units  = array( 'B', 'KB', 'MB', 'GB' );
		$bytes  = max( $bytes, 0 );
		$pow    = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow    = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Database Optimization Needed';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Identifies database cleanup opportunities (revisions, trash, spam, etc)';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Performance';
	}
}
