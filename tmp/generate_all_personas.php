<?php
/**
 * Generate ALL persona-focused diagnostic stubs with correct template
 * Philosophy: Helpful neighbor, education-first, show value
 */

$base = __DIR__ . '/../includes/diagnostics/new';

// All diagnostics organized by persona
$defs = [
    'BasicSiteHealth' => [ // Mom's diagnostics (17 total)
        ['site-actually-loading', 'Site_Actually_Loading', 'Is Your Site Actually Loading?', 'Checks if your homepage loads successfully for visitors.', 'Core', 1],
        ['backups-configured', 'Backups_Configured', 'Are Backups Set Up?', 'Verifies automatic backup system is configured.', 'Core', 1],
        ['backups-working', 'Backups_Working', 'Are Backups Actually Working?', 'Tests if recent backups completed successfully.', 'Core', 1],
        ['contact-form-working', 'Contact_Form_Working', 'Is Contact Form Working?', 'Tests if contact form submissions reach you.', 'Core', 1],
        ['ssl-valid', 'SSL_Valid', 'Is SSL Certificate Valid?', 'Checks if your site has a working security certificate.', 'Security', 1],
        ['ssl-expiring-soon', 'SSL_Expiring_Soon', 'SSL Certificate Expiring Soon?', 'Warns if SSL certificate expires within 30 days.', 'Security', 1],
        ['updates-available', 'Updates_Available', 'Updates Waiting to Install?', 'Lists WordPress, plugin, and theme updates available.', 'Core', 1],
        ['site-down', 'Site_Down', 'Is Site Currently Down?', 'External check to verify site is reachable.', 'Monitoring', 1],
        ['images-too-large', 'Images_Too_Large', 'Are Images Slowing Site Down?', 'Finds unoptimized images over 300KB.', 'Performance', 1],
        ['plugins-conflicting', 'Plugins_Conflicting', 'Plugins Causing Conflicts?', 'Detects JavaScript errors from plugin conflicts.', 'Core', 1],
        ['spam-blocking-real-comments', 'Spam_Blocking_Real_Comments', 'Spam Filter Too Aggressive?', 'Checks if legitimate comments are being blocked.', 'Core', 2],
        ['login-works', 'Login_Works', 'Can You Still Log In?', 'Tests admin login functionality.', 'Security', 1],
        ['forgot-password-works', 'Forgot_Password_Works', 'Password Reset Working?', 'Verifies password reset emails are sent.', 'Core', 1],
        ['email-delivery-working', 'Email_Delivery_Working', 'Are Emails Being Delivered?', 'Tests if WordPress can send emails successfully.', 'Core', 1],
        ['mobile-friendly', 'Mobile_Friendly', 'Does Site Work on Phones?', 'Checks mobile-friendliness and viewport settings.', 'Design', 1],
        ['broken-images', 'Broken_Images', 'Any Broken Images?', 'Scans for missing or broken image files.', 'Content', 1],
        ['site-down-detector', 'Site_Down_Detector', 'External Site Monitoring', 'Verifies site is accessible from external locations.', 'Monitoring', 1],
    ],
    'SmallBusiness' => [ // Local business (16 total)
        ['local-seo-schema', 'Local_SEO_Schema', 'Local Business Schema Markup', 'Checks for LocalBusiness schema to appear in Google.', 'SEO', 1],
        ['google-business-profile', 'Google_Business_Profile', 'Google Business Profile Integration', 'Verifies Google Business Profile is linked/embedded.', 'SEO', 1],
        ['business-hours-display', 'Business_Hours_Display', 'Business Hours Visible?', 'Checks if operating hours are prominently displayed.', 'Content', 1],
        ['contact-info-visible', 'Contact_Info_Visible', 'Contact Info Easy to Find?', 'Verifies phone/address visible on every page.', 'Content', 1],
        ['mobile-experience', 'Mobile_Experience', 'Mobile Experience Quality', 'Scores mobile usability for local search traffic.', 'Performance', 1],
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
    'Corporate' => [ // Enterprise (18 total)
        ['gdpr-compliance', 'GDPR_Compliance', 'GDPR Compliance Status', 'Checks consent management and data protection.', 'Compliance', 1],
        ['ccpa-compliance', 'CCPA_Compliance', 'CCPA Compliance Status', 'Verifies California privacy law compliance.', 'Compliance', 1],
        ['wcag-accessibility', 'WCAG_Accessibility', 'WCAG 2.1 AA Compliance', 'Measures accessibility compliance level.', 'Compliance', 1],
        ['brand-consistency', 'Brand_Consistency', 'Brand Style Guide Compliance', 'Checks colors, fonts, logos match brand guide.', 'Design', 2],
        ['multisite-health', 'Multisite_Health', 'Multisite Network Health', 'Monitors health across all network sites.', 'Core', 1],
        ['user-role-management', 'User_Role_Management', 'User Role Configuration Review', 'Audits custom roles and capabilities.', 'Security', 1],
        ['audit-logging', 'Audit_Logging', 'Audit Trail Logging Active?', 'Verifies user action logging is enabled.', 'Security', 1],
        ['disaster-recovery', 'Disaster_Recovery', 'Disaster Recovery Readiness', 'Tests backup restore and recovery procedures.', 'Core', 1],
        ['sla-monitoring', 'SLA_Monitoring', 'SLA Uptime Monitoring', 'Tracks uptime against service level agreement.', 'Monitoring', 1],
        ['performance-at-scale', 'Performance_At_Scale', 'Performance Under Load', 'Tests response time with simulated traffic.', 'Performance', 1],
        ['security-hardening', 'Security_Hardening', 'Security Hardening Checklist', 'Scores security best practices implementation.', 'Security', 1],
        ['integration-health', 'Integration_Health', 'API Integration Health', 'Monitors CRM, ERP, and third-party integrations.', 'Core', 1],
        ['data-retention-policy', 'Data_Retention_Policy', 'Data Retention Policy Set?', 'Checks if data retention schedule is configured.', 'Compliance', 2],
        ['privacy-policy-current', 'Privacy_Policy_Current', 'Privacy Policy Up to Date?', 'Verifies privacy policy updated within 12 months.', 'Compliance', 1],
        ['terms-of-service-current', 'Terms_Of_Service_Current', 'Terms of Service Current?', 'Checks if ToS updated within 12 months.', 'Compliance', 2],
        ['cookie-consent', 'Cookie_Consent', 'Cookie Consent Compliant?', 'Verifies cookie banner meets regulations.', 'Compliance', 1],
        ['user-data-export', 'User_Data_Export', 'User Data Export Available?', 'Tests GDPR data export functionality.', 'Compliance', 1],
        ['user-data-deletion', 'User_Data_Deletion', 'User Data Deletion Works?', 'Tests right-to-be-forgotten implementation.', 'Compliance', 1],
    ],
    'MarketingAgency' => [ // Agency (16 total)
        ['analytics-tracking', 'Analytics_Tracking', 'Analytics Tracking Active?', 'Verifies analytics code is firing correctly.', 'Marketing', 1],
        ['ga4-installed', 'GA4_Installed', 'Google Analytics 4 Installed?', 'Checks if GA4 tracking is configured.', 'Marketing', 1],
        ['gtm-installed', 'GTM_Installed', 'Google Tag Manager Installed?', 'Verifies GTM container is present and firing.', 'Marketing', 1],
        ['conversion-tracking', 'Conversion_Tracking', 'Conversion Tracking Working?', 'Tests if goal completions are tracked.', 'Marketing', 1],
        ['ab-testing-setup', 'AB_Testing_Setup', 'A/B Testing Configured?', 'Checks if split testing tools are active.', 'Marketing', 2],
        ['seo-meta-tags', 'SEO_Meta_Tags', 'SEO Meta Tags Complete?', 'Verifies title, description, OG tags present.', 'SEO', 1],
        ['social-media-integration', 'Social_Media_Integration', 'Social Media Integration Active?', 'Checks social sharing and profile links.', 'Marketing', 1],
        ['email-capture', 'Email_Capture', 'Email Capture Forms Working?', 'Tests newsletter signup and lead magnets.', 'Marketing', 1],
        ['landing-page-speed', 'Landing_Page_Speed', 'Landing Page Load Speed', 'Measures speed of conversion-critical pages.', 'Performance', 1],
        ['utm-parameters-preserved', 'UTM_Parameters_Preserved', 'UTM Parameters Tracked?', 'Verifies campaign tracking parameters work.', 'Marketing', 1],
        ['facebook-pixel', 'Facebook_Pixel', 'Facebook Pixel Firing?', 'Tests Meta/Facebook pixel installation.', 'Marketing', 1],
        ['google-ads-tracking', 'Google_Ads_Tracking', 'Google Ads Conversion Tracking?', 'Verifies Google Ads remarketing tag.', 'Marketing', 1],
        ['heatmap-tracking', 'Heatmap_Tracking', 'Heatmap/Recording Tools Active?', 'Checks for Hotjar, Clarity, etc.', 'Marketing', 2],
        ['lead-magnet-working', 'Lead_Magnet_Working', 'Lead Magnet Delivery Working?', 'Tests automated content delivery.', 'Marketing', 1],
        ['thank-you-page-tracking', 'Thank_You_Page_Tracking', 'Thank You Page Tracking?', 'Verifies conversion confirmation tracking.', 'Marketing', 1],
        ['form-abandonment', 'Form_Abandonment', 'Form Abandonment Tracking?', 'Tracks incomplete form submissions.', 'Marketing', 2],
    ],
    'Hosting' => [ // Hosting (15 total)
        ['resource-usage', 'Resource_Usage', 'Server Resource Usage', 'Monitors CPU, memory, disk I/O usage.', 'System', 1],
        ['cpu-spikes', 'CPU_Spikes', 'CPU Spike Detection', 'Alerts on unusual CPU usage patterns.', 'System', 1],
        ['memory-usage', 'Memory_Usage', 'Memory Usage Monitoring', 'Tracks PHP and MySQL memory consumption.', 'System', 1],
        ['database-optimization', 'Database_Optimization', 'Database Optimization Needed?', 'Identifies tables needing optimization.', 'Performance', 1],
        ['caching-effectiveness', 'Caching_Effectiveness', 'Cache Hit Rate Analysis', 'Measures object cache and page cache efficiency.', 'Performance', 1],
        ['php-version-compatible', 'PHP_Version_Compatible', 'PHP Version Compatibility', 'Checks for deprecated code in current PHP.', 'System', 1],
        ['server-config-optimal', 'Server_Config_Optimal', 'Server Configuration Optimized?', 'Reviews PHP/MySQL settings for performance.', 'System', 1],
        ['malware-scan', 'Malware_Scan', 'Malware Scanning Active?', 'Verifies malware detection is enabled.', 'Security', 1],
        ['backup-completion', 'Backup_Completion', 'Backup Success Rate', 'Tracks backup completion reliability.', 'Core', 1],
        ['uptime-monitoring', 'Uptime_Monitoring', 'Uptime Monitoring Enabled?', 'Checks external uptime monitoring.', 'Monitoring', 1],
        ['traffic-spike-handling', 'Traffic_Spike_Handling', 'Traffic Spike Readiness', 'Tests if site handles sudden traffic surges.', 'Performance', 1],
        ['error-log-patterns', 'Error_Log_Patterns', 'Error Log Pattern Analysis', 'Identifies recurring errors in logs.', 'System', 1],
        ['disk-space', 'Disk_Space', 'Disk Space Monitoring', 'Alerts when disk usage exceeds thresholds.', 'System', 1],
        ['inode-usage', 'Inode_Usage', 'Inode Usage Monitoring', 'Tracks file count against inode limits.', 'System', 2],
        ['slow-queries', 'Slow_Queries', 'Slow Database Query Detection', 'Identifies queries over 2 seconds.', 'Performance', 1],
    ],
    'WordPressVIP' => [ // Enterprise platforms (15 total)
        ['vip-go-compatibility', 'VIP_Go_Compatibility', 'WordPress VIP Compatibility', 'Checks code against VIP coding standards.', 'Compatibility', 2],
        ['jetpack-integration', 'Jetpack_Integration', 'Jetpack Integration Health', 'Monitors Jetpack feature functionality.', 'Integration', 2],
        ['woocommerce-optimization', 'WooCommerce_Optimization', 'WooCommerce Performance Optimized?', 'Checks WooCommerce query optimization.', 'Performance', 1],
        ['gutenberg-block-performance', 'Gutenberg_Block_Performance', 'Block Editor Performance', 'Measures Gutenberg editor load time.', 'Performance', 2],
        ['rest-api-health', 'REST_API_Health', 'REST API Health Check', 'Tests WordPress REST API endpoints.', 'Core', 1],
        ['multisite-scalability', 'Multisite_Scalability', 'Multisite Scaling Issues', 'Identifies bottlenecks in network setup.', 'Performance', 1],
        ['cdn-integration', 'CDN_Integration', 'CDN Integration Working?', 'Verifies content delivery network setup.', 'Performance', 1],
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

$template = static function (string $ns, string $slug, string $class, string $title, string $desc, string $module, int $priority, string $persona): string {
    $escapedDesc = addslashes($desc);
    $escapedTitle = addslashes($title);
    
    return <<<PHP
<?php
declare(strict_types=1);

namespace WPShadow\\DiagnosticsFuture\\{$ns};

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: {$title}
 * 
 * Target Persona: {$persona}
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_{$class} extends Diagnostic_Base {
    protected static \$slug = '{$slug}';
    protected static \$title = '{$escapedTitle}';
    protected static \$description = '{$escapedDesc}';

    public static function check(): ?array {
        return array(
            'id'            => static::\$slug,
            'title'         => static::\$title . ' [STUB]',
            'description'   => static::\$description . ' (Not yet implemented)',
            'color'         => '#9e9e9e',
            'bg_color'      => '#f5f5f5',
            'kb_link'       => 'https://wpshadow.com/kb/{$slug}/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign={$slug}',
            'training_link' => 'https://wpshadow.com/training/{$slug}/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => '{$module}',
            'priority'      => {$priority},
            'stub'          => true,
        );
    }

    /**
     * IMPLEMENTATION PLAN ({$persona})
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
     * - Business value delivered (\$)
     */
}
PHP;
};

$persona_names = [
    'BasicSiteHealth' => 'Non-technical Site Owner (Mom/Dad)',
    'SmallBusiness' => 'Local Business Owner (Bakery/Plumber/Insurance)',
    'Corporate' => 'Enterprise IT/Compliance Team',
    'MarketingAgency' => 'Digital Marketing Agency',
    'Hosting' => 'Web Hosting Provider',
    'WordPressVIP' => 'Enterprise WordPress Platform (Automattic/WPEngine)',
];

$total = 0;
$counts = [];

foreach ($defs as $ns => $items) {
    $count = 0;
    foreach ($items as [$slug, $class, $title, $desc, $module, $priority]) {
        $file = $base . '/class-diagnostic-' . $slug . '.php';
        
        $code = $template($ns, $slug, $class, $title, $desc, $module, $priority, $persona_names[$ns]);
        
        file_put_contents($file, $code);
        $count++;
        $total++;
    }
    $counts[$ns] = $count;
}

echo "✅ Generated {$total} persona-focused diagnostic stubs\n\n";
echo "Persona Coverage:\n";
echo "  👵 Non-Technical Users (Mom): {$counts['BasicSiteHealth']} diagnostics\n";
echo "  🏪 Small Business Owners: {$counts['SmallBusiness']} diagnostics\n";
echo "  🏢 Corporate/Enterprise: {$counts['Corporate']} diagnostics\n";
echo "  📊 Marketing Agencies: {$counts['MarketingAgency']} diagnostics\n";
echo "  🖥️  Web Hosting Companies: {$counts['Hosting']} diagnostics\n";
echo "  ⚡ WordPress VIP Platforms: {$counts['WordPressVIP']} diagnostics\n";
echo "\nAll diagnostics follow WPShadow philosophy:\n";
echo "  ✓ Educational (link to free KB + training)\n";
echo "  ✓ Value-focused (track KPIs, show time saved)\n";
echo "  ✓ Plain English (no jargon)\n";
echo "  ✓ Helpful neighbor (advice not sales)\n";
