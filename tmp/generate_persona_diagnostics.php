<?php
/**
 * Generate persona-focused diagnostic stubs
 * Philosophy: Helpful neighbor, education-first, show value
 */

$base = __DIR__ . '/../includes/diagnostics/new';

// Define diagnostics by persona with friendly titles and descriptions
$defs = [
    'BasicSiteHealth' => [ // Mom's diagnostics
        ['site-actually-loading', 'Site_Actually_Loading', 'Is Your Site Actually Loading?', 'Checks if your homepage loads successfully for visitors.', 'Core', 1],
        ['backups-configured', 'Backups_Configured', 'Are Backups Set Up?', 'Verifies automatic backup system is configured.', 'Core', 1],
        ['backups-working', 'Backups_Working', 'Are Backups Actually Working?', 'Tests if recent backups completed successfully.', 'Core', 1],
        ['contact-form-working', 'Contact_Form_Working', 'Is Contact Form Working?', 'Tests if contact form submissions reach you.', 'Core', 1],
        ['ssl-valid', 'SSL_Valid', 'Is SSL Certificate Valid?', 'Checks if your site has a working security certificate.', 'Security', 1],
        ['ssl-expiring-soon', 'SSL_Expiring_Soon', 'SSL Certificate Expiring Soon?', 'Warns if SSL certificate expires within 30 days.', 'Security', 1],
        ['updates-available', 'Updates_Available', 'Updates Waiting to Install?', 'Lists WordPress, plugin, and theme updates available.', 'Core', 1],
        ['site-down-detector', 'Site_Down_Detector', 'Is Site Currently Down?', 'External check to verify site is reachable.', 'Core', 1],
        ['images-too-large', 'Images_Too_Large', 'Are Images Slowing Site Down?', 'Finds unoptimized images over 300KB.', 'Performance', 1],
        ['plugins-conflicting', 'Plugins_Conflicting', 'Plugins Causing Conflicts?', 'Detects JavaScript errors from plugin conflicts.', 'Core', 1],
        ['spam-blocking-real-comments', 'Spam_Blocking_Real_Comments', 'Spam Filter Too Aggressive?', 'Checks if legitimate comments are being blocked.', 'Core', 2],
        ['login-works', 'Login_Works', 'Can You Still Log In?', 'Tests admin login functionality.', 'Security', 1],
        ['forgot-password-works', 'Forgot_Password_Works', 'Password Reset Working?', 'Verifies password reset emails are sent.', 'Core', 1],
        ['email-delivery-working', 'Email_Delivery_Working', 'Are Emails Being Delivered?', 'Tests if WordPress can send emails successfully.', 'Core', 1],
        ['mobile-friendly-basic', 'Mobile_Friendly_Basic', 'Does Site Work on Phones?', 'Checks mobile-friendliness and viewport settings.', 'Design', 1],
        ['broken-images', 'Broken_Images', 'Any Broken Images?', 'Scans for missing or broken image files.', 'Content', 1],
    ],
    'SmallBusiness' => [ // Local business diagnostics
        ['local-seo-schema', 'Local_SEO_Schema', 'Local Business Schema Markup', 'Checks for LocalBusiness schema to appear in Google.', 'SEO', 1],
        ['google-business-profile', 'Google_Business_Profile', 'Google Business Profile Integration', 'Verifies Google Business Profile is linked/embedded.', 'SEO', 1],
        ['business-hours-display', 'Business_Hours_Display', 'Business Hours Visible?', 'Checks if operating hours are prominently displayed.', 'Content', 1],
        ['contact-info-visible', 'Contact_Info_Visible', 'Contact Info Easy to Find?', 'Verifies phone/address visible on every page.', 'Content', 1],
        ['mobile-experience-score', 'Mobile_Experience_Score', 'Mobile Experience Quality', 'Scores mobile usability for local search traffic.', 'Performance', 1],
        ['page-speed-score', 'Page_Speed_Score', 'Page Speed Score', 'Measures Google PageSpeed Insights score.', 'Performance', 1],
        ['appointment-system-working', 'Appointment_System_Working', 'Booking System Functional?', 'Tests appointment/booking form submissions.', 'Commerce', 1],
        ['payment-gateway-functional', 'Payment_Gateway_Functional', 'Payment Processing Working?', 'Verifies payment gateway connectivity.', 'Commerce', 1],
        ['review-display', 'Review_Display', 'Customer Reviews Displayed?', 'Checks if testimonials/reviews are visible.', 'Content', 1],
        ['service-area-pages', 'Service_Area_Pages', 'Service Area Pages Created?', 'Verifies location-specific pages exist.', 'SEO', 2],
        ['emergency-contact-info', 'Emergency_Contact_Info', '24/7 Contact Info Visible?', 'Checks for emergency/after-hours contact.', 'Content', 2],
        ['call-tracking-working', 'Call_Tracking_Working', 'Phone Call Tracking Active?', 'Verifies click-to-call and tracking working.', 'Marketing', 2],
        ['map-embed-working', 'Map_Embed_Working', 'Google Maps Embedded?', 'Checks if location map is embedded and working.', 'Content', 1],
        ['social-proof-visible', 'Social_Proof_Visible', 'Social Proof Displayed?', 'Verifies trust signals (reviews, certs, awards).', 'Content', 2],
        ['customer-testimonials', 'Customer_Testimonials', 'Customer Testimonials Present?', 'Checks for authentic customer testimonials.', 'Content', 2],
        ['before-after-gallery', 'Before_After_Gallery', 'Before/After Gallery Present?', 'Looks for work portfolio or transformations.', 'Content', 2],
    ],
    'Corporate' => [ // Enterprise diagnostics
        ['gdpr-compliance', 'GDPR_Compliance', 'GDPR Compliance Status', 'Checks consent management and data protection.', 'Compliance', 1],
        ['ccpa-compliance', 'CCPA_Compliance', 'CCPA Compliance Status', 'Verifies California privacy law compliance.', 'Compliance', 1],
        ['wcag-accessibility-score', 'WCAG_Accessibility_Score', 'WCAG 2.1 AA Compliance', 'Measures accessibility compliance level.', 'Compliance', 1],
        ['brand-consistency', 'Brand_Consistency', 'Brand Style Guide Compliance', 'Checks colors, fonts, logos match brand guide.', 'Design', 2],
        ['multisite-health-check', 'Multisite_Health_Check', 'Multisite Network Health', 'Monitors health across all network sites.', 'Core', 1],
        ['user-role-management', 'User_Role_Management', 'User Role Configuration Review', 'Audits custom roles and capabilities.', 'Security', 1],
        ['audit-logging-active', 'Audit_Logging_Active', 'Audit Trail Logging Active?', 'Verifies user action logging is enabled.', 'Security', 1],
        ['disaster-recovery-plan', 'Disaster_Recovery_Plan', 'Disaster Recovery Readiness', 'Tests backup restore and recovery procedures.', 'Core', 1],
        ['sla-monitoring', 'SLA_Monitoring', 'SLA Uptime Monitoring', 'Tracks uptime against service level agreement.', 'Monitoring', 1],
        ['performance-at-scale', 'Performance_At_Scale', 'Performance Under Load', 'Tests response time with simulated traffic.', 'Performance', 1],
        ['security-hardening-score', 'Security_Hardening_Score', 'Security Hardening Checklist', 'Scores security best practices implementation.', 'Security', 1],
        ['integration-health', 'Integration_Health', 'API Integration Health', 'Monitors CRM, ERP, and third-party integrations.', 'Core', 1],
        ['data-retention-policy', 'Data_Retention_Policy', 'Data Retention Policy Set?', 'Checks if data retention schedule is configured.', 'Compliance', 2],
        ['privacy-policy-current', 'Privacy_Policy_Current', 'Privacy Policy Up to Date?', 'Verifies privacy policy updated within 12 months.', 'Compliance', 1],
        ['terms-of-service-current', 'Terms_Of_Service_Current', 'Terms of Service Current?', 'Checks if ToS updated within 12 months.', 'Compliance', 2],
        ['cookie-consent-compliant', 'Cookie_Consent_Compliant', 'Cookie Consent Compliant?', 'Verifies cookie banner meets regulations.', 'Compliance', 1],
        ['user-data-export', 'User_Data_Export', 'User Data Export Available?', 'Tests GDPR data export functionality.', 'Compliance', 1],
        ['user-data-deletion', 'User_Data_Deletion', 'User Data Deletion Works?', 'Tests right-to-be-forgotten implementation.', 'Compliance', 1],
    ],
    'MarketingAgency' => [ // Agency diagnostics
        ['analytics-tracking-active', 'Analytics_Tracking_Active', 'Analytics Tracking Active?', 'Verifies analytics code is firing correctly.', 'Marketing', 1],
        ['ga4-installed', 'GA4_Installed', 'Google Analytics 4 Installed?', 'Checks if GA4 tracking is configured.', 'Marketing', 1],
        ['gtm-installed', 'GTM_Installed', 'Google Tag Manager Installed?', 'Verifies GTM container is present and firing.', 'Marketing', 1],
        ['conversion-tracking-active', 'Conversion_Tracking_Active', 'Conversion Tracking Working?', 'Tests if goal completions are tracked.', 'Marketing', 1],
        ['ab-testing-setup', 'AB_Testing_Setup', 'A/B Testing Configured?', 'Checks if split testing tools are active.', 'Marketing', 2],
        ['seo-meta-tags-complete', 'SEO_Meta_Tags_Complete', 'SEO Meta Tags Complete?', 'Verifies title, description, OG tags present.', 'SEO', 1],
        ['social-media-integration', 'Social_Media_Integration', 'Social Media Integration Active?', 'Checks social sharing and profile links.', 'Marketing', 1],
        ['email-capture-working', 'Email_Capture_Working', 'Email Capture Forms Working?', 'Tests newsletter signup and lead magnets.', 'Marketing', 1],
        ['landing-page-speed', 'Landing_Page_Speed', 'Landing Page Load Speed', 'Measures speed of conversion-critical pages.', 'Performance', 1],
        ['utm-parameters-preserved', 'UTM_Parameters_Preserved', 'UTM Parameters Tracked?', 'Verifies campaign tracking parameters work.', 'Marketing', 1],
        ['facebook-pixel-firing', 'Facebook_Pixel_Firing', 'Facebook Pixel Firing?', 'Tests Meta/Facebook pixel installation.', 'Marketing', 1],
        ['google-ads-tracking', 'Google_Ads_Tracking', 'Google Ads Conversion Tracking?', 'Verifies Google Ads remarketing tag.', 'Marketing', 1],
        ['heatmap-tracking-active', 'Heatmap_Tracking_Active', 'Heatmap/Recording Tools Active?', 'Checks for Hotjar, Clarity, etc.', 'Marketing', 2],
        ['lead-magnet-delivery', 'Lead_Magnet_Delivery', 'Lead Magnet Delivery Working?', 'Tests automated content delivery.', 'Marketing', 1],
        ['thank-you-page-tracking', 'Thank_You_Page_Tracking', 'Thank You Page Tracking?', 'Verifies conversion confirmation tracking.', 'Marketing', 1],
    ],
    'Hosting' => [ // Hosting company diagnostics
        ['resource-usage-monitor', 'Resource_Usage_Monitor', 'Server Resource Usage', 'Monitors CPU, memory, disk I/O usage.', 'System', 1],
        ['cpu-spikes-detected', 'CPU_Spikes_Detected', 'CPU Spike Detection', 'Alerts on unusual CPU usage patterns.', 'System', 1],
        ['memory-usage-threshold', 'Memory_Usage_Threshold', 'Memory Usage Monitoring', 'Tracks PHP and MySQL memory consumption.', 'System', 1],
        ['database-optimization-needed', 'Database_Optimization_Needed', 'Database Optimization Needed?', 'Identifies tables needing optimization.', 'Performance', 1],
        ['caching-effectiveness', 'Caching_Effectiveness', 'Cache Hit Rate Analysis', 'Measures object cache and page cache efficiency.', 'Performance', 1],
        ['php-version-compatible', 'PHP_Version_Compatible', 'PHP Version Compatibility', 'Checks for deprecated code in current PHP.', 'System', 1],
        ['server-config-optimal', 'Server_Config_Optimal', 'Server Configuration Optimized?', 'Reviews PHP/MySQL settings for performance.', 'System', 1],
        ['malware-scan-active', 'Malware_Scan_Active', 'Malware Scanning Active?', 'Verifies malware detection is enabled.', 'Security', 1],
        ['backup-completion-rate', 'Backup_Completion_Rate', 'Backup Success Rate', 'Tracks backup completion reliability.', 'Core', 1],
        ['uptime-monitoring-active', 'Uptime_Monitoring_Active', 'Uptime Monitoring Enabled?', 'Checks external uptime monitoring.', 'Monitoring', 1],
        ['traffic-spike-handling', 'Traffic_Spike_Handling', 'Traffic Spike Readiness', 'Tests if site handles sudden traffic surges.', 'Performance', 1],
        ['error-log-patterns', 'Error_Log_Patterns', 'Error Log Pattern Analysis', 'Identifies recurring errors in logs.', 'System', 1],
        ['disk-space-monitor', 'Disk_Space_Monitor', 'Disk Space Monitoring', 'Alerts when disk usage exceeds thresholds.', 'System', 1],
        ['inode-usage-monitor', 'Inode_Usage_Monitor', 'Inode Usage Monitoring', 'Tracks file count against inode limits.', 'System', 2],
        ['slow-query-detection', 'Slow_Query_Detection', 'Slow Database Query Detection', 'Identifies queries over 2 seconds.', 'Performance', 1],
    ],
    'WordPressVIP' => [ // Enterprise WordPress platform diagnostics
        ['vip-go-compatibility', 'VIP_Go_Compatibility', 'WordPress VIP Compatibility', 'Checks code against VIP coding standards.', 'Compatibility', 2],
        ['jetpack-integration', 'Jetpack_Integration', 'Jetpack Integration Health', 'Monitors Jetpack feature functionality.', 'Integration', 2],
        ['woocommerce-optimization', 'WooCommerce_Optimization', 'WooCommerce Performance Optimized?', 'Checks WooCommerce query optimization.', 'Performance', 1],
        ['gutenberg-block-performance', 'Gutenberg_Block_Performance', 'Block Editor Performance', 'Measures Gutenberg editor load time.', 'Performance', 2],
        ['rest-api-health', 'REST_API_Health', 'REST API Health Check', 'Tests WordPress REST API endpoints.', 'Core', 1],
        ['multisite-scalability', 'Multisite_Scalability', 'Multisite Scaling Issues', 'Identifies bottlenecks in network setup.', 'Performance', 1],
        ['cdn-integration-active', 'CDN_Integration_Active', 'CDN Integration Working?', 'Verifies content delivery network setup.', 'Performance', 1],
        ['object-cache-utilization', 'Object_Cache_Utilization', 'Object Cache Hit Rate', 'Measures Redis/Memcached effectiveness.', 'Performance', 1],
        ['database-query-performance', 'Database_Query_Performance', 'Query Performance Profiling', 'Profiles slow database queries.', 'Performance', 1],
        ['premium-plugin-conflicts', 'Premium_Plugin_Conflicts', 'Premium Plugin Compatibility', 'Detects conflicts with common premium plugins.', 'Compatibility', 2],
        ['elasticsearch-ready', 'Elasticsearch_Ready', 'Elasticsearch Integration Ready?', 'Tests search infrastructure compatibility.', 'Integration', 2],
        ['redis-integration', 'Redis_Integration', 'Redis Object Cache Active?', 'Verifies Redis cache connection.', 'Performance', 1],
        ['memcached-integration', 'Memcached_Integration', 'Memcached Cache Active?', 'Verifies Memcached connection.', 'Performance', 2],
        ['varnish-compatibility', 'Varnish_Compatibility', 'Varnish Cache Compatible?', 'Tests compatibility with Varnish caching.', 'Performance', 2],
        ['nginx-optimization', 'Nginx_Optimization', 'Nginx Configuration Optimized?', 'Reviews nginx rules for performance.', 'System', 2],
    ],
];

$template = static function (string $ns, string $slug, string $class, string $title, string $desc, string $module, int $priority): string {
    return <<<'PHP'
<?php
declare(strict_types=1);

namespace WPShadow\DiagnosticsFuture\$ns;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: $title
 * 
 * Persona: $persona_context
 * Philosophy: Shows value (#9), educates (#5, #6), inspires confidence (#8)
 */
class Diagnostic_$class extends Diagnostic_Base {
    protected static \$slug = '$slug';
    protected static \$title = '$title';
    protected static \$description = '$desc';

    public static function check(): ?array {
        return array(
            'id'            => static::\$slug,
            'title'         => static::\$title . ' [STUB]',
            'description'   => static::\$description . ' (Not yet implemented)',
            'color'         => '#9e9e9e',
            'bg_color'      => '#f5f5f5',
            'kb_link'       => 'https://wpshadow.com/kb/$slug/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=$slug',
            'training_link' => 'https://wpshadow.com/training/$slug/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => '$module',
            'priority'      => $priority,
            'stub'          => true,
        );
    }

    /**
     * IMPLEMENTATION PLAN
     * 
     * Data Sources:
     * - [List specific data to check]
     * 
     * Success Criteria:
     * - [Define what "passing" means]
     * 
     * KPIs to Track:
     * - Issues found
     * - Time saved when fixed
     * - Value delivered (site health improvement)
     * 
     * Educational Links:
     * - KB article explaining why this matters
     * - Training video showing how to fix
     * - Related diagnostics to check next
     */
}
PHP;
};

$persona_map = [
    'BasicSiteHealth' => 'Non-technical users (Mom, Dad, friend)',
    'SmallBusiness' => 'Local business owners (Bakery, Plumber, Insurance Agent)',
    'Corporate' => 'Enterprise IT teams and compliance officers',
    'MarketingAgency' => 'Digital marketing agencies and growth teams',
    'Hosting' => 'Web hosting companies and server administrators',
    'WordPressVIP' => 'Enterprise WordPress platforms (Automattic, WPEngine)',
];

$count = 0;
foreach ($defs as $ns => $items) {
    $persona_context = $persona_map[$ns] ?? $ns;
    
    foreach ($items as [$slug, $class, $title, $desc, $module, $priority]) {
        $file = $base . '/class-diagnostic-' . $slug . '.php';
        
        if (file_exists($file)) {
            continue; // Skip existing
        }
        
        $code = $template($ns, $slug, $class, $title, $desc, $module, $priority);
        
        // Replace template variables
        $code = str_replace('$ns', $ns, $code);
        $code = str_replace('$slug', $slug, $code);
        $code = str_replace('$class', $class, $code);
        $code = str_replace('$title', $title, $code);
        $code = str_replace('$desc', addslashes($desc), $code);
        $code = str_replace('$module', $module, $code);
        $code = str_replace('$priority', (string)$priority, $code);
        $code = str_replace('$persona_context', $persona_context, $code);
        
        file_put_contents($file, $code);
        $count++;
    }
}

echo "✅ Generated {$count} persona-focused diagnostic stubs\n";
echo "\nBreakdown:\n";
foreach ($defs as $ns => $items) {
    echo "  - {$ns}: " . count($items) . " diagnostics\n";
}
echo "\n";
