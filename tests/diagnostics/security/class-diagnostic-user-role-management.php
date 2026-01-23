<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: User Role Configuration Review
 *
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Role_Management extends Diagnostic_Base
{
    protected static $slug = 'user-role-management';
    protected static $title = 'User Role Configuration Review';
    protected static $description = 'Audits custom roles and capabilities.';


    public static function check(): ?array
    {
        global $wp_roles;

        // Get all roles
        $all_roles = $wp_roles->roles;
        $default_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');

        // Find custom roles
        $custom_roles = array();
        foreach ($all_roles as $role_key => $role_info) {
            if (!in_array($role_key, $default_roles)) {
                $custom_roles[] = $role_key;
            }
        }

        // Check for users with administrator capability
        $admins = get_users(array('role' => 'administrator'));

        // Flag if too many admins or suspicious custom roles
        $issues = array();

        if (count($admins) > 5) {
            $issues[] = sprintf('%d administrator accounts (consider reducing)', count($admins));
        }

        if (count($custom_roles) > 0) {
            $issues[] = sprintf('%d custom role(s): %s', count($custom_roles), implode(', ', array_slice($custom_roles, 0, 3)));
        }

        if (empty($issues)) {
            return null;
        }

        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'User role review recommended: ' . implode('; ', $issues),
            'severity'      => 'low',
            'category'      => 'security',
            'kb_link'       => 'https://wpshadow.com/kb/user-role-management/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=user-role-management',
            'training_link' => 'https://wpshadow.com/training/user-role-management/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Security',
            'priority'      => 1,
        );
    }



    /**
     * Live test for this diagnostic
     *
     * Diagnostic: User Role Configuration Review
     * Slug: user-role-management
     *
     * Test Purpose:
     * - Verify that check() method returns the correct result based on site state
     * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
     * - FAIL: check() returns array when diagnostic condition IS met (issue found)
     * - Description: Audits custom roles and capabilities.
     *
     * @return array {
     *     @type bool   $passed  Whether the test passed
     *     @type string $message Human-readable test result message
     * }
     */
    public static function test_live_user_role_management(): array
    {
        $result = self::check();

        global $wp_roles;
        $all_roles = $wp_roles->roles;
        $default_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');

        $custom_roles = array();
        foreach ($all_roles as $role_key => $role_info) {
            if (!in_array($role_key, $default_roles)) {
                $custom_roles[] = $role_key;
            }
        }

        $admins = get_users(array('role' => 'administrator'));
        $has_issue = (count($admins) > 5) || (count($custom_roles) > 0);
        $diagnostic_found_issue = !is_null($result);
        $test_passes = ($has_issue === $diagnostic_found_issue);

        return array(
            'passed' => $test_passes,
            'message' => $test_passes ? 'User role check matches site state' :
                "Mismatch: expected " . ($has_issue ? 'issue' : 'no issue') . " but got " .
                ($diagnostic_found_issue ? 'issue' : 'pass'),
        );
    }
}
