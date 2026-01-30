<?php
/**
 * Qtranslate X Database Migration Diagnostic
 *
 * Qtranslate X Database Migration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1177.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Qtranslate X Database Migration Diagnostic Class
 *
 * @since 1.1177.0000
 */
class Diagnostic_QtranslateXDatabaseMigration extends Diagnostic_Base {

	protected static $slug = 'qtranslate-x-database-migration';
	protected static $title = 'Qtranslate X Database Migration';
	protected static $description = 'Qtranslate X Database Migration misconfigured';
	protected static $family = 'performance';

	public static function check() {
		// Check for qTranslate-X or similar plugins
		$has_qtranslate = function_exists( 'qtranxf_convertURL' ) ||
		                  defined( 'QTX_VERSION' ) ||
		                  get_option( 'qtranslate_enabled', '' ) !== '';
		
		if ( ! $has_qtranslate ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Legacy format posts
		$legacy_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%[:en]%' OR post_content LIKE '%[en]%'"
		);
		if ( $legacy_posts > 0 ) {
			$issues[] = sprintf( __( '%d posts with legacy format (migration pending)', 'wpshadow' ), $legacy_posts );
		}
		
		// Check 2: Meta field translations
		$meta_translations = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE '%[:en]%'"
		);
		if ( $meta_translations > 0 ) {
			$issues[] = sprintf( __( '%d meta fields need migration', 'wpshadow' ), $meta_translations );
		}
		
		// Check 3: Database charset
		$charset = $wpdb->get_var( "SELECT @@character_set_database" );
		if ( 'utf8mb4' !== $charset ) {
			$issues[] = sprintf( __( 'Database charset %s (utf8mb4 recommended)', 'wpshadow' ), $charset );
		}
		
		// Check 4: Migration backup
		$backup_exists = get_option( 'qtranslate_migration_backup', 'no' );
		if ( 'no' === $backup_exists ) {
			$issues[] = __( 'No migration backup (data loss risk)', 'wpshadow' );
		}
		
		// Check 5: Orphaned translations
		$orphaned = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_qtranslate_%' 
			 AND post_id NOT IN (SELECT ID FROM {$wpdb->posts})"
		);
		if ( $orphaned > 100 ) {
			$issues[] = sprintf( __( '%d orphaned translations (cleanup needed)', 'wpshadow' ), $orphaned );
		}
		
		// Check 6: Migration status
		$migration_status = get_option( 'qtranslate_migration_status', 'pending' );
		if ( 'pending' === $migration_status ) {
			$issues[] = __( 'Migration pending (incomplete setup)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 67;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 61;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'qTranslate-X has %d database migration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/qtranslate-x-database-migration',
		);
	}
}
