<?php
/**
 * Wpml Translation Memory Database Diagnostic
 *
 * Wpml Translation Memory Database misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1138.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpml Translation Memory Database Diagnostic Class
 *
 * @since 1.1138.0000
 */
class Diagnostic_WpmlTranslationMemoryDatabase extends Diagnostic_Base {

	protected static $slug = 'wpml-translation-memory-database';
	protected static $title = 'Wpml Translation Memory Database';
	protected static $description = 'Wpml Translation Memory Database misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return null;
		}
		
		// Check if WPML is active
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) && ! class_exists( 'SitePress' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check translation memory table
		$tm_table = $wpdb->prefix . 'icl_translation_status';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$tm_table}'" );
		
		if ( ! $table_exists ) {
			return null;
		}

		// Check table size
		$table_size = $wpdb->get_var(
			"SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
			 FROM information_schema.TABLES 
			 WHERE table_schema = DATABASE() 
			 AND table_name = '{$tm_table}'"
		);
		if ( $table_size > 500 ) { // Over 500MB
			$issues[] = 'translation_memory_oversized';
			$threat_level += 30;
		}

		// Check old translations
		$old_translations = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$tm_table} 
			 WHERE timestamp < DATE_SUB(NOW(), INTERVAL 2 YEAR)"
		);
		if ( $old_translations > 10000 ) {
			$issues[] = 'old_translations_not_cleaned';
			$threat_level += 25;
		}

		// Check database indexes
		$indexes = $wpdb->get_results( "SHOW INDEX FROM {$tm_table}" );
		$has_status_index = false;
		foreach ( $indexes as $index ) {
			if ( $index->Key_name === 'status' ) {
				$has_status_index = true;
				break;
			}
		}
		if ( ! $has_status_index ) {
			$issues[] = 'missing_translation_indexes';
			$threat_level += 25;
		}

		// Check cleanup automation
		$auto_cleanup = get_option( 'wpml_auto_cleanup_translation_memory', 0 );
		if ( ! $auto_cleanup ) {
			$issues[] = 'automatic_cleanup_disabled';
			$threat_level += 20;
		}

		// Check translation memory limit
		$tm_limit = get_option( 'icl_translation_memory_limit', 0 );
		if ( $tm_limit === 0 ) {
			$issues[] = 'no_memory_limit_configured';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of database issues */
				__( 'WPML Translation Memory database has performance problems: %s. This causes slow queries and database bloat.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-translation-memory-database',
			);
		}
		
		return null;
	}
}
