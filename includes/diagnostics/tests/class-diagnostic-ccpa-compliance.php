<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CCPA Compliance Status
 *
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_CCPA_Compliance extends Diagnostic_Base
{
    protected static $slug = 'ccpa-compliance';
    protected static $title = 'CCPA Compliance Status';
    protected static $description = 'Verifies California privacy law compliance.';

    public static function check(): ?array
    {
        // CCPA requires privacy policy, data export/deletion capabilities
        $privacy_policy_id = (int) get_option('wp_page_for_privacy_policy', 0);
        $has_privacy_page = ($privacy_policy_id > 0 && get_post_status($privacy_policy_id) === 'publish');

        // Check for CCPA/privacy plugins
        $ccpa_plugins = array(
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
            'cookie-notice/cookie-notice.php',
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
        );

        $has_privacy_plugin = false;
        foreach ($ccpa_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_privacy_plugin = true;
                break;
            }
        }

        // Pass if privacy page exists and plugin active
        if ($has_privacy_page && $has_privacy_plugin) {
            return null;
        }

        $issues = array();
        if (!$has_privacy_page) {
            $issues[] = 'Privacy policy page not configured';
        }
        if (!$has_privacy_plugin) {
            $issues[] = 'No privacy/consent management plugin detected';
        }

        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => static::$description . ' Issues found: ' . implode(', ', $issues),
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/ccpa-compliance/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=ccpa-compliance',
            'training_link' => 'https://wpshadow.com/training/ccpa-compliance/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
            'priority'      => 1,
        );
    }



    /**
     * Live test for this diagnostic
     *
     * Diagnostic: CCPA Compliance Status
     * Slug: ccpa-compliance
     *
     * Test Purpose:
     * - Verify that check() method returns the correct result based on site state
     * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
     * - FAIL: check() returns array when diagnostic condition IS met (issue found)
     * - Description: Verifies California privacy law compliance.
     *
     * @return array {
     *     @type bool   $passed  Whether the test passed
     *     @type string $message Human-readable test result message
     * }
     */
    public static function test_live_ccpa_compliance(): array
    {
        // Get diagnostic result
        $result = self::check();

        // Evaluate actual site state
        $privacy_policy_id = (int) get_option('wp_page_for_privacy_policy', 0);
        $has_privacy_page = ($privacy_policy_id > 0 && get_post_status($privacy_policy_id) === 'publish');

        // Check for CCPA/privacy plugins
        $ccpa_plugins = array(
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
            'cookie-notice/cookie-notice.php',
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
        );

        $has_privacy_plugin = false;
        foreach ($ccpa_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_privacy_plugin = true;
                break;
            }
        }

        // Determine expected diagnostic state
        $should_pass = ($has_privacy_page && $has_privacy_plugin);
        $diagnostic_passed = is_null($result);
        $test_passes = ($should_pass === $diagnostic_passed);

        return array(
            'passed' => $test_passes,
            'message' => $test_passes ? 'CCPA compliance check matches site state' :
                "Mismatch: expected " . ($should_pass ? 'pass' : 'fail') .
                " (privacy_page=$has_privacy_page, plugin=$has_privacy_plugin) but got " .
                ($diagnostic_passed ? 'pass' : 'fail'),
        );
    }
}
