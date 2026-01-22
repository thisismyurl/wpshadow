<?php
/**
 * Strategic Diagnostic Stub Creator
 *
 * Creates 440+ diagnostic test stubs organized by:
 * - 165 Priority-1 tests (must-have)
 * - 90 Priority-2 tests (should-have)
 * - 90 Priority-3 tests (nice-to-have)
 * - Plus external service permission stubs
 * 
 * This is organized around "trusted advisor" positioning:
 * - Move from "helpful neighbor" to "trusted advisor"
 * - Focus on user success metrics, not just technical metrics
 * - Enable guiding users toward solutions they can buy/learn
 * 
 * @package WPShadow
 */

$categories = array(
	// PRIORITY 1: MUST-HAVE (165 tests)
	// These make WPShadow feel essential
	
	'audit_trail' => array(
		'label' => 'Audit & Activity Trail',
		'category_slug' => 'audit_trail',
		'icon' => 'dashicons-history',
		'color' => '#f59e0b',
		'priority' => 1,
		'philosophy_commands' => array( 1, 5, 10 ), // Helpful neighbor, Drive to KB, Privacy
		'description' => 'Complete activity logging - who changed what, when, and why. Critical for compliance and debugging.',
		'tests' => array(
			'audit-logging-enabled' => 'Is activity logging enabled?',
			'audit-log-retention' => 'How long are logs being retained?',
			'audit-log-storage' => 'Are logs stored durably in database?',
			'audit-user-changes' => 'Are user creates/deletes logged?',
			'audit-settings-changes' => 'Are settings modifications tracked?',
			'audit-post-changes' => 'Are content modifications tracked?',
			'audit-image-uploads' => 'Are image uploads tracked with metadata?',
			'audit-plugin-changes' => 'Are plugin activations/deactivations logged?',
			'audit-theme-changes' => 'Are theme changes logged?',
			'audit-permission-changes' => 'Are user role/capability changes logged?',
			'audit-export-tracked' => 'Are data exports recorded?',
			'audit-deletion-tracked' => 'Are deletions logged with recovery info?',
			'audit-bulk-operations' => 'Are bulk actions logged?',
			'audit-schedule-tracked' => 'Are scheduled action changes logged?',
			'audit-cron-execution' => 'Are cron job executions recorded?',
			'audit-external-api' => 'Are third-party API calls logged?',
			'audit-failed-login' => 'Are failed login attempts tracked?',
			'audit-privilege-escalation' => 'Are suspicious permission gains logged?',
			'audit-orphaned-data' => 'Are deleted references cleaned with audit trail?',
			'audit-restore-safety' => 'Can site be restored to exact point-in-time?',
		),
	),

	'wordpress_ecosystem' => array(
		'label' => 'WordPress Ecosystem Health',
		'category_slug' => 'wordpress_ecosystem',
		'icon' => 'dashicons-wordpress-alt',
		'color' => '#00a0d2',
		'priority' => 1,
		'philosophy_commands' => array( 1, 8, 9 ), // Helpful neighbor, Inspire confidence, Show value
		'description' => 'One dashboard for all WordPress vitals - core, plugins, themes, database.',
		'tests' => array(
			'core-updates-available' => 'Is WordPress core behind latest version?',
			'core-auto-updates-enabled' => 'Are core auto-updates enabled?',
			'core-security-patches' => 'Are critical security patches pending?',
			'core-file-integrity' => 'Have core files been modified?',
			'core-permission-issues' => 'Are file/folder permissions correct?',
			'core-disk-space' => 'Is disk space above critical threshold?',
			'core-mysql-version' => 'Is MySQL/MariaDB version compatible?',
			'core-backups-recent' => 'Is last backup recent (< 24 hours)?',
			'core-backup-tested' => 'Have backups been tested for restoration?',
			'core-recovery-plan' => 'Is there a documented recovery procedure?',
			'plugin-count-analysis' => 'Is plugin count balanced (not bloated)?',
			'plugin-updates-pending' => 'Are plugins pending updates?',
			'plugin-security-updates' => 'Are security patches pending?',
			'plugin-abandoned' => 'Are abandoned plugins detected (2+ yrs)?',
			'plugin-conflicts-likely' => 'Are common conflict patterns detected?',
			'plugin-performance-impact' => 'Are plugins slowing the site?',
			'plugin-memory-footprint' => 'Are plugins consuming excessive memory?',
			'plugin-database-bloat' => 'Are plugins creating orphaned DB entries?',
			'plugin-beta-versions' => 'Are beta/RC plugins in production?',
			'plugin-activation-errors' => 'Do plugins have fatal errors on activation?',
			'plugin-deactivation-cleanup' => 'Do plugins leave data behind?',
			'plugin-multisite-issues' => 'Are network plugins causing problems?',
			'plugin-autoload-bloat' => 'Are plugins bloating autoload options?',
			'plugin-rest-api-exposure' => 'Are plugins exposing private data via REST?',
			'plugin-nonce-security' => 'Do plugins have unprotected forms/AJAX?',
			'theme-updates-pending' => 'Is theme update available?',
			'theme-child-theme-active' => 'Is child theme in use (safe)?',
			'theme-direct-edits' => 'Are theme files directly edited?',
			'theme-deprecated-hooks' => 'Is theme using deprecated WP hooks?',
			'theme-accessibility' => 'Does theme meet WCAG AA standards?',
			'theme-responsiveness' => 'Is theme mobile-friendly?',
			'theme-javascript-conflicts' => 'Is theme JS conflicting with plugins?',
			'theme-css-conflicts' => 'Is theme CSS causing specificity issues?',
			'theme-font-loading' => 'Are theme fonts optimized?',
			'theme-unused-templates' => 'Are there unused theme template files?',
			'database-tables-orphaned' => 'Are orphaned tables from old plugins present?',
			'database-options-bloated' => 'Is options table oversized?',
			'database-users-orphaned' => 'Are there users with no posts?',
			'database-post-revisions' => 'Are post revisions creating bloat?',
			'database-transients-stale' => 'Are expired transients in the database?',
		),
	),

	'performance_attribution' => array(
		'label' => 'Performance Attribution',
		'category_slug' => 'performance_attribution',
		'icon' => 'dashicons-performance',
		'color' => '#0891b2',
		'priority' => 1,
		'philosophy_commands' => array( 7, 9, 11 ), // Ridiculously good, Show value, Talk-worthy
		'description' => 'Per-plugin performance attribution - identify exactly which plugin is causing slowness.',
		'tests' => array(
			'plugin-ttfb-impact' => 'Which plugin delays time-to-first-byte most?',
			'plugin-query-count' => 'Which plugin generates most DB queries?',
			'plugin-query-time' => 'Which plugin queries are slowest?',
			'plugin-memory-peak' => 'Which plugin causes peak memory usage?',
			'plugin-asset-weight' => 'Which plugin loads most CSS/JS?',
			'plugin-request-time' => 'Which plugin adds most to response time?',
			'plugin-autoload-size' => 'Which plugin loads biggest autoload options?',
			'plugin-cache-misses' => 'Which plugin has worst cache efficiency?',
			'plugin-database-queries-slow' => 'Which plugin makes 100+ queries?',
			'plugin-n-plus-one' => 'Which plugin has N+1 query patterns?',
			'plugin-http-requests' => 'Which plugin makes external HTTP calls?',
			'plugin-file-system-io' => 'Which plugin does file system operations?',
			'plugin-css-specificity' => 'Which plugin has highest CSS specificity?',
			'plugin-javascript-execution' => 'Which plugin has long JS execution?',
			'plugin-database-writes' => 'Which plugin does most DB writes?',
			'plugin-transient-churn' => 'Which plugin sets/deletes transients most?',
			'plugin-cron-overhead' => 'Which plugin cron jobs are heaviest?',
			'plugin-late-loading' => 'Which plugin uses late-loading correctly?',
			'plugin-resource-headers' => 'Which plugin lacks cache headers?',
			'plugin-async-defer-missing' => 'Which plugin should be async/defer?',
			'theme-ttfb-impact' => 'Does active theme slow first byte?',
			'theme-render-blocking' => 'Is theme CSS blocking render?',
			'theme-javascript-defer' => 'Is theme JS deferred?',
			'theme-font-loading-strategy' => 'Are theme fonts optimized?',
			'theme-critical-css' => 'Does theme have critical CSS?',
			'core-query-count-total' => 'Total queries on page?',
			'core-query-time-total' => 'Total query execution time?',
			'core-memory-used-percent' => 'Memory usage vs limit?',
			'core-ttfb-baseline' => 'Time-to-first-byte baseline?',
			'core-response-time-total' => 'End-to-end response time?',
			'core-autoload-size-total' => 'Total autoload bloat?',
			'core-asset-count-total' => 'Total CSS/JS files?',
			'core-asset-size-total' => 'Total CSS/JS bytes?',
			'core-homepage-requests' => 'Total requests on homepage?',
			'core-homepage-load-time' => 'Full page load time?',
		),
	),

	'business_impact' => array(
		'label' => 'Business Impact & Revenue',
		'category_slug' => 'business_impact',
		'icon' => 'dashicons-chart-bar',
		'color' => '#10b981',
		'priority' => 1,
		'philosophy_commands' => array( 9, 11 ), // Show value, Talk-worthy
		'description' => 'Business metrics - revenue, conversions, uptime cost, visitor value.',
		'tests' => array(
			'ecommerce-conversion-rate' => '% of visitors converting to customers?',
			'ecommerce-avg-order-value' => 'Average transaction value?',
			'ecommerce-revenue-trend' => 'Revenue increasing/decreasing?',
			'ecommerce-cart-abandonment-rate' => '% of carts abandoned?',
			'ecommerce-revenue-lost-to-abandonment' => '$ value of lost carts?',
			'lead-generation-rate' => '% of visitors becoming leads?',
			'lead-quality-score' => 'Are leads qualified (BANT)?',
			'lead-to-customer-conversion' => 'How many leads convert to customers?',
			'revenue-per-visitor' => 'Average $ per unique visitor?',
			'monthly-revenue-impact' => 'Month-over-month revenue change?',
			'cost-per-acquisition-trend' => 'CPA increasing/decreasing?',
			'marketing-spend-efficiency' => 'ROI of marketing spend?',
			'traffic-cost-hosted' => 'What does each visitor cost (hosting)?',
			'development-cost-justification' => 'Is site worth ongoing investment?',
			'maintenance-cost-vs-revenue' => 'Are maintenance costs justified?',
			'page-speed-corr-to-revenue' => 'Does faster = more sales?',
			'uptime-corr-to-revenue' => 'Does more uptime = more sales?',
			'plugin-slowdown-cost' => 'Dollar impact of slow plugins?',
			'downtime-cost' => '$ cost per minute of downtime?',
			'seo-traffic-value' => 'Value of organic traffic?',
			'search-visibility-trend' => 'Are we ranking better/worse?',
			'qualified-traffic-percent' => '% of traffic likely to convert?',
			'brand-search-volume' => 'Are people searching for us?',
			'competitor-market-share' => 'Market share vs competitors?',
			'organic-traffic-sustainability' => 'Is organic growth sustainable?',
		),
	),

	'compliance_risk' => array(
		'label' => 'Compliance & Legal Risk',
		'category_slug' => 'compliance_risk',
		'icon' => 'dashicons-clipboard',
		'color' => '#8b5cf6',
		'priority' => 1,
		'philosophy_commands' => array( 10 ), // Beyond pure (privacy)
		'description' => 'GDPR, CCPA, PCI-DSS, industry-specific compliance.',
		'tests' => array(
			'gdpr-privacy-policy-exists' => 'Is privacy policy in place?',
			'gdpr-privacy-policy-current' => 'Is privacy policy recently updated?',
			'gdpr-cookies-disclosed' => 'Is cookie usage disclosed?',
			'gdpr-consent-tool-active' => 'Is cookie consent banner active?',
			'gdpr-consent-before-tracking' => 'Is consent collected before tracking?',
			'gdpr-data-retention-policy' => 'Is data retention policy documented?',
			'gdpr-data-deletion-capability' => 'Can users request data deletion?',
			'gdpr-data-portability' => 'Can users export their data?',
			'gdpr-contact-info-visible' => 'Is contact/DPA info on site?',
			'gdpr-third-party-vendors-disclosed' => 'Are vendors listed?',
			'gdpr-breach-notification-plan' => 'Is breach response plan documented?',
			'gdpr-dpia-completed' => 'Is Data Protection Impact Assessment done?',
			'ccpa-privacy-policy-exists' => 'Is CCPA-specific policy present?',
			'ccpa-opt-out-available' => 'Is "Do Not Sell" link present?',
			'ccpa-consumer-rights-disclosed' => 'Are consumer rights explained?',
			'ccpa-data-inventory-complete' => 'Is data collection inventory complete?',
			'ccpa-third-party-sales-disclosed' => 'Are third parties buying data disclosed?',
			'ccpa-retention-policy-documented' => 'How long is data kept?',
			'ccpa-sale-opt-out-working' => 'Does opt-out actually stop sales?',
			'ccpa-vendor-contracts-signed' => 'Are legal agreements in place?',
			'hipaa-pii-encryption' => 'Is patient data encrypted (healthcare)?',
			'pci-dss-compliance' => 'Is credit card security compliant (ecommerce)?',
			'coppa-compliance' => 'Is child safety compliance in place?',
			'ferpa-compliance' => 'Is student data protected (education)?',
			'sox-compliance' => 'Are financial controls in place?',
			'finra-compliance' => 'Are securities regulations followed?',
			'https-everywhere' => 'Are all pages HTTPS?',
			'tls-version-modern' => 'Is TLS 1.2+?',
			'certificate-valid' => 'Is SSL cert valid/not expired?',
			'certificate-trusted' => 'Is cert from trusted CA?',
			'sensitive-data-encrypted-rest' => 'Are passwords/keys encrypted at rest?',
			'sensitive-data-encrypted-transit' => 'Is data encrypted in flight?',
			'encryption-key-management' => 'Are keys stored securely?',
			'database-password-strength' => 'Is DB password strong?',
			'terms-of-service-exists' => 'Are Terms of Service in place?',
			'liability-insurance-documented' => 'Is cyber liability coverage documented?',
			'accessible-compliance' => 'Is site accessible (WCAG AA)?',
			'user-consent-tracking' => 'Are user consents properly tracked?',
			'data-retention-enforcement' => 'Are retention policies enforced?',
		),
	),

	// PRIORITY 2: SHOULD-HAVE (90 tests)
	// Expand reach and deepen trust

	'accessibility' => array(
		'label' => 'Accessibility & Inclusivity',
		'category_slug' => 'accessibility',
		'icon' => 'dashicons-universal-access',
		'color' => '#06b6d4',
		'priority' => 2,
		'philosophy_commands' => array( 7, 8 ), // Ridiculously good, Inspire confidence
		'description' => 'WCAG 2.1 Level AA - serve 15% more users, reduce legal liability.',
		'tests' => array(
			'wcag-color-contrast' => 'Do all text have sufficient contrast?',
			'wcag-text-resize' => 'Is text resizable to 200%?',
			'wcag-zoom-no-loss' => 'Is no content lost at 200% zoom?',
			'wcag-keyboard-nav' => 'Are all functions keyboard accessible?',
			'wcag-focus-visible' => 'Is focus indicator visible?',
			'wcag-focus-order' => 'Is tab order logical?',
			'wcag-link-purpose' => 'Do link texts describe purpose?',
			'wcag-form-labels' => 'Are form fields labeled?',
			'wcag-error-prevention' => 'Do form submits prevent errors?',
			'wcag-language' => 'Is primary language marked?',
			'wcag-abbreviations' => 'Are abbreviations explained?',
			'wcag-unusual-words' => 'Are unusual words defined?',
			'wcag-page-titled' => 'Do pages have descriptive titles?',
			'wcag-consistent-nav' => 'Is navigation consistent?',
			'wcag-consistent-id' => 'Are identical components consistent?',
			'wcag-valid-html' => 'Does HTML validate?',
			'wcag-aria-roles' => 'Are ARIA roles valid?',
			'wcag-aria-attributes' => 'Are ARIA attributes valid?',
			'wcag-aria-live-regions' => 'Are dynamic content announced?',
			'wcag-alt-text' => 'Do images have alt text?',
			'screenreader-headings-structured' => 'Is heading hierarchy correct?',
			'screenreader-form-instructions' => 'Are form instructions present?',
			'screenreader-skip-links-present' => 'Are skip links available?',
			'screenreader-aria-landmarks' => 'Are landmark regions marked?',
			'screenreader-button-text-meaningful' => 'Is button text descriptive?',
			'motor-click-targets-large' => 'Are buttons/links ≥ 44x44px?',
			'motor-double-click-alternative' => 'Are alternatives to double-click available?',
			'motor-no-motion-triggers' => 'Are there motion-induced traps?',
			'cognitive-jargon-free' => 'Does content use plain language?',
			'cognitive-instructions-clear' => 'Are procedures easy to follow?',
		),
	),

	'developer_experience' => array(
		'label' => 'Developer Experience',
		'category_slug' => 'developer_experience',
		'icon' => 'dashicons-code-alt',
		'color' => '#0ea5e9',
		'priority' => 2,
		'philosophy_commands' => array( 1, 7 ), // Helpful neighbor, Ridiculously good
		'description' => 'DX improvements - make development 10x faster.',
		'tests' => array(
			'dx-debugging-enabled' => 'Are debug tools active?',
			'dx-logging-enabled' => 'Is error logging to file?',
			'dx-query-monitor-active' => 'Is Query Monitor helpful?',
			'dx-reusable-blocks-strategy' => 'Are reusable blocks leveraged?',
			'dx-cpt-organized' => 'Are custom post types organized?',
			'dx-custom-taxonomies-strategy' => 'Are custom taxonomies effective?',
			'dx-meta-fields-structured' => 'Is custom meta organized?',
			'dx-rest-api-custom-endpoints' => 'Are REST endpoints available?',
			'dx-rest-api-pagination' => 'Are custom endpoints paginated?',
			'dx-filters-hooks-documented' => 'Are custom hooks documented?',
			'dx-multisite-awareness' => 'Are multisite patterns used?',
			'dx-cli-commands-available' => 'Are WP-CLI commands available?',
			'dx-github-action-setup' => 'Is CI/CD pipeline in place?',
			'dx-testing-coverage' => 'Does code have test coverage?',
			'dx-staging-environment' => 'Is staging environment available?',
			'dx-version-control-used' => 'Is Git/version control in use?',
			'dx-code-review-process' => 'Is code review happening?',
			'dx-api-documentation' => 'Are APIs documented?',
			'dx-local-development-setup' => 'Is local dev easy?',
			'dx-deployment-automated' => 'Is deployment automated?',
			'dx-rollback-capability' => 'Can deployments be rolled back?',
			'dx-database-sync' => 'Can database sync between environments?',
			'dx-asset-pipeline' => 'Are frontend assets optimized?',
			'dx-code-standardization' => 'Does team follow standards?',
			'dx-technical-documentation' => 'Are common tasks documented?',
		),
	),

	'user_engagement' => array(
		'label' => 'User Engagement',
		'category_slug' => 'user_engagement',
		'icon' => 'dashicons-chart-line',
		'color' => '#f59e0b',
		'priority' => 2,
		'philosophy_commands' => array( 9 ), // Show value
		'description' => 'Visitor behavior - are users actually engaged?',
		'tests' => array(
			'pageviews-trend' => 'Is page view trend positive?',
			'bounce-rate-healthy' => 'Is bounce rate healthy?',
			'avg-session-duration' => 'How long do visitors stay?',
			'user-retention' => 'What % return?',
			'new-vs-returning' => 'Is new/returning balance healthy?',
			'mobile-traffic-ratio' => 'What is mobile vs desktop split?',
			'mobile-performance-vs-desktop' => 'Is mobile slower than desktop?',
			'scroll-depth-avg' => 'How far down pages do users scroll?',
			'cta-click-rate' => 'Are CTAs being clicked?',
			'search-usage' => 'How often do users search?',
			'comment-activity' => 'What is comment engagement rate?',
			'social-shares' => 'Is content being shared?',
			'form-abandonment' => 'What % of forms are submitted?',
			'checkout-abandonment' => 'What is cart abandonment rate?',
			'time-to-conversion' => 'How long before visitor converts?',
			'device-type-performance' => 'What is performance by device?',
			'referrer-quality' => 'Are traffic sources quality?',
			'exit-page-analysis' => 'Where do visitors leave?',
			'engagement-vs-optimization' => 'Does optimization help engagement?',
			'visitor-satisfaction-proxy' => 'Are visitors happy or bouncing?',
		),
	),

	'competitor_benchmarking' => array(
		'label' => 'Competitive Benchmarking',
		'category_slug' => 'competitor_benchmarking',
		'icon' => 'dashicons-networking',
		'color' => '#ec4899',
		'priority' => 2,
		'philosophy_commands' => array( 9 ), // Show value
		'description' => 'How are we doing vs top 3 competitors?',
		'tests' => array(
			'benchmark-page-speed-vs-competitors' => 'How fast vs competitors?',
			'benchmark-mobile-performance' => 'Mobile speed vs competitors?',
			'benchmark-seo-visibility' => 'Search visibility vs competitors?',
			'benchmark-keyword-rankings' => 'Keyword rankings vs competitors?',
			'benchmark-content-volume' => 'Content volume vs competitors?',
			'benchmark-backlink-profile' => 'Link profile vs competitors?',
			'benchmark-domain-authority' => 'Domain authority vs competitors?',
			'benchmark-traffic-volume-est' => 'Estimated traffic vs competitors?',
			'benchmark-ctr-search-results' => 'Search click-through rate?',
			'benchmark-featured-snippet-ownership' => 'Featured snippet share?',
			'benchmark-rich-snippet-usage' => 'Rich snippet adoption?',
			'benchmark-social-presence' => 'Social following vs competitors?',
			'benchmark-conversion-rate-industry' => 'Conversion rate vs industry?',
			'benchmark-site-speed-industry-avg' => 'Speed vs industry average?',
			'benchmark-uptime-industry-avg' => 'Uptime vs industry standard?',
		),
	),

	// PRIORITY 3: NICE-TO-HAVE (90 tests)
	// Future-proof ecosystem

	'marketing_growth' => array(
		'label' => 'Marketing & Growth',
		'category_slug' => 'marketing_growth',
		'icon' => 'dashicons-trending-up',
		'color' => '#f97316',
		'priority' => 3,
		'philosophy_commands' => array( 11 ), // Talk-worthy
		'description' => 'Systematic growth - not just hope.',
		'tests' => array(
			'marketing-email-list-size' => 'Is mailing list substantial?',
			'marketing-email-engagement' => 'Is email being read?',
			'marketing-newsletter-frequency' => 'Is content cadence regular?',
			'marketing-lead-magnet' => 'Is lead magnet converting?',
			'marketing-cta-count' => 'Are enough CTAs on site?',
			'marketing-cta-placement' => 'Are CTAs in optimal places?',
			'marketing-social-integrated' => 'Is social linked/embedded?',
			'marketing-social-frequency' => 'Is social content regular?',
			'marketing-social-engagement' => 'Are followers engaging?',
			'marketing-ad-strategy' => 'Are ads driving traffic?',
			'marketing-affiliate-partners' => 'Is revenue from affiliates?',
			'marketing-content-calendar' => 'Is content planned ahead?',
			'marketing-content-repurposing' => 'Is content reused across channels?',
			'marketing-partnership-strategy' => 'Are partnerships driving growth?',
			'marketing-press-coverage' => 'Are we getting media mentions?',
			'marketing-referral-program' => 'Are customers referring others?',
			'marketing-webinar-strategy' => 'Are webinars used for demand gen?',
			'marketing-podcast-presence' => 'Is podcast strategy in place?',
			'marketing-brand-awareness' => 'Is brand awareness growing?',
			'marketing-competitive-share' => 'Is market share growing?',
		),
	),

	'customer_retention' => array(
		'label' => 'Customer Retention',
		'category_slug' => 'customer_retention',
		'icon' => 'dashicons-smiley',
		'color' => '#14b8a6',
		'priority' => 3,
		'philosophy_commands' => array( 11 ), // Talk-worthy
		'description' => 'Build sustainable business, not churn treadmill.',
		'tests' => array(
			'retention-nps-score' => 'How likely to recommend (NPS)?',
			'retention-customer-satisfaction' => 'What is customer satisfaction score?',
			'retention-churn-rate' => 'What % of customers are leaving?',
			'retention-repeat-purchase-rate' => 'Are customers buying again?',
			'retention-customer-lifetime-value' => 'Is LTV increasing?',
			'retention-support-response-time' => 'Is support replying quickly?',
			'retention-support-satisfaction' => 'Are customers happy with support?',
			'retention-onboarding-completion' => 'Are new users completing onboarding?',
			'retention-feature-adoption' => 'Are users using new features?',
			'retention-help-documentation' => 'Are users finding help?',
			'retention-community-engagement' => 'Are users helping each other?',
			'retention-user-feedback-loop' => 'Are customer feedback collected?',
			'retention-roadmap-transparency' => 'Do customers see direction?',
			'retention-beta-program' => 'Are beta testers engaged?',
			'retention-advisory-board' => 'Are customers in strategy?',
			'retention-win-back-campaign' => 'Are lost customers re-engaged?',
			'retention-upsell-opportunity' => 'Are customers ready for upgrade?',
			'retention-cross-sell-opportunity' => 'Could customers use other products?',
			'retention-segment-satisfaction' => 'Are different segments happy?',
			'retention-price-competitiveness' => 'Is price causing churn?',
		),
	),

	'seo_discovery' => array(
		'label' => 'SEO & Discovery (Enhanced)',
		'category_slug' => 'seo_discovery',
		'icon' => 'dashicons-search',
		'color' => '#2563eb',
		'priority' => 3,
		'philosophy_commands' => array( 5, 6 ), // Drive to KB, Drive to training
		'description' => 'Strategic SEO - grow organic traffic.',
		'tests' => array(
			'seo-content-pillar-strategy' => 'Is content pillar structure in place?',
			'seo-topical-authority' => 'Is topical authority being built?',
			'seo-link-cluster-strategy' => 'Do internal links create topic clusters?',
			'seo-entity-recognition' => 'Is Google recognizing entity?',
			'seo-featured-snippet-targets' => 'Is content optimized for snippets?',
			'seo-people-also-ask-coverage' => 'Are "People Also Ask" covered?',
			'seo-voice-search-ready' => 'Is content optimized for voice?',
			'seo-semantic-seo' => 'Are semantic relationships marked?',
			'seo-eeat-signals' => 'Are E-E-A-T signals present?',
			'seo-author-authority' => 'Are author credentials visible?',
			'seo-reviews-aggregate-rating' => 'Is review schema present?',
			'seo-breadcrumb-implementation' => 'Are breadcrumbs in place?',
			'seo-faq-schema-coverage' => 'Is FAQ schema on applicable pages?',
			'seo-how-to-schema' => 'Is how-to schema for procedures?',
			'seo-video-optimization' => 'Is video content indexed?',
			'seo-image-optimization' => 'Are images tagged for search?',
			'seo-mobile-first-indexing' => 'Is mobile version crawlable?',
			'seo-page-experience' => 'Are Core Web Vitals optimized?',
			'seo-spam-penalties-risk' => 'Is manual spam action risk low?',
			'seo-recovery-plan' => 'Is recovery strategy documented?',
		),
	),

	'sustainability' => array(
		'label' => 'Sustainability & Long-Term Health',
		'category_slug' => 'sustainability',
		'icon' => 'dashicons-building',
		'color' => '#059669',
		'priority' => 3,
		'philosophy_commands' => array( 11 ), // Talk-worthy
		'description' => 'Will this site work in 5 years?',
		'tests' => array(
			'sustainability-hosting-quality' => 'Is hosting provider reliable?',
			'sustainability-technical-debt' => 'How much technical debt?',
			'sustainability-code-maintainability' => 'Is code easy to maintain?',
			'sustainability-dependency-freshness' => 'Are dependencies updated?',
			'sustainability-plugin-update-velocity' => 'Are plugins kept current?',
			'sustainability-wordpress-core-drift' => 'Is core version lag minimal?',
			'sustainability-knowledge-transfer-risk' => 'What is developer departure risk?',
			'sustainability-documentation-quality' => 'Is code documented?',
			'sustainability-monitoring-coverage' => 'Are issues caught proactively?',
			'sustainability-backup-redundancy' => 'Are backups in multiple locations?',
			'sustainability-disaster-recovery-time' => 'How fast can we recover?',
			'sustainability-vendor-lock-in' => 'Is plugin dependency too high?',
			'sustainability-api-deprecation-risk' => 'Are deprecated APIs used?',
			'sustainability-compliance-drift' => 'Are policies staying current?',
			'sustainability-cost-efficiency' => 'Are costs trending up or down?',
		),
	),

	'ai_readiness' => array(
		'label' => 'AI & ML Readiness',
		'category_slug' => 'ai_readiness',
		'icon' => 'dashicons-lightbulb',
		'color' => '#a855f7',
		'priority' => 3,
		'philosophy_commands' => array( 7 ), // Ridiculously good
		'description' => 'Future-proof for AI-powered systems.',
		'tests' => array(
			'ai-structured-data' => 'Is schema markup for AI present?',
			'ai-content-quality-llm' => 'Is content LLM-friendly?',
			'ai-knowledge-base-buildable' => 'Is data organized for KB?',
			'ai-chatbot-readiness' => 'Can intelligent chatbot be supported?',
			'ai-recommendation-engine' => 'Is data available for recommendations?',
			'ai-predictive-analytics' => 'Is historical data available?',
			'ai-personalization-infrastructure' => 'Is personalization infrastructure ready?',
			'ai-nlp-readiness' => 'Is content optimized for NLP?',
			'ai-image-alt-text' => 'Are images described for AI?',
			'ai-video-transcripts' => 'Are video transcripts available?',
			'ai-sentiment-analysis' => 'Is content ready for sentiment analysis?',
			'ai-api-integrations' => 'Is AI API strategy documented?',
			'ai-training-data-quality' => 'Is training data clean/unbiased?',
			'ai-ethical-ai-policy' => 'Are ethical AI guidelines in place?',
			'ai-user-privacy' => 'Is privacy maintained with AI?',
		),
	),
);

// Create diagnostic stub files
$diagnostic_dir = __DIR__ . '/includes/diagnostics';
$created_count = 0;

foreach ( $categories as $category_key => $category_data ) {
	foreach ( $category_data['tests'] as $test_slug => $test_label ) {
		$class_name = 'Diagnostic_' . str_replace( '-', '_', ucwords( $test_slug, '-' ) );
		$class_name = preg_replace_callback( '/_([a-z])/', function( $m ) { return strtoupper( $m[1] ); }, $class_name );
		
		$file_path = $diagnostic_dir . '/class-diagnostic-' . $test_slug . '.php';
		
		// Skip if already exists
		if ( file_exists( $file_path ) ) {
			echo "SKIP: $test_slug (already exists)\n";
			continue;
		}
		
		$namespace_class = 'WPShadow\\Diagnostics\\' . $class_name;
		$template = <<<'STUB'
<?php
declare( strict_types=1 );

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: {TEST_LABEL}
 *
 * Category: {CATEGORY_NAME}
 * Priority: {PRIORITY}
 * Philosophy: {PHILOSOPHY_COMMANDS}
 *
 * Test Description:
 * {TEST_LABEL}
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */
class {CLASS_NAME} extends Diagnostic_Base {
	
	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return '{TEST_SLUG}';
	}
	
	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __('{TEST_LABEL}', 'wpshadow');
	}
	
	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __('{TEST_LABEL}. Part of {CATEGORY_NAME} analysis.', 'wpshadow');
	}
	
	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return '{CATEGORY_SLUG}';
	}
	
	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// TODO: Implement {TEST_LABEL} test
		// This is a stub for {CATEGORY_NAME} category
		// Philosophy focus: {PHILOSOPHY_COMMANDS}
		//
		// IMPLEMENTATION NOTES:
		// - Check if {TEST_LABEL}
		// - Return finding with severity, threat level, and resolution advice
		// - Link to knowledge base article for user education
		// - Consider business impact (commandment #9: Show Value)
		// - Make sure output is user-friendly (commandment #1: Helpful Neighbor)
		
		return array();
	}
	
	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// TODO: Set appropriate threat level
		// 0-30: Low
		// 31-60: Medium
		// 61-100: High/Critical
		return 50;
	}
	
	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/{TEST_SLUG}/';
	}
	
	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/{TEST_SLUG}/';
	}
}
STUB;
		
		$philosophy_commands = implode( ', ', $category_data['philosophy_commands'] );
		
		$content = str_replace(
			array(
				'{TEST_LABEL}',
				'{CATEGORY_NAME}',
				'{CATEGORY_SLUG}',
				'{PRIORITY}',
				'{PHILOSOPHY_COMMANDS}',
				'{TEST_SLUG}',
				'{CLASS_NAME}',
			),
			array(
				$test_label,
				$category_data['label'],
				$category_data['category_slug'],
				$category_data['priority'],
				$philosophy_commands,
				$test_slug,
				$class_name,
			),
			$template
		);
		
		file_put_contents( $file_path, $content );
		$created_count++;
		echo "CREATE: $test_slug\n";
	}
}

echo "\n✅ Created $created_count diagnostic stub files!\n";
echo "Next: Run 'composer phpcs' to validate syntax\n";
echo "Then: Update includes/diagnostics/class-diagnostic-registry.php\n";
