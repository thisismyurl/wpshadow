<?php
/**
 * Gutenberg Reusable Blocks Sync Diagnostic
 *
 * Gutenberg Reusable Blocks Sync issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1239.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg Reusable Blocks Sync Diagnostic Class
 *
 * @since 1.1239.0000
 */
class Diagnostic_GutenbergReusableBlocksSync extends Diagnostic_Base {

	protected static $slug = 'gutenberg-reusable-blocks-sync';
	protected static $title = 'Gutenberg Reusable Blocks Sync';
	protected static $description = 'Gutenberg Reusable Blocks Sync issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Core WordPress feature (Gutenberg/block editor)
		if ( ! function_exists( 'register_block_type' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Reusable blocks exist
		$reusable_blocks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'wp_block'
			)
		);
		
		if ( $reusable_blocks === 0 ) {
			return null; // No reusable blocks to check
		}
		
		// Check 2: Orphaned block references
		$orphaned_refs = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.meta_value = p.ID
			WHERE pm.meta_key = '_wp_block_id' AND p.ID IS NULL"
		);
		
		if ( $orphaned_refs > 0 ) {
			$issues[] = sprintf( __( '%d orphaned block references', 'wpshadow' ), $orphaned_refs );
		}
		
		// Check 3: Block revision tracking
		$blocks_with_revisions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_parent) FROM {$wpdb->posts} 
				WHERE post_type = %s AND post_parent IN (
					SELECT ID FROM {$wpdb->posts} WHERE post_type = 'wp_block'
				)",
				'revision'
			)
		);
		
		if ( $blocks_with_revisions === 0 ) {
			$issues[] = __( 'No revision tracking (cannot restore changes)', 'wpshadow' );
		}
		
		// Check 4: Block usage tracking
		$blocks_in_use = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_content LIKE '%wp:block%'"
		);
		
		if ( $blocks_in_use > ( $reusable_blocks * 10 ) ) {
			$issues[] = sprintf( __( '%d posts using %d blocks (high coupling)', 'wpshadow' ), $blocks_in_use, $reusable_blocks );
		}
		
		// Check 5: Sync conflicts
		$modified_blocks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = %s AND post_modified > post_date + INTERVAL 1 DAY",
				'wp_block'
			)
		);
		
		if ( $modified_blocks > ( $reusable_blocks * 0.5 ) ) {
			$issues[] = sprintf( __( '%d frequently modified blocks (sync issues)', 'wpshadow' ), $modified_blocks );
		}
		
		// Check 6: Database consistency
		$inconsistent = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = %s AND (post_content = '' OR post_content IS NULL)",
				'wp_block'
			)
		);
		
		if ( $inconsistent > 0 ) {
			$issues[] = sprintf( __( '%d empty blocks (database corruption)', 'wpshadow' ), $inconsistent );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of reusable block sync issues */
				__( 'Gutenberg reusable blocks have %d sync issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/gutenberg-reusable-blocks-sync',
		);
	}
}
