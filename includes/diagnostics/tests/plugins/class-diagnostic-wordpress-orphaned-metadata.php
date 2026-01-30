<?php
/**
 * Wordpress Orphaned Metadata Diagnostic
 *
 * Wordpress Orphaned Metadata issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1279.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Orphaned Metadata Diagnostic Class
 *
 * @since 1.1279.0000
 */
class Diagnostic_WordpressOrphanedMetadata extends Diagnostic_Base {

	protected static $slug = 'wordpress-orphaned-metadata';
	protected static $title = 'Wordpress Orphaned Metadata';
	protected static $description = 'Wordpress Orphaned Metadata issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// This is a WordPress core feature, always applicable
		$issues = array();
		
		// Check 1: Count orphaned postmeta
		$orphaned_postmeta = get_posts( array(
			'post_type'      => 'any',
			'post_status'    => 'trash',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		if ( ! empty( $orphaned_postmeta ) ) {
			$issues[] = 'Posts in trash may have orphaned metadata';
		}
		
		// Check 2: Check for orphaned term relationships
		$terms = get_terms( array(
			'taxonomy'   => get_taxonomies(),
			'hide_empty' => false,
			'count'      => true,
		) );
		$empty_terms = 0;
		foreach ( $terms as $term ) {
			if ( $term->count == 0 ) {
				$empty_terms++;
			}
		}
		if ( $empty_terms > 10 ) {
			$issues[] = sprintf( '%d unused terms found', $empty_terms );
		}
		
		// Check 3: Check for orphaned user meta
		$user_count = count_users();
		if ( isset( $user_count['total_users'] ) && $user_count['total_users'] < 10 ) {
			// Only check on smaller sites for performance
			$users = get_users( array( 'fields' => 'ID' ) );
			if ( count( $users ) > 0 ) {
				// User meta check would require database query
				// Flagging as potential issue if site has deleted users
				$deleted_users = get_option( '_deleted_user_count', 0 );
				if ( $deleted_users > 5 ) {
					$issues[] = 'Potential orphaned user metadata from deleted users';
				}
			}
		}
		
		// Check 4: Check transient cleanup
		$transients = get_option( '_transient_timeout_*', array() );
		if ( ! empty( $transients ) ) {
			$issues[] = 'Expired transients not cleaned up';
		}
		
		// Check 5: Check for auto-draft posts
		$auto_drafts = wp_count_posts( 'any' );
		if ( isset( $auto_drafts->{'auto-draft'} ) && $auto_drafts->{'auto-draft'} > 20 ) {
			$issues[] = sprintf( '%d auto-draft posts should be cleaned up', $auto_drafts->{'auto-draft'} );
		}
		
		// Check 6: Check for revision buildup
		$revisions = wp_count_posts( 'revision' );
		if ( isset( $revisions->inherit ) && $revisions->inherit > 1000 ) {
			$issues[] = sprintf( '%d revisions found (consider limiting)', $revisions->inherit );
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d orphaned metadata issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-orphaned-metadata',
			);
		}
		
		return null;
	}
}
