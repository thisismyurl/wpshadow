<?php
declare(strict_types=1);
/**
 * No Two-Factor Authentication Diagnostic
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
 * No Two-Factor Authentication
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Free + Guardian module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "0% of your admin accounts use two-factor authentication"
 * 
 * @priority 2
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_No_Two_Factor_Auth extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'no-two-factor-auth';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'No Two-Factor Authentication';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Checks if 2FA is enabled for admin/editor accounts.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        // Get all admin/editor users
        $privileged_users = get_users(array(
            'role__in' => array('administrator', 'editor'),
            'fields' => array('ID', 'user_login'),
        ));
        
        if (empty($privileged_users)) {
            return null;
        }
        
        $total = count($privileged_users);
        $with_2fa = 0;
        
        // Check if any popular 2FA plugins are active
        $twofa_plugins = array(
            'two-factor/two-factor.php',
            'wordfence/wordfence.php',
            'google-authenticator/google-authenticator.php',
            'two-factor-authentication/two-factor-authentication.php',
        );
        
        $has_2fa_plugin = false;
        foreach ($twofa_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_2fa_plugin = true;
                break;
            }
        }
        
        // If 2FA plugin exists, check user meta for enabled users
        if ($has_2fa_plugin) {
            foreach ($privileged_users as $user) {
                // Check common 2FA meta keys
                if (get_user_meta($user->ID, '_two_factor_enabled', true) ||
                    get_user_meta($user->ID, 'two-factor-enabled', true) ||
                    get_user_meta($user->ID, 'wf2fa', true)) {
                    $with_2fa++;
                }
            }
        }
        
        // If no 2FA plugin or less than 50% adoption, flag it
        if (!$has_2fa_plugin || ($with_2fa / $total) < 0.5) {
            $percentage = $has_2fa_plugin ? round(($with_2fa / $total) * 100) : 0;
            
            return array(
                'id'           => static::$slug,
                'title'        => static::$title,
                'description'  => sprintf(
                    'Only %d%% of privileged accounts (%d/%d) use two-factor authentication',
                    $percentage,
                    $with_2fa,
                    $total
                ),
                'severity'     => 'high',
                'category'     => 'security',
                'kb_link'      => 'https://wpshadow.com/kb/two-factor-auth/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=two-factor-auth',
                'training_link' => 'https://wpshadow.com/training/two-factor-auth/',
                'auto_fixable' => false,
                'threat_level' => 80,
                'module'       => 'Free + Guardian',
                'priority'     => 2,
            );
        }
        
        return null;
    }




	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: No Two-Factor Authentication
	 * Slug: no-two-factor-auth
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if 2FA is enabled for admin/editor accounts.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_no_two_factor_auth(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}
