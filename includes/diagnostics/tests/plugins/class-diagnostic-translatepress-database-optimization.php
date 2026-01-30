<?php
/**
 * Translatepress Database Optimization Diagnostic
 *
 * Translatepress Database Optimization misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1155.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatepress Database Optimization Diagnostic Class
 *
 * @since 1.1155.0000
 */
class Diagnostic_TranslatepressDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'translatepress-database-optimization';
	protected static $title = 'Translatepress Database Optimization';
	protected static $description = 'Translatepress Database Optimization misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$settings = get_option( 'trp_settings', array() );
		
		// Check 1: DB optimization enabled
		$db_opt = isset( $settings['db_optimization'] ) ? (bool) $settings['db_optimization'] : false;
		if ( ! $db_opt ) {
			$issues[] = 'Database optimization not enabled';
		}
		
		// Check 2: Cleanup unused translations
		$cleanup_unused = isset( $settings['cleanup_unused_translations'] ) ? (bool) $settings['cleanup_unused_translations'] : false;
		if ( ! $cleanup_unused ) {
			$issues[] = 'Cleanup of unused translations not enabled';
		}
		
		// Check 3: String limit configured
		$string_limit = isset( $settings['db_string_limit'] ) ? absint( $settings['db_string_limit'] ) : 0;
		if ( $string_limit <= 0 ) {
			$issues[] = 'String limit not configured';
		}
		
		// Check 4: Table defragmentation
		$defrag = isset( $settings['table_defragmentation'] ) ? (bool) $settings['table_defragmentation'] : false;
		if ( ! $defrag ) {
			$issues[] = 'Table defragmentation not enabled';
		}
		
		// Check 5: Index optimization
		$index_opt = isset( $settings['index_optimization'] ) ? (bool) $settings['index_optimization'] : false;
		if ( ! $index_opt ) {
			$issues[] = 'Index optimization not enabled';
		}
		
		// Check 6: Backup before optimization
		$backup = isset( $settings['backup_before_optimization'] ) ? (bool) $settings['backup_before_optimization'] : false;
		if ( ! $backup ) {
			$issues[] = 'Backup before optimization not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d TranslatePress DB optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-database-optimization',
			);
		}
		
		return null;
	}
}
