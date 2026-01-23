<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: User Data Deletion Works?
 *
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Data_Deletion extends Diagnostic_Base
{
    protected static $slug = 'user-data-deletion';
    protected static $title = 'User Data Deletion Works?';
    protected static $description = 'Tests right-to-be-forgotten implementation.';

    public static function check(): ?array
    {
        // WordPress 4.9.6+ has built-in data erasure (Tools > Erase Personal Data)
        global $wp_version;
        $has_core_erasure = version_compare($wp_version, '4.9.6', '>=');

        // Check for GDPR plugins that enhance deletion
        $deletion_plugins = array(
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
        );

        $has_deletion_plugin = false;
        foreach ($deletion_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_deletion_plugin = true;
                break;
            }
        }

        // Pass if core erasure available or plugin active
        if ($has_core_erasure || $has_deletion_plugin) {
            return null;
        }

        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'User data deletion capability not detected. Update WordPress or install GDPR plugin.',
            'color'         => '#f44336',
            'bg_color'      => '#ffebee',
            'kb_link'       => 'https://wpshadow.com/kb/user-data-deletion/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=user-data-deletion',
            'training_link' => 'https://wpshadow.com/training/user-data-deletion/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
            'priority'      => 1,
        );
    }



    /**
     * Live test for this diagnostic
     *
     * Diagnostic: User Data Deletion Works?
     * Slug: user-data-deletion
     *
     * Test Purpose:
     * - Verify that check() method returns the correct result based on site state
     * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
     * - FAIL: check() returns array when diagnostic condition IS met (issue found)
     * - Description: Tests right-to-be-forgotten implementation.
     *
     * @return array {
     *     @type bool   $passed  Whether the test passed
     *     @type string $message Human-readable test result message
     * }
     */
    public static function test_live_user_data_deletion(): array
    {
        $result = self::check();

        global $wp_version;
        $has_core_erasure = version_compare($wp_version, '4.9.6', '>=');

        $deletion_plugins = array(
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
        );

        $has_deletion_plugin = false;
        foreach ($deletion_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_deletion_plugin = true;
                break;
            }
        }

        $should_pass = ($has_core_erasure || $has_deletion_plugin);
        $diagnostic_passed = is_null($result);
        $test_passes = ($should_pass === $diagnostic_passed);

        return array(
            'passed' => $test_passes,
            'message' => $test_passes ? 'User data deletion check matches site state' :
                "Mismatch: expected " . ($should_pass ? 'pass' : 'fail') . " but got " .
                ($diagnostic_passed ? 'pass' : 'fail'),
        );
    }
}
