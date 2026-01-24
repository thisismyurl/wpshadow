<?php

declare(strict_types=1);
/**
 * Unauthorized Admin Creation Detection Diagnostic
 *
 * @package WPShadow
 * @subpackage DiagnosticsFuture
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
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
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
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

        // Analyze creation method for each new account
        $suspicious_accounts = self::detect_suspicious_admin_creation($new_accounts);

        if (empty($suspicious_accounts)) {
            return null;
        }

        return array(
            'id'           => static::$slug,
            'title'        => static::$title,
            'description'  => sprintf(
                '%d admin/editor account(s) created via code detected: %s',
                count($suspicious_accounts),
                implode(', ', wp_list_pluck($suspicious_accounts, 'label'))
            ),
            'severity'     => 'critical',
            'category'     => 'security',
            'kb_link'      => 'https://wpshadow.com/kb/unauthorized-admins/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=unauthorized-admins',
            'training_link' => 'https://wpshadow.com/training/unauthorized-admins/',
            'auto_fixable' => false,
            'threat_level' => 100,
            'module'       => 'Guardian',
            'priority'     => 1,
            'suspicious_accounts' => $suspicious_accounts,
        );
    }

    /**
     * Detect admin accounts created via code vs web interface
     *
     * Admin accounts created via code (wp_insert_user, wp_create_user) may indicate:
     * - Unauthorized access / hacked installation
     * - Malicious plugin injecting admins
     * - Backdoor in theme/plugin code
     *
     * Legitimate web interface creation leaves traces in:
     * - User meta (created via admin screen)
     * - User registration data
     * - Action hooks logged in code
     *
     * @param array $user_ids Array of new user IDs to check
     * @return array Array of suspicious accounts with creation method analysis
     */
    private static function detect_suspicious_admin_creation(array $user_ids): array
    {
        $suspicious = array();

        foreach ($user_ids as $user_id) {
            $user = get_userdata($user_id);
            if (!$user) {
                continue;
            }

            $creation_method = self::analyze_user_creation_method($user);

            // If created via code (not web interface), flag as suspicious
            if ($creation_method['via_code']) {
                $suspicious[] = array(
                    'user_id'    => $user_id,
                    'user_login' => $user->user_login,
                    'user_email' => $user->user_email,
                    'registered' => $user->user_registered,
                    'method'     => $creation_method['method'],
                    'indicators' => $creation_method['indicators'],
                    'label'      => sprintf(
                        '%s (%s) - Created: %s via %s',
                        $user->user_login,
                        $user->user_email,
                        $user->user_registered,
                        $creation_method['method']
                    ),
                );
            }
        }

        return $suspicious;
    }

    /**
     * Analyze how a user was created
     *
     * Returns:
     * - via_code: true if created programmatically, false if via web interface
     * - method: description of creation method
     * - indicators: array of evidence used to determine creation method
     *
     * @param \WP_User $user User object
     * @return array Analysis results
     */
    private static function analyze_user_creation_method(\WP_User $user): array
    {
        $indicators = array();
        $is_code_created = false;

        // Check 1: User meta left by admin screen
        $admin_meta = get_user_meta($user->ID, 'admin_color', true);
        if (!empty($admin_meta)) {
            $indicators[] = 'admin_color meta present (web interface)';
        } else {
            $indicators[] = 'no admin_color meta (possible code creation)';
            $is_code_created = true;
        }

        // Check 2: Last login meta
        $last_login = get_user_meta($user->ID, 'wp_last_login', true);
        if (empty($last_login)) {
            $indicators[] = 'no login activity (newly created)';
        }

        // Check 3: User notification sent flag (set during web admin creation)
        $user_notification = get_user_meta($user->ID, '_wpshadow_user_created_notified', true);
        if (empty($user_notification)) {
            $indicators[] = 'no creation notification meta';
            $is_code_created = true;
        }

        // Check 4: Account age vs registration time
        $user_registered = strtotime($user->user_registered);
        $now = time();
        $age_seconds = $now - $user_registered;

        // If account is extremely new (less than 5 minutes old) and no login, suspicious
        if ($age_seconds < 300 && empty($last_login)) {
            $indicators[] = 'account less than 5 minutes old';
        }

        // Check 5: Email verification status (WordPress doesn't verify by default, but some plugins do)
        $email_verified = get_user_meta($user->ID, 'email_verified', true);
        if (empty($email_verified)) {
            $indicators[] = 'email not verified';
        }

        // Determine creation method
        if ($is_code_created) {
            $method = 'Code execution (programmatic)';
        } else {
            $method = 'Web interface (admin panel)';
        }

        return array(
            'via_code'   => $is_code_created,
            'method'     => $method,
            'indicators' => $indicators,
        );
    }

    /**
     * Live test for this diagnostic
     *
     * Tests detection of admin accounts created via code vs web interface.
     *
     * @return array {
     *     @type bool   $passed  Whether the test passed
     *     @type string $message Human-readable test result message
     * }
     */
    public static function test_live_unauthorized_admin_creation(): array
    {
        // Run the full diagnostic check
        $result = self::check();

        if (is_null($result)) {
            return array(
                'passed' => true,
                'message' => '✓ No suspicious admin account creation detected (baseline check)',
            );
        }

        // Found suspicious accounts created via code
        $suspicious_count = count($result['suspicious_accounts']);
        return array(
            'passed' => false,
            'message' => sprintf(
                '✗ %d admin account(s) detected created via code: %s',
                $suspicious_count,
                implode('; ', wp_list_pluck($result['suspicious_accounts'], 'method'))
            ),
        );
    }
}
