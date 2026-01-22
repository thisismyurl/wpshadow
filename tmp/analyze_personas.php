<?php
/**
 * Analyze diagnostics coverage by user persona
 */

$dir = __DIR__ . '/../includes/diagnostics';

// Define persona needs
$personas = [
    'mom' => [
        'name' => 'Non-Technical User (Mom)',
        'needs' => [
            'site-actually-loading', 'backups-configured', 'backups-working', 'contact-form-working',
            'ssl-valid', 'ssl-expiring-soon', 'updates-available', 'site-down', 'images-too-large',
            'plugins-conflicting', 'broken-links', 'spam-blocking-real-comments', 'login-works',
            'forgot-password-works', 'email-delivery-working', 'mobile-friendly', 'broken-images',
        ],
    ],
    'small-business' => [
        'name' => 'Small Business Owner (Bakery/Plumber/Insurance)',
        'needs' => [
            'local-seo-schema', 'google-business-profile', 'business-hours-display', 'contact-info-visible',
            'mobile-experience', 'page-speed-score', 'appointment-system-working', 'payment-gateway-functional',
            'review-display', 'service-area-pages', 'emergency-contact-info', 'call-tracking-working',
            'map-embed-working', 'social-proof-visible', 'customer-testimonials', 'before-after-gallery',
        ],
    ],
    'corporate' => [
        'name' => 'Corporate/Enterprise',
        'needs' => [
            'gdpr-compliance', 'ccpa-compliance', 'wcag-accessibility', 'brand-consistency',
            'multisite-health', 'user-role-management', 'audit-logging', 'disaster-recovery',
            'sla-monitoring', 'performance-at-scale', 'security-hardening', 'integration-health',
            'data-retention-policy', 'privacy-policy-current', 'terms-of-service-current',
            'cookie-consent', 'user-data-export', 'user-data-deletion',
        ],
    ],
    'agency' => [
        'name' => 'Marketing Agency',
        'needs' => [
            'analytics-tracking', 'ga4-installed', 'gtm-installed', 'conversion-tracking',
            'ab-testing-setup', 'seo-meta-tags', 'social-media-integration', 'email-capture',
            'landing-page-speed', 'utm-parameters-preserved', 'facebook-pixel', 'google-ads-tracking',
            'heatmap-tracking', 'lead-magnet-working', 'thank-you-page-tracking', 'form-abandonment',
        ],
    ],
    'hosting' => [
        'name' => 'Web Hosting Company',
        'needs' => [
            'resource-usage', 'cpu-spikes', 'memory-usage', 'database-optimization',
            'caching-effectiveness', 'php-version-compatible', 'server-config-optimal',
            'malware-scan', 'backup-completion', 'uptime-monitoring', 'traffic-spike-handling',
            'error-log-patterns', 'disk-space', 'inode-usage', 'slow-queries',
        ],
    ],
    'wordpress-vip' => [
        'name' => 'Automattic/WPEngine/WordPress VIP',
        'needs' => [
            'vip-go-compatibility', 'jetpack-integration', 'woocommerce-optimization',
            'gutenberg-block-performance', 'rest-api-health', 'multisite-scalability',
            'cdn-integration', 'object-cache-utilization', 'database-query-performance',
            'premium-plugin-conflicts', 'elasticsearch-ready', 'redis-integration',
            'memcached-integration', 'varnish-compatibility', 'nginx-optimization',
        ],
    ],
];

// Scan for existing diagnostics
$existing = [];
foreach (new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
) as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
        $slug = preg_replace('/^class-diagnostic-(.+)\.php$/', '$1', $file->getFilename());
        $existing[] = $slug;
    }
}

// Check coverage
echo "=== PERSONA DIAGNOSTIC COVERAGE ANALYSIS ===\n\n";

$total_missing = 0;
foreach ($personas as $key => $persona) {
    echo "## {$persona['name']}\n";
    echo str_repeat('-', 60) . "\n";
    
    $missing = [];
    foreach ($persona['needs'] as $need) {
        if (!in_array($need, $existing)) {
            $missing[] = $need;
        }
    }
    
    $coverage = count($persona['needs']) - count($missing);
    $percent = round(($coverage / count($persona['needs'])) * 100, 1);
    
    echo "Coverage: {$coverage}/" . count($persona['needs']) . " ({$percent}%)\n";
    
    if (!empty($missing)) {
        echo "Missing diagnostics:\n";
        foreach ($missing as $m) {
            echo "  ❌ {$m}\n";
        }
        $total_missing += count($missing);
    } else {
        echo "✅ Full coverage!\n";
    }
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "Total missing diagnostics across all personas: {$total_missing}\n";
