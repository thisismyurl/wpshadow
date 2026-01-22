<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: User Session Table Bloat (DB-010)
 * 
 * Checks wp_usermeta for expired user sessions.
 * Philosophy: Drive to training (#6) - teach session management.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_User_Session_Table_Bloat extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Security check implementation
        // Check WordPress user session table for bloat
        global $wpdb;
        
        $session_table = $wpdb->prefix . 'user_meta';
        $session_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$session_table} WHERE meta_key LIKE 'wp_session%' OR meta_key LIKE '%_session%'"
        );
        
        if ($session_count && $session_count > 1000) {
            return array(
                'id' => 'user-session-table-bloat',
                'title' => sprintf(__('Large Number of Stored Sessions (%d)', 'wpshadow'), $session_count),
                'description' => __('Many sessions are stored in the database. Consider implementing session cleanup or Redis for better performance.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'performance',
                'kb_link' => 'https://wpshadow.com/kb/session-table-cleanup/',
                'training_link' => 'https://wpshadow.com/training/session-optimization/',
                'auto_fixable' => false,
                'threat_level' => 45,
            );
        }
        
        return null;
	}
}
