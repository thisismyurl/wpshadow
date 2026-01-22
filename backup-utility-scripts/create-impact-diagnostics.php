<?php
/**
 * Generate Impact & Operations Diagnostic Stubs
 * 
 * Phase 4.5 - Environment, Users, Content Publishing
 * Creates comprehensive test stubs for three new gauge categories
 */

$tests = array(
	// ====================
	// ENVIRONMENT IMPACT (30 tests)
	// ====================
	'environment' => array(
		'label' => 'Environment & Impact',
		'category_slug' => 'environment',
		'icon' => 'dashicons-leaf',
		'color' => '#10b981',
		'priority' => 3,
		'philosophy_commands' => '7, 8, 9',
		'description' => 'Measure and reduce environmental impact of WordPress site',
		'tests' => array(
			array(
				'id' => 'env-dark-mode-support',
				'name' => 'Dark Mode Support in Admin',
				'description' => 'Does admin interface support dark mode to reduce energy consumption?',
			),
			array(
				'id' => 'env-dark-mode-adoption-rate',
				'name' => 'Dark Mode Adoption Rate',
				'description' => 'What % of admins/editors are using dark mode?',
			),
			array(
				'id' => 'env-carbon-offset-calculated',
				'name' => 'Carbon Offset Calculated',
				'description' => 'Is site carbon footprint tracked and offset calculated?',
			),
			array(
				'id' => 'env-page-weight-optimization',
				'name' => 'Average Page Weight',
				'description' => 'What is average KB per page? Smaller = less bandwidth = less energy',
			),
			array(
				'id' => 'env-lazy-loading-images',
				'name' => 'Lazy Loading on Images',
				'description' => 'Are images lazy-loaded to reduce unnecessary data transfer?',
			),
			array(
				'id' => 'env-lazy-loading-adoption',
				'name' => 'Lazy Loading Adoption %',
				'description' => 'What % of images actually have lazy loading enabled?',
			),
			array(
				'id' => 'env-video-optimization',
				'name' => 'Video Optimization',
				'description' => 'Videos using efficient codecs and adaptive bitrate streaming?',
			),
			array(
				'id' => 'env-font-loading-strategy',
				'name' => 'Font Loading Strategy',
				'description' => 'Using font-display:swap to avoid blocking render?',
			),
			array(
				'id' => 'env-unnecessary-plugins',
				'name' => 'Unnecessary Plugins Installed',
				'description' => 'Inactive plugins still consume energy (loading, updates)',
			),
			array(
				'id' => 'env-database-bloat-queries',
				'name' => 'Database Query Efficiency',
				'description' => 'Inefficient queries waste CPU and energy',
			),
			array(
				'id' => 'env-caching-effectiveness',
				'name' => 'Cache Hit Rate',
				'description' => 'What % of requests served from cache vs regenerated?',
			),
			array(
				'id' => 'env-cdn-usage',
				'name' => 'CDN in Use',
				'description' => 'Serving assets from CDN closer to users = less bandwidth',
			),
			array(
				'id' => 'env-cdn-data-served',
				'name' => '% Data Served from CDN',
				'description' => 'What percentage of total bandwidth from CDN?',
			),
			array(
				'id' => 'env-server-efficiency',
				'name' => 'Server Resource Efficiency',
				'description' => 'CPU usage per request - lower is greener',
			),
			array(
				'id' => 'env-green-hosting-verification',
				'name' => 'Green Hosting Provider',
				'description' => 'Is hosting provider carbon-neutral or using renewables?',
			),
			array(
				'id' => 'env-animation-usage',
				'name' => 'Heavy Animations Detected',
				'description' => 'Excessive animations increase CPU/battery drain on devices',
			),
			array(
				'id' => 'env-autoplay-video-disabled',
				'name' => 'Autoplay Videos',
				'description' => 'Autoplay wastes bandwidth on users who don\'t watch',
			),
			array(
				'id' => 'env-compression-enabled',
				'name' => 'Gzip/Brotli Compression',
				'description' => 'Assets compressed to reduce bandwidth',
			),
			array(
				'id' => 'env-image-optimization-score',
				'name' => 'Image Optimization Score',
				'description' => 'Images properly sized for display context',
			),
			array(
				'id' => 'env-webp-adoption',
				'name' => 'WebP Format Adoption',
				'description' => 'Modern image formats reduce file size',
			),
			array(
				'id' => 'env-critical-css-strategy',
				'name' => 'Critical CSS Extraction',
				'description' => 'Load only critical CSS to speed initial render',
			),
			array(
				'id' => 'env-unused-css-detection',
				'name' => 'Unused CSS Detected',
				'description' => 'Dead CSS files waste bandwidth and parsing time',
			),
			array(
				'id' => 'env-unused-javascript',
				'name' => 'Unused JavaScript',
				'description' => 'Dead code wastes bandwidth and execution time',
			),
			array(
				'id' => 'env-request-count-total',
				'name' => 'Total Requests per Page',
				'description' => 'Fewer requests = less energy (more efficient)',
			),
			array(
				'id' => 'env-energy-score-calculated',
				'name' => 'Environmental Impact Score',
				'description' => 'Overall energy efficiency rating (like pagespeed insights)',
			),
			array(
				'id' => 'env-carbon-offset-invested',
				'name' => 'Carbon Offset Investment',
				'description' => 'Is site owner offsetting their carbon footprint?',
			),
			array(
				'id' => 'env-eco-hosting-commitment',
				'name' => 'Eco Hosting Badge/Commitment',
				'description' => 'Has site committed to sustainable practices publicly?',
			),
			array(
				'id' => 'env-visitor-device-breakdown',
				'name' => 'Visitor Device Breakdown',
				'description' => 'What % mobile vs desktop (mobile = more energy-efficient)',
			),
			array(
				'id' => 'env-monthly-bandwidth-trend',
				'name' => 'Bandwidth Usage Trend',
				'description' => 'Is bandwidth usage growing or shrinking?',
			),
			array(
				'id' => 'env-hosting-energy-report',
				'name' => 'Hosting Provider Energy Report',
				'description' => 'Transparency from host on their energy usage/offsetting',
			),
		),
	),

	// ====================
	// USERS & TEAM (25 tests)
	// ====================
	'users' => array(
		'label' => 'Users & Team',
		'category_slug' => 'users',
		'icon' => 'dashicons-groups',
		'color' => '#3b82f6',
		'priority' => 3,
		'philosophy_commands' => '1, 8, 9',
		'description' => 'Track team productivity, user behavior, and staff activity patterns',
		'tests' => array(
			array(
				'id' => 'users-admin-count',
				'name' => 'Active Administrators',
				'description' => 'How many admins have access?',
			),
			array(
				'id' => 'users-admin-login-frequency',
				'name' => 'Admin Login Frequency',
				'description' => 'How often are admins accessing site?',
			),
			array(
				'id' => 'users-admin-last-login',
				'name' => 'Admin Last Login Date',
				'description' => 'When did each admin last access site?',
			),
			array(
				'id' => 'users-editor-count',
				'name' => 'Active Editors',
				'description' => 'How many content editors do you have?',
			),
			array(
				'id' => 'users-editor-activity-level',
				'name' => 'Editor Activity Level',
				'description' => 'Which editors are actively creating/editing content?',
			),
			array(
				'id' => 'users-author-count',
				'name' => 'Active Authors',
				'description' => 'How many content authors?',
			),
			array(
				'id' => 'users-author-productivity',
				'name' => 'Author Productivity (Posts/Month)',
				'description' => 'Average posts per author per month',
			),
			array(
				'id' => 'users-contributor-count',
				'name' => 'Contributor Count',
				'description' => 'How many contributors?',
			),
			array(
				'id' => 'users-inactive-accounts',
				'name' => 'Inactive User Accounts',
				'description' => 'Users who haven\'t logged in for 90+ days?',
			),
			array(
				'id' => 'users-profile-completion-overall',
				'name' => 'Average Profile Completion %',
				'description' => 'What % of user profile fields are filled?',
			),
			array(
				'id' => 'users-profile-photo-adoption',
				'name' => 'User Profile Photo Adoption',
				'description' => 'What % of users have profile photos?',
			),
			array(
				'id' => 'users-bio-completion',
				'name' => 'User Bio/Description Completion',
				'description' => 'What % of users have bios filled?',
			),
			array(
				'id' => 'users-social-profile-links',
				'name' => 'Social Profile Links',
				'description' => 'What % of users linked social profiles?',
			),
			array(
				'id' => 'users-role-distribution',
				'name' => 'User Role Distribution',
				'description' => 'Breakdown of users by role (admin, editor, author, etc)',
			),
			array(
				'id' => 'users-customer-login-frequency',
				'name' => 'Customer Login Frequency',
				'description' => 'For membership/customer sites: how often do customers log in?',
			),
			array(
				'id' => 'users-customer-engagement-score',
				'name' => 'Customer Engagement Score',
				'description' => 'Aggregate score of customer activity',
			),
			array(
				'id' => 'users-password-change-frequency',
				'name' => 'Password Change Frequency',
				'description' => 'When did users last change passwords? (Security hygiene)',
			),
			array(
				'id' => 'users-two-factor-adoption',
				'name' => '2FA Adoption Rate',
				'description' => 'What % of users have two-factor auth enabled?',
			),
			array(
				'id' => 'users-admin-2fa-required',
				'name' => '2FA Required for Admins',
				'description' => 'Is 2FA enforced for admin accounts?',
			),
			array(
				'id' => 'users-permission-scope-creep',
				'name' => 'Permission Scope Creep',
				'description' => 'Do users have more permissions than their role needs?',
			),
			array(
				'id' => 'users-orphaned-accounts',
				'name' => 'Orphaned User Accounts',
				'description' => 'Deleted users whose content still exists',
			),
			array(
				'id' => 'users-support-ticket-by-user',
				'name' => 'Support Tickets by User',
				'description' => 'Who is creating most support tickets?',
			),
			array(
				'id' => 'users-comment-activity-top-authors',
				'name' => 'Top Commenters/Contributors',
				'description' => 'Who is most active in comments?',
			),
			array(
				'id' => 'users-api-token-activity',
				'name' => 'API Token Usage',
				'description' => 'Who is using API tokens? Are they active?',
			),
			array(
				'id' => 'users-session-duration-avg',
				'name' => 'Average Session Duration',
				'description' => 'How long do admin sessions last?',
			),
		),
	),

	// ====================
	// CONTENT PUBLISHING (50+ tests)
	// ====================
	'content_publishing' => array(
		'label' => 'Content Publishing',
		'category_slug' => 'content_publishing',
		'icon' => 'dashicons-edit',
		'color' => '#f59e0b',
		'priority' => 2,
		'philosophy_commands' => '7, 8, 9',
		'description' => 'Pre-publication audit: comprehensive checks before content goes live',
		'tests' => array(
			// Content Quality
			array(
				'id' => 'pub-title-length',
				'name' => 'Title Length Check',
				'description' => 'Is title between 30-60 characters? (SEO optimal)',
			),
			array(
				'id' => 'pub-title-keyword',
				'name' => 'Primary Keyword in Title',
				'description' => 'Does title contain main keyword?',
			),
			array(
				'id' => 'pub-description-length',
				'name' => 'Meta Description Length',
				'description' => 'Is description 120-160 characters?',
			),
			array(
				'id' => 'pub-content-length',
				'name' => 'Content Length Check',
				'description' => 'Is content long enough (500+ words for depth)?',
			),
			array(
				'id' => 'pub-content-too-long',
				'name' => 'Content Too Long Warning',
				'description' => 'Is content so long it needs chunking (10K+ words)?',
			),
			array(
				'id' => 'pub-readability-score',
				'name' => 'Readability Score',
				'description' => 'Flesch-Kincaid grade level (target: 8th grade)',
			),
			array(
				'id' => 'pub-sentence-variety',
				'name' => 'Sentence Variety',
				'description' => 'Mix of short and long sentences?',
			),
			array(
				'id' => 'pub-paragraph-length-check',
				'name' => 'Paragraph Length Check',
				'description' => 'Paragraphs too long (wall of text)?',
			),

			// Images
			array(
				'id' => 'pub-image-count',
				'name' => 'Image Count in Content',
				'description' => 'Does content have at least one image?',
			),
			array(
				'id' => 'pub-image-count-too-many',
				'name' => 'Image Count Too High',
				'description' => 'More than one image per 300 words? (May be too many)',
			),
			array(
				'id' => 'pub-alt-text-coverage',
				'name' => 'Alt Text Coverage',
				'description' => 'Do all images have alt text?',
			),
			array(
				'id' => 'pub-alt-text-descriptive',
				'name' => 'Alt Text is Descriptive',
				'description' => 'Alt text more than just filename?',
			),
			array(
				'id' => 'pub-featured-image-present',
				'name' => 'Featured Image Present',
				'description' => 'Does post have a featured image?',
			),
			array(
				'id' => 'pub-featured-image-dimension',
				'name' => 'Featured Image Dimensions',
				'description' => 'Is featured image at optimal size?',
			),
			array(
				'id' => 'pub-images-optimized',
				'name' => 'Images Are Optimized',
				'description' => 'Images compressed and in modern formats?',
			),

			// Links
			array(
				'id' => 'pub-internal-links-count',
				'name' => 'Internal Links Count',
				'description' => 'At least 2-3 internal links per post?',
			),
			array(
				'id' => 'pub-internal-links-anchor-text',
				'name' => 'Internal Link Anchor Text',
				'description' => 'Links use descriptive anchor text (not "click here")?',
			),
			array(
				'id' => 'pub-external-links-present',
				'name' => 'External Links Present',
				'description' => 'Content references external sources?',
			),
			array(
				'id' => 'pub-external-links-working',
				'name' => 'External Links Working',
				'description' => 'Do all external links resolve?',
			),
			array(
				'id' => 'pub-external-links-nofollow',
				'name' => 'External Links Have Nofollow',
				'description' => 'Are affiliate/untrusted links marked nofollow?',
			),
			array(
				'id' => 'pub-broken-internal-links',
				'name' => 'Broken Internal Links',
				'description' => 'Any internal links 404?',
			),

			// SEO & Keywords
			array(
				'id' => 'pub-keyword-density',
				'name' => 'Keyword Density',
				'description' => 'Primary keyword appears naturally (0.5-2.5%)?',
			),
			array(
				'id' => 'pub-keyword-in-headings',
				'name' => 'Keyword in Headings',
				'description' => 'Primary keyword in H1 or H2?',
			),
			array(
				'id' => 'pub-synonym-variations',
				'name' => 'Synonym Usage',
				'description' => 'Using keyword synonyms and variations?',
			),
			array(
				'id' => 'pub-slug-optimization',
				'name' => 'URL Slug Optimized',
				'description' => 'Slug is short and keyword-relevant?',
			),
			array(
				'id' => 'pub-heading-hierarchy',
				'name' => 'Heading Hierarchy Correct',
				'description' => 'H1 → H2 → H3 structure (no gaps)?',
			),
			array(
				'id' => 'pub-heading-count',
				'name' => 'Heading Count',
				'description' => 'At least 3-5 subheadings?',
			),

			// Structured Data & Technical
			array(
				'id' => 'pub-schema-markup-present',
				'name' => 'Schema Markup Present',
				'description' => 'Article schema, FAQ, recipe, etc?',
			),
			array(
				'id' => 'pub-og-tags-complete',
				'name' => 'Open Graph Tags Complete',
				'description' => 'og:title, og:image, og:description?',
			),
			array(
				'id' => 'pub-twitter-card-present',
				'name' => 'Twitter Card Present',
				'description' => 'Twitter card metadata for sharing?',
			),
			array(
				'id' => 'pub-canonical-tag',
				'name' => 'Canonical Tag Present',
				'description' => 'Is canonical tag set (avoid duplicates)?',
			),

			// Temporal & Relevance
			array(
				'id' => 'pub-year-references-check',
				'name' => 'Year References Audit',
				'description' => 'Does content reference years? (e.g., "Best of 2025")',
			),
			array(
				'id' => 'pub-year-references-updateable',
				'name' => 'Year References Are Updateable',
				'description' => 'If content has year references, is it easily updatable?',
			),
			array(
				'id' => 'pub-outdated-references-detected',
				'name' => 'Outdated References Detected',
				'description' => 'Content references events/stats from years ago?',
			),
			array(
				'id' => 'pub-statistics-sourced',
				'name' => 'Statistics Are Sourced',
				'description' => 'Claims backed by citations/sources?',
			),
			array(
				'id' => 'pub-publication-date-set',
				'name' => 'Publication Date Set',
				'description' => 'Is publish date properly configured?',
			),
			array(
				'id' => 'pub-update-date-recent',
				'name' => 'Update Date Current',
				'description' => 'If updating content, is update date current?',
			),

			// Accessibility
			array(
				'id' => 'pub-color-contrast-sufficient',
				'name' => 'Color Contrast Sufficient',
				'description' => 'Text has WCAG AA contrast ratio?',
			),
			array(
				'id' => 'pub-buttons-accessible',
				'name' => 'Buttons Are Accessible',
				'description' => 'Buttons have proper labels/ARIA?',
			),
			array(
				'id' => 'pub-forms-accessible',
				'name' => 'Forms Are Accessible',
				'description' => 'Form fields properly labeled?',
			),
			array(
				'id' => 'pub-table-headers',
				'name' => 'Table Headers Marked',
				'description' => 'Data tables have proper header markup?',
			),
			array(
				'id' => 'pub-video-transcripts',
				'name' => 'Video Has Transcripts',
				'description' => 'Embedded videos have captions/transcripts?',
			),

			// Metadata & Assignment
			array(
				'id' => 'pub-category-assigned',
				'name' => 'Category Assigned',
				'description' => 'Post assigned to appropriate category?',
			),
			array(
				'id' => 'pub-tags-added',
				'name' => 'Tags Added',
				'description' => 'At least 3-5 relevant tags?',
			),
			array(
				'id' => 'pub-excerpt-present',
				'name' => 'Excerpt Present',
				'description' => 'Has custom excerpt (not auto-generated)?',
			),
			array(
				'id' => 'pub-author-set',
				'name' => 'Author Set Correctly',
				'description' => 'Post author set and bio filled?',
			),
			array(
				'id' => 'pub-author-bio-complete',
				'name' => 'Author Bio Complete',
				'description' => 'Author profile has bio, photo, social?',
			),

			// Call to Action & Engagement
			array(
				'id' => 'pub-cta-present',
				'name' => 'Call-to-Action Present',
				'description' => 'Content includes CTA (email, purchase, contact)?',
			),
			array(
				'id' => 'pub-cta-clear',
				'name' => 'CTA is Clear & Compelling',
				'description' => 'CTA uses action words and stands out?',
			),
			array(
				'id' => 'pub-related-posts-linked',
				'name' => 'Related Posts Linked',
				'description' => 'Links to related content for engagement?',
			),

			// Final Checks
			array(
				'id' => 'pub-grammar-spell-check',
				'name' => 'Grammar & Spelling Check',
				'description' => 'No obvious grammar/spelling errors?',
			),
			array(
				'id' => 'pub-mobile-preview-checked',
				'name' => 'Mobile Preview Checked',
				'description' => 'Has content been previewed on mobile?',
			),
			array(
				'id' => 'pub-read-through-complete',
				'name' => 'Content Proof-Read',
				'description' => 'Has full content been read through?',
			),
			array(
				'id' => 'pub-compliance-check',
				'name' => 'Compliance Check',
				'description' => 'Content complies with brand guidelines?',
			),
		),
	),
);

// Generate diagnostic stub files
$total_created = 0;
$total_skipped = 0;

foreach ( $tests as $category_key => $category_data ) {
	foreach ( $category_data['tests'] as $test ) {
		$class_name = 'Diagnostic_' . str_replace( '-', '_', ucwords( $test['id'], '-' ) );
		$filename = 'class-diagnostic-' . $test['id'] . '.php';
		$filepath = __DIR__ . '/includes/diagnostics/' . $filename;

		// Skip if already exists
		if ( file_exists( $filepath ) ) {
			echo "SKIP: {$filename} (already exists)\n";
			$total_skipped++;
			continue;
		}

		$content = <<<'PHP'
<?php
declare( strict_types=1 );

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: {NAME}
 *
 * Category: {CATEGORY}
 * Priority: {PRIORITY}
 * Philosophy: {PHILOSOPHY}
 *
 * Test Description:
 * {DESCRIPTION}
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */
class {CLASS_NAME} extends Diagnostic_Base {
	
	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return '{ID}';
	}
	
	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( '{NAME}', 'wpshadow' );
	}
	
	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( '{DESCRIPTION}', 'wpshadow' );
	}
	
	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return '{CATEGORY_SLUG}';
	}
	
	/**
	 * Get threat level
	 *
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return {THREAT};
	}
	
	/**
	 * Run diagnostic test
	 *
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		// TODO: Implement {ID} test
		// Philosophy focus: Commandment #{PHILOSOPHY}
		// 
		// Data collection strategy:
		// - Gather relevant metrics from WordPress
		// - Calculate or query necessary values
		// - Return structured result
		//
		// KB Article: https://wpshadow.com/kb/{KB_SLUG}
		// Training: https://wpshadow.com/training/{TRAINING_SLUG}
		//
		// User impact: {USER_IMPACT}
		
		return array(
			'status' => 'todo',
			'message' => 'Diagnostic not yet implemented',
			'data' => array(),
		);
	}
	
	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/{KB_SLUG}';
	}
	
	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/{TRAINING_SLUG}';
	}
}
PHP;

		// Replace placeholders
		$user_impact = '';
		switch ( $category_key ) {
			case 'environment':
				$user_impact = 'Help users understand and reduce environmental footprint of their site. Feel-good metrics with genuine impact on energy consumption and carbon offset.';
				$threat = '10';
				break;
			case 'users':
				$user_impact = 'Give site owners visibility into team productivity and customer engagement patterns. Identify inactive accounts, track admin activity.';
				$threat = '15';
				break;
			case 'content_publishing':
				$user_impact = 'Comprehensive pre-publication audit ensures content meets quality standards, SEO best practices, and accessibility requirements before going live.';
				$threat = '25';
				break;
		}

		$kb_slug = str_replace( '_', '-', $test['id'] );
		$training_slug = 'category-' . str_replace( '_', '-', $category_key );

		$content = str_replace(
			array(
				'{NAME}',
				'{CATEGORY}',
				'{CATEGORY_SLUG}',
				'{PRIORITY}',
				'{PHILOSOPHY}',
				'{DESCRIPTION}',
				'{CLASS_NAME}',
				'{ID}',
				'{THREAT}',
				'{KB_SLUG}',
				'{TRAINING_SLUG}',
				'{USER_IMPACT}',
			),
			array(
				$test['name'],
				$category_data['label'],
				$category_data['category_slug'],
				$category_data['priority'],
				$category_data['philosophy_commands'],
				$test['description'],
				$class_name,
				$test['id'],
				$threat,
				$kb_slug,
				$training_slug,
				$user_impact,
			),
			$content
		);

		file_put_contents( $filepath, $content );
		echo "CREATE: {$filename}\n";
		$total_created++;
	}
}

echo "\n✅ Created {$total_created} diagnostic stub files!\n";
echo "⏭️  Skipped {$total_skipped} existing files\n";
echo "📊 Total stubs for Phase 4.5: " . ( $total_created + $total_skipped ) . "\n";
echo "\nNext: Update includes/diagnostics/class-diagnostic-registry.php\n";
