<?php
/**
 * KPI Metadata for Diagnostics
 *
 * Defines business value, time savings, and category for each diagnostic
 * This enables accurate KPI tracking and executive reporting.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

/**
 * KPI metadata registry for all diagnostics
 *
 * Each diagnostic has:
 * - time_to_fix_minutes: How long it would take to fix manually
 * - business_value: Impact description for executives
 * - category: Primary classification
 * - risk_reduction: % risk reduction when fixed
 * - roi_multiplier: How this scales for ROI calculations
 */
class KPI_Metadata {
	
	/**
	 * Get all KPI metadata indexed by diagnostic ID
	 *
	 * @return array KPI metadata keyed by diagnostic ID.
	 */
	public static function get_all() {
		return array(
			// Security Diagnostics
			'admin-email' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'security',
				'business_value'         => 'Protects admin identity from public exposure',
				'risk_reduction'         => 15,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.0,
			),
			'admin-username' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'security',
				'business_value'         => 'Eliminates brute-force attack surface (prevents 40% of attacks)',
				'risk_reduction'         => 40,
				'severity'               => 'high',
				'roi_multiplier'         => 1.5,
			),
			'ssl' => array(
				'time_to_fix_minutes'    => 45,
				'category'               => 'security',
				'business_value'         => 'Enables HTTPS; critical for SEO, PCI compliance, and visitor trust',
				'risk_reduction'         => 50,
				'severity'               => 'critical',
				'roi_multiplier'         => 2.0,
			),
			'security-headers' => array(
				'time_to_fix_minutes'    => 20,
				'category'               => 'security',
				'business_value'         => 'Adds CSP, X-Frame-Options, X-Content-Type-Options headers (prevents 30% of XSS attacks)',
				'risk_reduction'         => 30,
				'severity'               => 'high',
				'roi_multiplier'         => 1.3,
			),
			'hotlink-protection' => array(
				'time_to_fix_minutes'    => 15,
				'category'               => 'security',
				'business_value'         => 'Protects bandwidth costs; prevents image hijacking',
				'risk_reduction'         => 10,
				'severity'               => 'low',
				'roi_multiplier'         => 0.8,
			),
			'rest-api' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'security',
				'business_value'         => 'Restricts REST API exposure to authenticated users',
				'risk_reduction'         => 20,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.2,
			),
			'rss-feeds' => array(
				'time_to_fix_minutes'    => 8,
				'category'               => 'security',
				'business_value'         => 'Prevents content scraping and leaks sensitive site info',
				'risk_reduction'         => 5,
				'severity'               => 'low',
				'roi_multiplier'         => 0.7,
			),
			'post-via-email' => array(
				'time_to_fix_minutes'    => 15,
				'category'               => 'security',
				'business_value'         => 'Disables unused feature; eliminates email-based attack vector',
				'risk_reduction'         => 25,
				'severity'               => 'high',
				'roi_multiplier'         => 1.4,
			),
			'file-permissions' => array(
				'time_to_fix_minutes'    => 60,
				'category'               => 'security',
				'business_value'         => 'Prevents unauthorized file access; critical for multisite security',
				'risk_reduction'         => 45,
				'severity'               => 'high',
				'roi_multiplier'         => 1.6,
			),
			'consent-checks' => array(
				'time_to_fix_minutes'    => 25,
				'category'               => 'security',
				'business_value'         => 'Ensures GDPR/CCPA compliance; prevents legal liability',
				'risk_reduction'         => 35,
				'severity'               => 'high',
				'roi_multiplier'         => 2.5,
			),
			'error-log' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'security',
				'business_value'         => 'Enables safe error logging for debugging; prevents data leaks',
				'risk_reduction'         => 20,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.1,
			),
			'debug-mode' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'code_quality',
				'business_value'         => 'Disables debug output on production; prevents info disclosure',
				'risk_reduction'         => 10,
				'severity'               => 'medium',
				'roi_multiplier'         => 0.9,
			),
			
			// Performance Diagnostics
			'memory-limit' => array(
				'time_to_fix_minutes'    => 20,
				'category'               => 'performance',
				'business_value'         => 'Increases available RAM; prevents fatal errors and improves responsiveness',
				'risk_reduction'         => 5,
				'severity'               => 'high',
				'roi_multiplier'         => 1.8,
			),
			'image-lazy-load' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'performance',
				'business_value'         => 'Defers offscreen image loading; improves page speed 15-25%',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.9,
			),
			'external-fonts' => array(
				'time_to_fix_minutes'    => 15,
				'category'               => 'performance',
				'business_value'         => 'Eliminates external requests; improves load time and SEO',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.4,
			),
			'jquery-migrate' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'performance',
				'business_value'         => 'Removes deprecated jQuery library; reduces JS payload 50KB',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.1,
			),
			'emoji-scripts' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'performance',
				'business_value'         => 'Removes unnecessary WordPress emoji script; improves performance',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.9,
			),
			'database-health' => array(
				'time_to_fix_minutes'    => 45,
				'category'               => 'performance',
				'business_value'         => 'Optimizes database; improves query speed 30-40%',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 2.1,
			),
			'head-cleanup' => array(
				'time_to_fix_minutes'    => 20,
				'category'               => 'performance',
				'business_value'         => 'Removes bloat from document head; improves render time',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.2,
			),
			
			// Code Quality Diagnostics
			'wp-generator' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'code_quality',
				'business_value'         => 'Hides WordPress version; minor security through obscurity',
				'risk_reduction'         => 5,
				'severity'               => 'low',
				'roi_multiplier'         => 0.6,
			),
			'embed-disable' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'code_quality',
				'business_value'         => 'Disables oEmbed feature; reduces attack surface',
				'risk_reduction'         => 8,
				'severity'               => 'low',
				'roi_multiplier'         => 0.8,
			),
			'skiplinks' => array(
				'time_to_fix_minutes'    => 25,
				'category'               => 'code_quality',
				'business_value'         => 'Improves accessibility; helps screen readers navigate content',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.0,
			),
			
			// WordPress Config Diagnostics
			'wordpress-version' => array(
				'time_to_fix_minutes'    => 60,
				'category'               => 'settings',
				'business_value'         => 'Keeps core updated; security patches, performance, new features',
				'risk_reduction'         => 50,
				'severity'               => 'high',
				'roi_multiplier'         => 2.0,
			),
			'php-version' => array(
				'time_to_fix_minutes'    => 90,
				'category'               => 'settings',
				'business_value'         => 'Upgrades to modern PHP; massive performance boost 50-100%',
				'risk_reduction'         => 40,
				'severity'               => 'high',
				'roi_multiplier'         => 3.0,
			),
			'permalinks' => array(
				'time_to_fix_minutes'    => 15,
				'category'               => 'settings',
				'business_value'         => 'Enables pretty URLs; critical for SEO and user experience',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.3,
			),
			'tagline' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'settings',
				'business_value'         => 'Updates site tagline; improves brand clarity and SEO',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.7,
			),
			
			// Monitoring Diagnostics
			'broken-links' => array(
				'time_to_fix_minutes'    => 120,
				'category'               => 'monitoring',
				'business_value'         => 'Identifies broken links; improves UX and site credibility',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.5,
			),
			'plugin-count' => array(
				'time_to_fix_minutes'    => 90,
				'category'               => 'monitoring',
				'business_value'         => 'Identifies unused plugins; reduces maintenance burden',
				'risk_reduction'         => 20,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.4,
			),
			'mobile-friendly' => array(
				'time_to_fix_minutes'    => 180,
				'category'               => 'monitoring',
				'business_value'         => 'Ensures mobile responsiveness; critical for SEO and engagement',
				'risk_reduction'         => 0,
				'severity'               => 'high',
				'roi_multiplier'         => 2.2,
			),
			'backup' => array(
				'time_to_fix_minutes'    => 45,
				'category'               => 'monitoring',
				'business_value'         => 'Ensures regular backups; critical for disaster recovery',
				'risk_reduction'         => 60,
				'severity'               => 'critical',
				'roi_multiplier'         => 2.8,
			),
			'backup-verification' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'monitoring',
				'business_value'         => 'Verifies backups are working; prevents failed recovery scenarios',
				'risk_reduction'         => 50,
				'severity'               => 'high',
				'roi_multiplier'         => 2.5,
			),
			
			// System Diagnostics
			'plugin-auto-updates' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'settings',
				'business_value'         => 'Enables automatic security updates; reduces manual maintenance',
				'risk_reduction'         => 25,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.2,
			),
			'inactive-plugins' => array(
				'time_to_fix_minutes'    => 60,
				'category'               => 'monitoring',
				'business_value'         => 'Removes inactive plugins; reduces attack surface and bloat',
				'risk_reduction'         => 15,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.3,
			),
			'outdated-plugins' => array(
				'time_to_fix_minutes'    => 90,
				'category'               => 'monitoring',
				'business_value'         => 'Updates plugins with security patches; prevents exploits',
				'risk_reduction'         => 50,
				'severity'               => 'critical',
				'roi_multiplier'         => 2.3,
			),
			'core-integrity' => array(
				'time_to_fix_minutes'    => 120,
				'category'               => 'security',
				'business_value'         => 'Verifies WordPress core files haven\'t been tampered with',
				'risk_reduction'         => 40,
				'severity'               => 'high',
				'roi_multiplier'         => 1.8,
			),
			'database-health' => array(
				'time_to_fix_minutes'    => 75,
				'category'               => 'performance',
				'business_value'         => 'Optimizes database; improves query speed 20-40%',
				'risk_reduction'         => 5,
				'severity'               => 'medium',
				'roi_multiplier'         => 2.1,
			),
			'database-indexes' => array(
				'time_to_fix_minutes'    => 45,
				'category'               => 'performance',
				'business_value'         => 'Adds missing database indexes; accelerates queries 30-60%',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 2.4,
			),
			'object-cache' => array(
				'time_to_fix_minutes'    => 60,
				'category'               => 'performance',
				'business_value'         => 'Enables Redis/Memcached caching; reduces DB calls 70%',
				'risk_reduction'         => 0,
				'severity'               => 'high',
				'roi_multiplier'         => 3.5,
			),
			'heartbeat-throttling' => array(
				'time_to_fix_minutes'    => 15,
				'category'               => 'performance',
				'business_value'         => 'Reduces admin AJAX polling; saves bandwidth and CPU',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.4,
			),
			'two-factor' => array(
				'time_to_fix_minutes'    => 20,
				'category'               => 'security',
				'business_value'         => 'Enables 2FA for admin accounts; prevents 99% of credential attacks',
				'risk_reduction'         => 85,
				'severity'               => 'high',
				'roi_multiplier'         => 2.2,
			),
			'disallow-file-edit' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'security',
				'business_value'         => 'Disables plugin/theme editor; prevents malicious code injection',
				'risk_reduction'         => 30,
				'severity'               => 'high',
				'roi_multiplier'         => 1.5,
			),
			'timezone' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'settings',
				'business_value'         => 'Sets correct timezone; ensures accurate scheduled tasks and timestamps',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.6,
			),
			'initial-setup' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'settings',
				'business_value'         => 'Completes WordPress setup; foundational for all operations',
				'risk_reduction'         => 5,
				'severity'               => 'high',
				'roi_multiplier'         => 1.1,
			),
			
			// SEO & Content Diagnostics
			'search-indexing' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'seo',
				'business_value'         => 'Allows search engines to index content; essential for SEO',
				'risk_reduction'         => 0,
				'severity'               => 'high',
				'roi_multiplier'         => 2.0,
			),
			'xml-sitemap' => array(
				'time_to_fix_minutes'    => 15,
				'category'               => 'seo',
				'business_value'         => 'Enables XML sitemaps; helps search engines discover all pages',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.5,
			),
			'robots-txt' => array(
				'time_to_fix_minutes'    => 15,
				'category'               => 'seo',
				'business_value'         => 'Creates robots.txt; controls crawler access and prevents crawl waste',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.3,
			),
			'favicon' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'design',
				'business_value'         => 'Adds favicon; improves brand recognition and browser tabs',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.8,
			),
			'resource-hints' => array(
				'time_to_fix_minutes'    => 20,
				'category'               => 'performance',
				'business_value'         => 'Adds DNS prefetch, preconnect hints; speeds up resource loading',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.4,
			),
			'wp-generator' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'security',
				'business_value'         => 'Removes WP version from source; hides easy attack target',
				'risk_reduction'         => 8,
				'severity'               => 'low',
				'roi_multiplier'         => 0.9,
			),
			
			// Accessibility & UX Diagnostics
			'nav-aria' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'accessibility',
				'business_value'         => 'Adds ARIA labels to navigation; helps screen readers and SEO',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.2,
			),
			'css-classes' => array(
				'time_to_fix_minutes'    => 45,
				'category'               => 'code_quality',
				'business_value'         => 'Maintains semantic CSS classes; improves maintainability',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.1,
			),
			'maintenance' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'settings',
				'business_value'         => 'Sets up maintenance mode; safe for updates and troubleshooting',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.7,
			),
			'howdy-greeting' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'design',
				'business_value'         => 'Customizes admin greeting; improves branding',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.6,
			),
			'dark-mode' => array(
				'time_to_fix_minutes'    => 45,
				'category'               => 'design',
				'business_value'         => 'Enables dark mode support; improves user experience',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.0,
			),
			'comments-disabled' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'settings',
				'business_value'         => 'Disables unused comments; reduces spam and admin burden',
				'risk_reduction'         => 5,
				'severity'               => 'low',
				'roi_multiplier'         => 0.8,
			),
			
			// Content Management Diagnostics
			'content-optimizer' => array(
				'time_to_fix_minutes'    => 60,
				'category'               => 'code_quality',
				'business_value'         => 'Optimizes content formatting; improves readability and SEO',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.3,
			),
			'paste-cleanup' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'code_quality',
				'business_value'         => 'Removes formatting when pasting; prevents bloat and conflicts',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.1,
			),
			'html-cleanup' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'code_quality',
				'business_value'         => 'Cleans up malformed HTML; improves rendering and standards compliance',
				'risk_reduction'         => 10,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.2,
			),
			'pre-publish-review' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'code_quality',
				'business_value'         => 'Adds pre-publish checklist; prevents quality issues',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.0,
			),
			'embed-disable' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'security',
				'business_value'         => 'Disables WordPress embed feature; reduces attack surface',
				'risk_reduction'         => 8,
				'severity'               => 'low',
				'roi_multiplier'         => 0.8,
			),
			'interactivity-cleanup' => array(
				'time_to_fix_minutes'    => 20,
				'category'               => 'performance',
				'business_value'         => 'Removes unused interactivity scripts; improves load time',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.2,
			),
			'block-cleanup' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'design',
				'business_value'         => 'Removes unused block types; simplifies editor',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.9,
			),
			'emoji-scripts' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'performance',
				'business_value'         => 'Disables emoji scripts; reduces HTTP requests by 1',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.7,
			),
			'jquery-cleanup' => array(
				'time_to_fix_minutes'    => 45,
				'category'               => 'performance',
				'business_value'         => 'Removes unused jQuery; reduces payload 30-50KB',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.3,
			),
			
			// Monitoring & Integration Diagnostics
			'monitoring-status' => array(
				'time_to_fix_minutes'    => 20,
				'category'               => 'monitoring',
				'business_value'         => 'Sets up site monitoring; enables proactive issue detection',
				'risk_reduction'         => 15,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.8,
			),
			'automation-readiness' => array(
				'time_to_fix_minutes'    => 45,
				'category'               => 'settings',
				'business_value'         => 'Prepares site for automation; enables recurring fixes',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 2.0,
			),
			'webhooks-readiness' => array(
				'time_to_fix_minutes'    => 30,
				'category'               => 'settings',
				'business_value'         => 'Enables webhooks for integrations; unlocks advanced workflows',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 1.5,
			),
			'php-compatibility' => array(
				'time_to_fix_minutes'    => 60,
				'category'               => 'settings',
				'business_value'         => 'Ensures plugins are compatible with PHP version; prevents breakage',
				'risk_reduction'         => 10,
				'severity'               => 'high',
				'roi_multiplier'         => 1.7,
			),
			'theme-performance' => array(
				'time_to_fix_minutes'    => 90,
				'category'               => 'performance',
				'business_value'         => 'Optimizes theme code; reduces render blocking',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.9,
			),
			'font-optimization' => array(
				'time_to_fix_minutes'    => 40,
				'category'               => 'performance',
				'business_value'         => 'Optimizes font loading; improves CLS and page speed',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.6,
			),
			'theme-update-noise' => array(
				'time_to_fix_minutes'    => 15,
				'category'               => 'monitoring',
				'business_value'         => 'Manages theme update notifications; reduces alert fatigue',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.8,
			),
			'plugin-update-noise' => array(
				'time_to_fix_minutes'    => 15,
				'category'               => 'monitoring',
				'business_value'         => 'Manages plugin update notifications; reduces noise',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.8,
			),
			'user-notification-email' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'settings',
				'business_value'         => 'Sets up user notification email; ensures admin alerts work',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.7,
			),
			'head-cleanup-emoji' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'performance',
				'business_value'         => 'Removes emoji loader from head; reduces blocking scripts',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.7,
			),
			'head-cleanup-oembed' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'performance',
				'business_value'         => 'Removes oEmbed script from head; reduces HTTP requests',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.7,
			),
			'head-cleanup-rsd' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'performance',
				'business_value'         => 'Removes RSD link from head; minimal but measurable savings',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.6,
			),
			'head-cleanup-shortlink' => array(
				'time_to_fix_minutes'    => 5,
				'category'               => 'performance',
				'business_value'         => 'Removes shortlink from head; cleanup optimization',
				'risk_reduction'         => 0,
				'severity'               => 'low',
				'roi_multiplier'         => 0.6,
			),
			'asset-versions-css' => array(
				'time_to_fix_minutes'    => 20,
				'category'               => 'performance',
				'business_value'         => 'Adds CSS version hashes; prevents cache issues on updates',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.1,
			),
			'asset-versions-js' => array(
				'time_to_fix_minutes'    => 20,
				'category'               => 'performance',
				'business_value'         => 'Adds JS version hashes; ensures cache busting on updates',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.1,
			),
			'jquery-migrate' => array(
				'time_to_fix_minutes'    => 60,
				'category'               => 'performance',
				'business_value'         => 'Removes jQuery Migrate; improves compatibility and speed',
				'risk_reduction'         => 0,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.4,
			),
			'iframe-busting' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'security',
				'business_value'         => 'Prevents clickjacking via iframe; protects against XSS',
				'risk_reduction'         => 15,
				'severity'               => 'medium',
				'roi_multiplier'         => 1.2,
			),
			'post-via-email-category' => array(
				'time_to_fix_minutes'    => 10,
				'category'               => 'settings',
				'business_value'         => 'Disables post-by-email category feature; reduces attack surface',
				'risk_reduction'         => 10,
				'severity'               => 'low',
				'roi_multiplier'         => 0.8,
			),
		);
	}
	
	/**
	 * Get KPI metadata for a specific diagnostic
	 *
	 * @param string $diagnostic_id Diagnostic identifier.
	 * @return array KPI metadata or default if not found.
	 */
	public static function get( $diagnostic_id ) {
		$all = self::get_all();
		return isset( $all[ $diagnostic_id ] ) ? $all[ $diagnostic_id ] : self::get_default();
	}
	
	/**
	 * Get default KPI metadata when not explicitly defined
	 *
	 * @return array Default metadata.
	 */
	public static function get_default() {
		return array(
			'time_to_fix_minutes'    => 15,
			'category'               => 'general',
			'business_value'         => 'Improves site quality and security',
			'risk_reduction'         => 10,
			'severity'               => 'medium',
			'roi_multiplier'         => 1.0,
		);
	}
	
	/**
	 * Get metadata by category
	 *
	 * @param string $category Category name.
	 * @return array Diagnostics in this category.
	 */
	public static function get_by_category( $category ) {
		$all = self::get_all();
		return array_filter(
			$all,
			function( $meta ) use ( $category ) {
				return isset( $meta['category'] ) && $meta['category'] === $category;
			}
		);
	}
	
	/**
	 * Calculate total ROI for a list of applied fixes
	 *
	 * @param array $applied_fixes Array of diagnostic IDs that were fixed.
	 * @param int   $hourly_rate   Labor cost per hour ($).
	 * @return array ROI breakdown.
	 */
	public static function calculate_roi( $applied_fixes, $hourly_rate = 50 ) {
		$total_minutes = 0;
		$risk_reduction = 0;
		$roi_score = 0;
		
		foreach ( $applied_fixes as $diagnostic_id ) {
			$meta = self::get( $diagnostic_id );
			$total_minutes += $meta['time_to_fix_minutes'];
			$risk_reduction += $meta['risk_reduction'];
			$roi_score += $meta['roi_multiplier'];
		}
		
		$total_hours = $total_minutes / 60;
		$labor_cost_avoided = $total_hours * $hourly_rate;
		
		return array(
			'total_hours'         => (int) $total_hours,
			'labor_cost_avoided'  => (int) $labor_cost_avoided,
			'risk_reduction_pct'  => min( 100, $risk_reduction ),
			'roi_score'           => $roi_score,
			'fixes_count'         => count( $applied_fixes ),
		);
	}
}
