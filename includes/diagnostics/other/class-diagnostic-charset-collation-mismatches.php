<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Charset/Collation Mismatches (DB-017)
 * 
 * Detects mixed character sets across tables.
 * Philosophy: Educate (#5) about character set importance.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Charset_Collation_Mismatches extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		global $wpdb;
		$target_charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
		$target_collate = defined('DB_COLLATE') && DB_COLLATE !== '' ? DB_COLLATE : $wpdb->collate;

		$mismatches = 0;
		$tables = $wpdb->get_results('SHOW TABLE STATUS', ARRAY_A);
		if (is_array($tables)) {
			foreach ($tables as $table) {
				$table_charset = isset($table['Collation']) ? $table['Collation'] : '';
				if ($table_charset && $target_collate && $table_charset !== $target_collate) {
					$mismatches++;
				}
			}
		}

		if ($mismatches > 0) {
			return array(
				'id' => 'charset-collation-mismatches',
				'title' => sprintf(__('Charset/collation mismatches found (%d tables)', 'wpshadow'), $mismatches),
				'description' => __('Tables use mixed charsets/collations. Align to a single charset (utf8mb4) to prevent index and sorting issues.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'other',
				'kb_link' => 'https://wpshadow.com/kb/charset-collation/',
				'training_link' => 'https://wpshadow.com/training/database-health/',
				'auto_fixable' => false,
				'threat_level' => 60,
				'mismatch_count' => $mismatches,
			);
		}

		return null;
	}
