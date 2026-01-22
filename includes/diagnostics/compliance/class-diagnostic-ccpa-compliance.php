<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CCPA Compliance Status
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_CCPA_Compliance extends Diagnostic_Base {
    protected static $slug = 'ccpa-compliance';
    protected static $title = 'CCPA Compliance Status';
    protected static $description = 'Verifies California privacy law compliance.';

    public static function check(): ?array {
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
     * IMPLEMENTATION PLAN (Enterprise IT/Compliance Team)
     * 
     * What This Checks:
     * - [Technical implementation details]
     * 
     * Why It Matters:
     * - [Business value in plain English]
     * 
     * Success Criteria:
     * - [What "passing" means]
     * 
     * How to Fix:
     * - Step 1: [Clear instruction]
     * - Step 2: [Next step]
     * - KB Article: Detailed explanation and examples
     * - Training Video: Visual walkthrough
     * 
     * KPIs Tracked:
     * - Issues found and fixed
     * - Time saved (estimated minutes)
     * - Site health improvement %
     * - Business value delivered ($)
     */
}