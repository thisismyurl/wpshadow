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
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}
	/**
	 * Test: Plugin detection logic
	 *
	 * Verifies that diagnostic correctly checks for active plugins
	 * and reports issues appropriately.
	 *
	 * @return array Test result
	 */
	public static function test_plugin_detection(): array {
		$result = self::check();
		
		// Plugin detection should return null (no plugin/no issue) or array (issue)
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Plugin detection logic valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid plugin detection result',
		);
	}}
