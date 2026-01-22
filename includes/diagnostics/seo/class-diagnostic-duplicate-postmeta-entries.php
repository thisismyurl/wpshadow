<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Duplicate Postmeta Entries (DB-009)
 * 
 * Finds identical meta_key/meta_value pairs for same post_id.
 * Philosophy: Helpful neighbor (#1) - catch data integrity issues.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Duplicate_Postmeta_Entries extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for duplicate postmeta entries
        global $wpdb;
        
        // Find postmeta entries with multiple values for same meta_key and post_id
        $duplicates = $wpdb->get_var(
            "SELECT COUNT(*) FROM (SELECT post_id, meta_key, COUNT(*) as cnt FROM {$wpdb->postmeta} GROUP BY post_id, meta_key HAVING cnt > 1) as t"
        );
        
        if ($duplicates && $duplicates > 50) {
            return array(
                'id' => 'duplicate-postmeta-entries',
                'title' => sprintf(__('%d Duplicate Postmeta Entries Found', 'wpshadow'), $duplicates),
                'description' => __('Duplicate postmeta entries slow down queries. Use WPShadow Pro cleanup tools to consolidate duplicates.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/postmeta-optimization/',
                'training_link' => 'https://wpshadow.com/training/database-cleanup/',
                'auto_fixable' => false,
                'threat_level' => 50,
            );
        }
        return null;
}
}
