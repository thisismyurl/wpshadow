<?php
declare(strict_types=1);
/**
 * Unauthorized Admin Creation Detection Diagnostic
 *
 * @package WPShadow
 * @subpackage DiagnosticsFuture
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Unauthorized Admin Creation Detection
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Guardian module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "WPShadow detected a new admin account you didn't create"
 * 
 * @priority 1
 */
class Diagnostic_Unauthorized_Admin_Creation extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'unauthorized-admin-creation';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Unauthorized Admin Creation Detection';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Alerts when new admin/editor accounts are created unexpectedly.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        // Get baseline of admin/editor users
        $baseline = get_option('wpshadow_admin_baseline', array());
        
        // Get current admin/editor users
        $current_admins = get_users(array(
            'role__in' => array('administrator', 'editor'),
            'fields' => array('ID', 'user_login', 'user_email', 'user_registered')
        ));
        
        $current_ids = wp_list_pluck($current_admins, 'ID');
        
        // First run - establish baseline
        if (empty($baseline)) {
            update_option('wpshadow_admin_baseline', $current_ids);
            return null;
        }
        
        // Check for new admin/editor accounts
        $new_accounts = array_diff($current_ids, $baseline);
        
        if (empty($new_accounts)) {
            return null;
        }
        
        // Build details of new accounts
        $details = array();
        foreach ($new_accounts as $user_id) {
            $user = get_userdata($user_id);
            $details[] = sprintf(
                '%s (%s) - Created: %s',
                $user->user_login,
                $user->user_email,
                $user->user_registered
            );
        }
        
        return array(
            'id'           => static::$slug,
            'title'        => static::$title,
            'description'  => sprintf(
                '%d new admin/editor account(s) detected: %s',
                count($new_accounts),
                implode(', ', $details)
            ),
            'severity'     => 'critical',
            'category'     => 'security',
            'kb_link'      => 'https://wpshadow.com/kb/unauthorized-admins/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=unauthorized-admins',
            'training_link' => 'https://wpshadow.com/training/unauthorized-admins/',
            'auto_fixable' => false,
            'threat_level' => 100,
            'module'       => 'Guardian',
            'priority'     => 1,
        );
    }
    
    /**
     * IMPLEMENTATION PLAN
     * 
     * "Holy Shit" Moment: "WPShadow detected a new admin account you didn't create"
     * Revenue Path: Guardian
     * KB Article: https://wpshadow.com/kb/unauthorized-admins/
     * Training Video: https://wpshadow.com/training/unauthorized-admins/
     * 
     * Implementation Steps:
     * Track baseline of admin/editor user IDs on first run
     * Store in wp_options: wpshadow_admin_baseline
     * Check for new admin/editor accounts on each diagnostic run
     * Alert if new accounts detected
     * Show account details: username, email, created date, IP address (from user_meta)
     * Display user agent from creation event
     * One-click "Disable account" treatment
     * Email/SMS alert (SaaS tier)
     * Guardian module: Real-time monitoring
     * 
     * KPI Tracking:
     * - Time saved: [Calculate based on severity]
     * - Issues found: [Count of findings]
     * - Value delivered: [Show $ impact if applicable]
     * 
     * Treatment Options (Future):
     * - Free: Basic remediation steps (KB link)
     * - Guardian: Advanced automation + monitoring
     * 
     * Philosophy Compliance:
     * - Free detection: ✅ Always accessible
     * - Paid fixes: ✅ Module-gated advanced features
     * - Education: ✅ KB + Training links
     * - KPI: ✅ Track measurable value
     * - Talk-worthy: ✅ Creates "holy shit" moments
     */
}
