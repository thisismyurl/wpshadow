<?php

declare(strict_types=1);
/**
 * Unauthorized Admin Creation Detection Diagnostic
 *
 * @package WPShadow
 * @subpackage DiagnosticsFuture
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
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
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Unauthorized_Admin_Creation extends Diagnostic_Base
{

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
    public static function check(): ?array
    {
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
     * Live test for this diagnostic
     *
     * Diagnostic: Unauthorized Admin Creation Detection
     * Slug: unauthorized-admin-creation
     *
     * Test Purpose:
     * - Verify that check() method returns the correct result based on site state
     * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
     * - FAIL: check() returns array when diagnostic condition IS met (issue found)
     * - Description: Alerts when new admin/editor accounts are created unexpectedly.
     *
     * @return array {
     *     @type bool   $passed  Whether the test passed
     *     @type string $message Human-readable test result message
     * }
     */
    public static function test_live_unauthorized_admin_creation(): array
    {
        $result = self::check();

        // Get baseline and current admin/editor users
        $baseline = get_option('wpshadow_admin_baseline', array());
        $current_admins = get_users(array(
            'role__in' => array('administrator', 'editor'),
            'fields' => array('ID')
        ));

        $current_ids = wp_list_pluck($current_admins, 'ID');
        $new_accounts = array_diff($current_ids, $baseline);

        $has_issue = !empty($new_accounts) && !empty($baseline);
        $diagnostic_found_issue = !is_null($result);
        $test_passes = ($has_issue === $diagnostic_found_issue);

        return array(
            'passed' => $test_passes,
            'message' => $test_passes ? 'Unauthorized admin check matches site state' :
                "Mismatch: expected " . ($has_issue ? 'issue' : 'no issue') . " but got " .
                ($diagnostic_found_issue ? 'issue' : 'pass'),
        );
    }
}
