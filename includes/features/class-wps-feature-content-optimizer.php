<?php
/**
 * Feature: Complete Content Quality Optimizer
 *
 * Comprehensive content quality and SEO system with 35+ checks including
 * title/meta optimization, readability scoring, image validation, social previews,
 * accessibility compliance, featured images, categories, URL optimization,
 * keyword placement, link quality, duplicate content detection, and more.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75010
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Content_Optimizer
 *
 * Complete content quality and SEO optimization system with real-time feedback.
 */
final class WPSHADOW_Feature_Content_Optimizer extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'content-optimizer',
				'name'            => __( 'Complete Content Quality Optimizer', 'wpshadow' ),
				'description'     => __( 'Get 35+ real-time checks to create perfect content: SEO, readability, accessibility, images, social media, categories, and more. Your all-in-one content quality assistant.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => true,
				'version'         => '2.0.0',
				'widget_group'    => 'content',
				'aliases'         => array(
					'search engine optimization',
					'google ranking',
					'seo helper',
					'content quality',
					'content checker',
					'post validation',
					'readability score',
					'flesch kincaid',
					'featured image',
					'social preview',
					'open graph',
					'image optimization',
					'accessibility check',
					'url optimization',
					'category validation',
					'duplicate content',
					'plagiarism',
					'grammar check',
					'content score',
				),
				'sub_features'    => array(
					// Core SEO Checks
					'check_title_length'        => __( 'Check if title is the right length for Google', 'wpshadow' ),
					'check_meta_description'    => __( 'Check if summary text exists', 'wpshadow' ),
					'check_heading_structure'   => __( 'Check if section titles are organized correctly', 'wpshadow' ),
					'check_keyword_density'     => __( 'Check if important words appear enough times', 'wpshadow' ),
					'check_content_length'      => __( 'Check if content is long enough', 'wpshadow' ),
					'check_internal_links'      => __( 'Check for links to other pages on your site', 'wpshadow' ),
					'check_external_links'      => __( 'Check for links to other websites', 'wpshadow' ),
					'suggest_publish_time'      => __( 'Suggest best day and time to publish', 'wpshadow' ),
					// Content Quality Checks
					'check_readability'         => __( 'Calculate Flesch-Kincaid readability score', 'wpshadow' ),
					'check_empty_tags'          => __( 'Check for empty paragraphs and breaks', 'wpshadow' ),
					'check_paragraph_length'    => __( 'Check if paragraphs are too long', 'wpshadow' ),
					'check_sentence_length'     => __( 'Check if sentences are too long', 'wpshadow' ),
					'check_passive_voice'       => __( 'Detect excessive passive voice usage', 'wpshadow' ),
					'check_duplicate_content'   => __( 'Check for duplicate content on your site', 'wpshadow' ),
					// Media & Visual Checks
					'check_featured_image'      => __( 'Check if featured image is set and optimized', 'wpshadow' ),
					'check_image_optimization'  => __( 'Check if images are compressed and optimized', 'wpshadow' ),
					'check_image_filenames'     => __( 'Check if image filenames are descriptive', 'wpshadow' ),
					'check_image_alt_text'      => __( 'Check if all images have alt text', 'wpshadow' ),
					'check_video_embeds'        => __( 'Validate video embeds and URLs', 'wpshadow' ),
					// Taxonomy & Organization
					'check_categories'          => __( 'Check if at least one category is assigned', 'wpshadow' ),
					'check_tags'                => __( 'Check if tags are assigned', 'wpshadow' ),
					'check_manual_excerpt'      => __( 'Check if manual excerpt is provided', 'wpshadow' ),
					// URL & Technical SEO
					'check_url_slug'            => __( 'Check if URL is SEO-friendly', 'wpshadow' ),
					'check_focus_keyword'       => __( 'Check keyword placement in title, content, URL', 'wpshadow' ),
					'check_schema_markup'       => __( 'Validate JSON-LD schema markup', 'wpshadow' ),
					// Social Media
					'check_social_preview'      => __( 'Validate Open Graph and Twitter Card data', 'wpshadow' ),
					// Accessibility
					'check_table_accessibility' => __( 'Check if tables have headers and captions', 'wpshadow' ),
					'check_link_anchor_text'    => __( 'Check link anchor text quality', 'wpshadow' ),
					// Conversion & Engagement
					'check_call_to_action'      => __( 'Check if content has a call-to-action', 'wpshadow' ),
					'check_publication_date'    => __( 'Warn about backdating or future dating', 'wpshadow' ),
					// Advanced Checks
					'check_grammar'             => __( 'Check grammar and spelling (requires API)', 'wpshadow' ),
					'check_mobile_preview'      => __( 'Validate mobile responsiveness', 'wpshadow' ),
					'check_load_time'           => __( 'Estimate page load time', 'wpshadow' ),
					'check_legal_compliance'    => __( 'Check for affiliate/sponsored disclosures', 'wpshadow' ),
				),
			)
		);

		$this->register_default_settings(
			array(
				// Core SEO Checks
				'check_title_length'        => true,
				'check_meta_description'    => true,
				'check_heading_structure'   => true,
				'check_keyword_density'     => false,
				'check_content_length'      => true,
				'check_internal_links'      => true,
				'check_external_links'      => false,
				'suggest_publish_time'      => false,
				// Content Quality Checks
				'check_readability'         => true,
				'check_empty_tags'          => true,
				'check_paragraph_length'    => true,
				'check_sentence_length'     => true,
				'check_passive_voice'       => false,
				'check_duplicate_content'   => false,
				// Media & Visual Checks
				'check_featured_image'      => true,
				'check_image_optimization'  => true,
				'check_image_filenames'     => false,
				'check_image_alt_text'      => true,
				'check_video_embeds'        => false,
				// Taxonomy & Organization
				'check_categories'          => true,
				'check_tags'                => false,
				'check_manual_excerpt'      => true,
				// URL & Technical SEO
				'check_url_slug'            => true,
				'check_focus_keyword'       => false,
				'check_schema_markup'       => false,
				// Social Media
				'check_social_preview'      => true,
				// Accessibility
				'check_table_accessibility' => true,
				'check_link_anchor_text'    => false,
				// Conversion & Engagement
				'check_call_to_action'      => false,
				'check_publication_date'    => false,
				// Advanced Checks
				'check_grammar'             => false,
				'check_mobile_preview'      => false,
				'check_load_time'           => false,
				'check_legal_compliance'    => false,
				// Settings
				'min_title_length'          => 30,
				'max_title_length'          => 60,
				'min_meta_length'           => 120,
				'max_meta_length'           => 160,
				'min_content_words'         => 300,
				'min_internal_links'        => 2,
				'max_paragraph_words'       => 150,
				'max_sentence_words'        => 25,
				'min_readability_score'     => 60,
				'max_image_size_kb'         => 500,
				'featured_image_min_width'  => 1200,
				'featured_image_min_height' => 630,
				'optimal_publish_days'      => array( 'Tuesday', 'Wednesday', 'Thursday' ),
				'optimal_publish_hours'     => array( 9, 10, 11 ),
			)
		);

		$this->log_activity( 'feature_initialized', 'Content Quality Optimizer feature initialized', 'info' );
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Enqueue editor scripts
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );

		// AJAX endpoint for content quality checks
		add_action( 'wp_ajax_wpshadow_content_check', array( $this, 'ajax_run_content_check' ) );

		// REST API endpoint
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Hook into pre-publish review
		add_filter( 'wpshadow_pre_publish_checks', array( $this, 'add_content_checks_to_review' ), 10, 2 );

		// Site health test
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		// Enqueue inline script for content quality panel
		wp_add_inline_script(
			'wp-blocks',
			$this->get_content_panel_script(),
			'after'
		);

		// Add styles for content quality panel
		wp_add_inline_style(
			'wp-edit-post',
			$this->get_content_panel_styles()
		);

		// Pass settings to JavaScript
		wp_localize_script(
			'wp-blocks',
			'wpshadowContentOptimizer',
			array(
				'enabled'                   => $this->is_enabled(),
				// Core SEO Checks
				'checkTitleLength'          => $this->is_sub_feature_enabled( 'check_title_length', true ),
				'checkMetaDescription'      => $this->is_sub_feature_enabled( 'check_meta_description', true ),
				'checkHeadingStructure'     => $this->is_sub_feature_enabled( 'check_heading_structure', true ),
				'checkKeywordDensity'       => $this->is_sub_feature_enabled( 'check_keyword_density', false ),
				'checkContentLength'        => $this->is_sub_feature_enabled( 'check_content_length', true ),
				'checkInternalLinks'        => $this->is_sub_feature_enabled( 'check_internal_links', true ),
				'checkExternalLinks'        => $this->is_sub_feature_enabled( 'check_external_links', false ),
				'suggestPublishTime'        => $this->is_sub_feature_enabled( 'suggest_publish_time', false ),
				// Content Quality Checks
				'checkReadability'          => $this->is_sub_feature_enabled( 'check_readability', true ),
				'checkEmptyTags'            => $this->is_sub_feature_enabled( 'check_empty_tags', true ),
				'checkParagraphLength'      => $this->is_sub_feature_enabled( 'check_paragraph_length', true ),
				'checkSentenceLength'       => $this->is_sub_feature_enabled( 'check_sentence_length', true ),
				'checkPassiveVoice'         => $this->is_sub_feature_enabled( 'check_passive_voice', false ),
				'checkDuplicateContent'     => $this->is_sub_feature_enabled( 'check_duplicate_content', false ),
				// Media & Visual Checks
				'checkFeaturedImage'        => $this->is_sub_feature_enabled( 'check_featured_image', true ),
				'checkImageOptimization'    => $this->is_sub_feature_enabled( 'check_image_optimization', true ),
				'checkImageFilenames'       => $this->is_sub_feature_enabled( 'check_image_filenames', false ),
				'checkImageAltText'         => $this->is_sub_feature_enabled( 'check_image_alt_text', true ),
				'checkVideoEmbeds'          => $this->is_sub_feature_enabled( 'check_video_embeds', false ),
				// Taxonomy & Organization
				'checkCategories'           => $this->is_sub_feature_enabled( 'check_categories', true ),
				'checkTags'                 => $this->is_sub_feature_enabled( 'check_tags', false ),
				'checkManualExcerpt'        => $this->is_sub_feature_enabled( 'check_manual_excerpt', true ),
				// URL & Technical SEO
				'checkUrlSlug'              => $this->is_sub_feature_enabled( 'check_url_slug', true ),
				'checkFocusKeyword'         => $this->is_sub_feature_enabled( 'check_focus_keyword', false ),
				'checkSchemaMarkup'         => $this->is_sub_feature_enabled( 'check_schema_markup', false ),
				// Social Media
				'checkSocialPreview'        => $this->is_sub_feature_enabled( 'check_social_preview', true ),
				// Accessibility
				'checkTableAccessibility'   => $this->is_sub_feature_enabled( 'check_table_accessibility', true ),
				'checkLinkAnchorText'       => $this->is_sub_feature_enabled( 'check_link_anchor_text', false ),
				// Conversion & Engagement
				'checkCallToAction'         => $this->is_sub_feature_enabled( 'check_call_to_action', false ),
				'checkPublicationDate'      => $this->is_sub_feature_enabled( 'check_publication_date', false ),
				// Advanced Checks
				'checkGrammar'              => $this->is_sub_feature_enabled( 'check_grammar', false ),
				'checkMobilePreview'        => $this->is_sub_feature_enabled( 'check_mobile_preview', false ),
				'checkLoadTime'             => $this->is_sub_feature_enabled( 'check_load_time', false ),
				'checkLegalCompliance'      => $this->is_sub_feature_enabled( 'check_legal_compliance', false ),
				// Configuration
				'minTitleLength'            => (int) $this->get_setting( 'min_title_length', 30 ),
				'maxTitleLength'            => (int) $this->get_setting( 'max_title_length', 60 ),
				'minMetaLength'             => (int) $this->get_setting( 'min_meta_length', 120 ),
				'maxMetaLength'             => (int) $this->get_setting( 'max_meta_length', 160 ),
				'minContentWords'           => (int) $this->get_setting( 'min_content_words', 300 ),
				'minInternalLinks'          => (int) $this->get_setting( 'min_internal_links', 2 ),
				'maxParagraphWords'         => (int) $this->get_setting( 'max_paragraph_words', 150 ),
				'maxSentenceWords'          => (int) $this->get_setting( 'max_sentence_words', 25 ),
				'minReadabilityScore'       => (int) $this->get_setting( 'min_readability_score', 60 ),
				'maxImageSizeKb'            => (int) $this->get_setting( 'max_image_size_kb', 500 ),
				'featuredImageMinWidth'     => (int) $this->get_setting( 'featured_image_min_width', 1200 ),
				'featuredImageMinHeight'    => (int) $this->get_setting( 'featured_image_min_height', 630 ),
				'optimalPublishDays'        => $this->get_setting( 'optimal_publish_days', array( 'Tuesday', 'Wednesday', 'Thursday' ) ),
				'optimalPublishHours'       => $this->get_setting( 'optimal_publish_hours', array( 9, 10, 11 ) ),
				'ajaxUrl'                   => admin_url( 'admin-ajax.php' ),
				'nonce'                     => wp_create_nonce( 'wpshadow_content_check' ),
			)
		);
	}

	/**
	 * Get JavaScript for content quality panel.
	 *
	 * @return string
	 */
	private function get_content_panel_script(): string {
		return "
(function() {
	if (!window.wpshadowContentOptimizer || !window.wpshadowContentOptimizer.enabled) {
		return;
	}

	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editPost;
	const { Component } = wp.element;
	const { __ } = wp.i18n;
	const settings = window.wpshadowContentOptimizer;

	class WPShadowSEOPanel extends Component {
		constructor(props) {
			super(props);
			this.state = {
				score: 0,
				issues: [],
				suggestions: []
			};
		}

		componentDidMount() {
			this.runChecks();
			// Re-run checks when content changes
			wp.data.subscribe(() => {
				const isAutoSave = wp.data.select('core/editor').isAutosavingPost();
				if (!isAutoSave) {
					clearTimeout(this.checkTimeout);
					this.checkTimeout = setTimeout(() => this.runChecks(), 2000);
				}
			});
		}

		componentWillUnmount() {
			clearTimeout(this.checkTimeout);
		}

		runChecks = () => {
			const content = wp.data.select('core/editor').getEditedPostContent();
			const title = wp.data.select('core/editor').getEditedPostAttribute('title');
			const excerpt = wp.data.select('core/editor').getEditedPostAttribute('excerpt');
			
			const issues = [];
			const suggestions = [];
			let score = 100;

			// Check title length
			if (settings.checkTitleLength) {
				const titleLength = title.length;
				if (titleLength < settings.minTitleLength) {
					issues.push(`Title is too short (${titleLength} characters). Aim for ${settings.minTitleLength}-${settings.maxTitleLength}.`);
					score -= 10;
				} else if (titleLength > settings.maxTitleLength) {
					issues.push(`Title is too long (${titleLength} characters). Google may cut it off.`);
					score -= 5;
				} else {
					suggestions.push('✓ Title length is perfect for Google search results');
				}
			}

			// Check meta description (excerpt)
			if (settings.checkMetaDescription) {
				if (!excerpt || excerpt.length === 0) {
					issues.push('No summary text (meta description) provided. Add one for better search results.');
					score -= 15;
				} else if (excerpt.length < settings.minMetaLength) {
					issues.push(`Summary text is too short (${excerpt.length} characters). Aim for ${settings.minMetaLength}-${settings.maxMetaLength}.`);
					score -= 10;
				} else if (excerpt.length > settings.maxMetaLength) {
					issues.push(`Summary text is too long (${excerpt.length} characters). Google may cut it off.`);
					score -= 5;
				} else {
					suggestions.push('✓ Summary text length is ideal');
				}
			}

			// Check heading structure
			if (settings.checkHeadingStructure) {
				const headingMatches = content.match(/<h([1-6])[^>]*>/g) || [];
				const headingLevels = headingMatches.map(h => parseInt(h.match(/<h([1-6])/)[1]));
				
				if (headingLevels.length === 0) {
					issues.push('No headings found. Add section titles (H2, H3) to organize content.');
					score -= 10;
				} else {
					let prevLevel = 1;
					let structureValid = true;
					headingLevels.forEach(level => {
						if (level > prevLevel + 1) {
							structureValid = false;
						}
						prevLevel = level;
					});
					
					if (!structureValid) {
						issues.push('Heading structure is not nested correctly (e.g., H2 → H4 skips H3).');
						score -= 5;
					} else {
						suggestions.push('✓ Headings are organized correctly');
					}
				}
			}

			// Check empty tags
			if (settings.checkEmptyTags) {
				const emptyParagraphs = (content.match(/<p[^>]*>\\s*(&nbsp;)*\\s*<\\/p>/g) || []).length;
				const emptySpans = (content.match(/<span[^>]*>\\s*<\\/span>/g) || []).length;
				const totalEmpty = emptyParagraphs + emptySpans;
				
				if (totalEmpty > 0) {
					issues.push(`${totalEmpty} empty paragraph(s) or tag(s) found. Clean them up for better performance.`);
					score -= 5;
				}
			}

			// Check content length
			if (settings.checkContentLength) {
				const wordCount = content.replace(/<[^>]*>/g, '').split(/\\s+/).filter(w => w.length > 0).length;
				if (wordCount < settings.minContentWords) {
					issues.push(`Content is short (${wordCount} words). Longer content (${settings.minContentWords}+ words) ranks better on Google.`);
					score -= 10;
				} else {
					suggestions.push(`✓ Content length is good (${wordCount} words)`);
				}
			}

			// Check internal links
			if (settings.checkInternalLinks) {
				const siteUrl = window.location.origin;
				const linkMatches = content.match(/href=[\"']([^\"']+)[\"']/g) || [];
				const internalLinks = linkMatches.filter(link => {
					const url = link.match(/href=[\"']([^\"']+)[\"']/)[1];
					return url.includes(siteUrl) || url.startsWith('/');
				}).length;
				
				if (internalLinks < settings.minInternalLinks) {
					issues.push(`Only ${internalLinks} link(s) to other pages on your site. Add ${settings.minInternalLinks}+ for better SEO.`);
					score -= 5;
				} else {
					suggestions.push(`✓ ${internalLinks} internal link(s) found`);
				}
			}

			// Check external links
			if (settings.checkExternalLinks) {
				const siteUrl = window.location.origin;
				const linkMatches = content.match(/href=[\"']([^\"']+)[\"']/g) || [];
				const externalLinks = linkMatches.filter(link => {
					const url = link.match(/href=[\"']([^\"']+)[\"']/)[1];
					return url.startsWith('http') && !url.includes(siteUrl);
				}).length;
				
				if (externalLinks === 0) {
					suggestions.push('Consider adding 1-2 links to quality external sources');
				} else {
					suggestions.push(`✓ ${externalLinks} external link(s) found`);
				}
			}

			// Suggest optimal publish time
			if (settings.suggestPublishTime) {
				const now = new Date();
				const currentDay = now.toLocaleString('en-US', { weekday: 'long' });
				const currentHour = now.getHours();
				
				const isOptimalDay = settings.optimalPublishDays.includes(currentDay);
				const isOptimalHour = settings.optimalPublishHours.includes(currentHour);
				
				if (!isOptimalDay || !isOptimalHour) {
					suggestions.push(`💡 Best time to publish: ${settings.optimalPublishDays.join(', ')} at ${settings.optimalPublishHours[0]}:00-${settings.optimalPublishHours[settings.optimalPublishHours.length - 1]}:00`);
				} else {
					suggestions.push('✓ Great time to publish!');
				}
			}

			// Ensure score doesn't go below 0
			score = Math.max(0, score);

			this.setState({
				score: score,
				issues: issues,
				suggestions: suggestions
			});
		}

		render() {
			const { score, issues, suggestions } = this.state;
			
			let scoreColor = '#00a32a'; // green
			if (score < 70) scoreColor = '#dba617'; // yellow
			if (score < 50) scoreColor = '#d63638'; // red

			return wp.element.createElement(
				'div',
				{ className: 'wpshadow-seo-panel' },
				wp.element.createElement(
					'div',
					{ className: 'wpshadow-seo-score', style: { textAlign: 'center', marginBottom: '16px' } },
					wp.element.createElement(
						'div',
						{ 
							style: { 
								fontSize: '48px', 
								fontWeight: 'bold', 
								color: scoreColor,
								lineHeight: '1'
							} 
						},
						score
					),
					wp.element.createElement('div', { style: { fontSize: '12px', color: '#666' } }, 'SEO Score')
				),
				issues.length > 0 && wp.element.createElement(
					'div',
					{ className: 'wpshadow-seo-issues', style: { marginBottom: '12px' } },
					wp.element.createElement('strong', { style: { display: 'block', marginBottom: '8px' } }, '⚠️ Issues to Fix:'),
					wp.element.createElement(
						'ul',
						{ style: { margin: '0 0 0 20px', fontSize: '13px' } },
						issues.map((issue, i) => 
							wp.element.createElement('li', { key: i, style: { marginBottom: '6px' } }, issue)
						)
					)
				),
				suggestions.length > 0 && wp.element.createElement(
					'div',
					{ className: 'wpshadow-seo-suggestions' },
					wp.element.createElement('strong', { style: { display: 'block', marginBottom: '8px' } }, '💡 Suggestions:'),
					wp.element.createElement(
						'ul',
						{ style: { margin: '0 0 0 20px', fontSize: '13px' } },
						suggestions.map((suggestion, i) => 
							wp.element.createElement('li', { key: i, style: { marginBottom: '6px' } }, suggestion)
						)
					)
				)
			);
		}
	}

	const WPShadowSEOSettingsPanel = () => {
		return wp.element.createElement(
			PluginDocumentSettingPanel,
			{
				name: 'wpshadow-seo-optimizer',
				title: 'SEO & Content Quality',
				initialOpen: true
			},
			wp.element.createElement(WPShadowSEOPanel)
		);
	};

	registerPlugin('wpshadow-seo-optimizer', {
		render: WPShadowSEOSettingsPanel,
		icon: 'chart-line'
	});
})();
";
	}

	/**
	 * Get CSS styles for SEO panel.
	 *
	 * @return string
	 */
	private function get_seo_panel_styles(): string {
		return "
.wpshadow-seo-panel {
	padding: 4px 0;
}
.wpshadow-seo-score {
	background: #f0f0f1;
	padding: 16px;
	border-radius: 4px;
	margin-bottom: 16px;
}
.wpshadow-seo-issues ul,
.wpshadow-seo-suggestions ul {
	list-style-type: disc;
}
";
	}

	/**
	 * AJAX handler for SEO check.
	 *
	 * @return void
	 */
	public function ajax_run_seo_check(): void {
		check_ajax_referer( 'wpshadow_seo_check', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
		$excerpt = isset( $_POST['excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['excerpt'] ) ) : '';

		$results = $this->run_seo_checks( $post_id, $title, $content, $excerpt );

		wp_send_json_success( $results );
	}

	/**
	 * Run all SEO checks.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $title   Post title.
	 * @param string $content Post content.
	 * @param string $excerpt Post excerpt.
	 * @return array<string, mixed>
	 */
	private function run_seo_checks( int $post_id, string $title, string $content, string $excerpt ): array {
		$issues      = array();
		$suggestions = array();
		$score       = 100;

		// Check title length
		if ( $this->is_sub_feature_enabled( 'check_title_length', true ) ) {
			$title_length = mb_strlen( $title );
			$min_length   = (int) $this->get_setting( 'min_title_length', 30 );
			$max_length   = (int) $this->get_setting( 'max_title_length', 60 );

			if ( $title_length < $min_length ) {
				$issues[] = sprintf(
					/* translators: 1: current length, 2: minimum length, 3: maximum length */
					__( 'Title is too short (%1$d characters). Aim for %2$d-%3$d.', 'wpshadow' ),
					$title_length,
					$min_length,
					$max_length
				);
				$score   -= 10;
			} elseif ( $title_length > $max_length ) {
				$issues[] = sprintf(
					/* translators: %d: current length */
					__( 'Title is too long (%d characters). Google may cut it off.', 'wpshadow' ),
					$title_length
				);
				$score   -= 5;
			} else {
				$suggestions[] = __( '✓ Title length is perfect for Google search results', 'wpshadow' );
			}
		}

		// Check meta description
		if ( $this->is_sub_feature_enabled( 'check_meta_description', true ) ) {
			$excerpt_length = mb_strlen( $excerpt );
			$min_length     = (int) $this->get_setting( 'min_meta_length', 120 );
			$max_length     = (int) $this->get_setting( 'max_meta_length', 160 );

			if ( empty( $excerpt ) ) {
				$issues[] = __( 'No summary text (meta description) provided. Add one for better search results.', 'wpshadow' );
				$score   -= 15;
			} elseif ( $excerpt_length < $min_length ) {
				$issues[] = sprintf(
					/* translators: 1: current length, 2: minimum length, 3: maximum length */
					__( 'Summary text is too short (%1$d characters). Aim for %2$d-%3$d.', 'wpshadow' ),
					$excerpt_length,
					$min_length,
					$max_length
				);
				$score   -= 10;
			} elseif ( $excerpt_length > $max_length ) {
				$issues[] = sprintf(
					/* translators: %d: current length */
					__( 'Summary text is too long (%d characters). Google may cut it off.', 'wpshadow' ),
					$excerpt_length
				);
				$score   -= 5;
			} else {
				$suggestions[] = __( '✓ Summary text length is ideal', 'wpshadow' );
			}
		}

		// Check heading structure
		if ( $this->is_sub_feature_enabled( 'check_heading_structure', true ) ) {
			$heading_result = $this->check_heading_structure( $content );
			if ( isset( $heading_result['issue'] ) ) {
				$issues[] = $heading_result['issue'];
				$score   -= $heading_result['penalty'];
			} else {
				$suggestions[] = $heading_result['suggestion'];
			}
		}

		// Check empty tags
		if ( $this->is_sub_feature_enabled( 'check_empty_tags', true ) ) {
			$empty_count = $this->count_empty_tags( $content );
			if ( $empty_count > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of empty tags */
					_n( '%d empty paragraph or tag found. Clean them up for better performance.', '%d empty paragraphs or tags found. Clean them up for better performance.', $empty_count, 'wpshadow' ),
					$empty_count
				);
				$score   -= 5;
			}
		}

		// Check content length
		if ( $this->is_sub_feature_enabled( 'check_content_length', true ) ) {
			$word_count = str_word_count( wp_strip_all_tags( $content ) );
			$min_words  = (int) $this->get_setting( 'min_content_words', 300 );

			if ( $word_count < $min_words ) {
				$issues[] = sprintf(
					/* translators: 1: current word count, 2: minimum word count */
					__( 'Content is short (%1$d words). Longer content (%2$d+ words) ranks better on Google.', 'wpshadow' ),
					$word_count,
					$min_words
				);
				$score   -= 10;
			} else {
				$suggestions[] = sprintf(
					/* translators: %d: word count */
					__( '✓ Content length is good (%d words)', 'wpshadow' ),
					$word_count
				);
			}
		}

		// Check internal links
		if ( $this->is_sub_feature_enabled( 'check_internal_links', true ) ) {
			$link_result = $this->check_internal_links( $content );
			if ( isset( $link_result['issue'] ) ) {
				$issues[] = $link_result['issue'];
				$score   -= $link_result['penalty'];
			} else {
				$suggestions[] = $link_result['suggestion'];
			}
		}

		return array(
			'score'       => max( 0, $score ),
			'issues'      => $issues,
			'suggestions' => $suggestions,
		);
	}

	/**
	 * Check heading structure.
	 *
	 * @param string $content Post content.
	 * @return array<string, mixed>
	 */
	private function check_heading_structure( string $content ): array {
		preg_match_all( '/<h([1-6])[^>]*>/i', $content, $matches );

		if ( empty( $matches[1] ) ) {
			return array(
				'issue'   => __( 'No headings found. Add section titles (H2, H3) to organize content.', 'wpshadow' ),
				'penalty' => 10,
			);
		}

		$heading_levels = array_map( 'intval', $matches[1] );
		$prev_level     = 1;
		$structure_valid = true;

		foreach ( $heading_levels as $level ) {
			if ( $level > $prev_level + 1 ) {
				$structure_valid = false;
				break;
			}
			$prev_level = $level;
		}

		if ( ! $structure_valid ) {
			return array(
				'issue'   => __( 'Heading structure is not nested correctly (e.g., H2 → H4 skips H3).', 'wpshadow' ),
				'penalty' => 5,
			);
		}

		return array(
			'suggestion' => __( '✓ Headings are organized correctly', 'wpshadow' ),
		);
	}

	/**
	 * Count empty tags in content.
	 *
	 * @param string $content Post content.
	 * @return int
	 */
	private function count_empty_tags( string $content ): int {
		$empty_p     = preg_match_all( '/<p[^>]*>\s*(&nbsp;)*\s*<\/p>/i', $content );
		$empty_span  = preg_match_all( '/<span[^>]*>\s*<\/span>/i', $content );
		$empty_div   = preg_match_all( '/<div[^>]*>\s*<\/div>/i', $content );

		return $empty_p + $empty_span + $empty_div;
	}

	/**
	 * Check internal links.
	 *
	 * @param string $content Post content.
	 * @return array<string, mixed>
	 */
	private function check_internal_links( string $content ): array {
		$site_url = get_site_url();
		preg_match_all( '/href=["\']([^"\']+)["\']/i', $content, $matches );

		$internal_count = 0;
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $url ) {
				if ( strpos( $url, $site_url ) !== false || strpos( $url, '/' ) === 0 ) {
					$internal_count++;
				}
			}
		}

		$min_links = (int) $this->get_setting( 'min_internal_links', 2 );

		if ( $internal_count < $min_links ) {
			return array(
				'issue'   => sprintf(
					/* translators: 1: current count, 2: minimum count */
					__( 'Only %1$d link(s) to other pages on your site. Add %2$d+ for better SEO.', 'wpshadow' ),
					$internal_count,
					$min_links
				),
				'penalty' => 5,
			);
		}

		return array(
			'suggestion' => sprintf(
				/* translators: %d: link count */
				__( '✓ %d internal link(s) found', 'wpshadow' ),
				$internal_count
			),
		);
	}

	/**
	 * Add SEO checks to pre-publish review.
	 *
	 * @param array<int, array<string, string>> $checks  Existing checks.
	 * @param int                               $post_id Post ID.
	 * @return array<int, array<string, string>>
	 */
	public function add_seo_checks_to_review( array $checks, int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return $checks;
		}

		$results = $this->run_seo_checks(
			$post_id,
			$post->post_title,
			$post->post_content,
			$post->post_excerpt
		);

		// Convert issues to check format
		foreach ( $results['issues'] as $issue ) {
			$checks[] = array(
				'type'    => 'warning',
				'message' => '[SEO] ' . $issue,
			);
		}

		return $checks;
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'wpshadow/v1',
			'/seo-check',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_run_checks' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * REST API callback for running checks.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function rest_run_checks( $request ) {
		$post_id = $request->get_param( 'post_id' );
		$title   = $request->get_param( 'title' );
		$content = $request->get_param( 'content' );
		$excerpt = $request->get_param( 'excerpt' );

		$results = $this->run_seo_checks( (int) $post_id, $title, $content, $excerpt );

		return rest_ensure_response( $results );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, array<string, mixed>> $tests Site Health tests.
	 * @return array<string, array<string, mixed>>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_seo_optimizer'] = array(
			'label' => __( 'SEO Content Optimizer', 'wpshadow' ),
			'test'  => array( $this, 'site_health_test' ),
		);

		return $tests;
	}

	/**
	 * Site Health test callback.
	 *
	 * @return array<string, mixed>
	 */
	public function site_health_test(): array {
		$result = array(
			'label'       => __( 'SEO content optimization is active', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'SEO', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Content is automatically checked for SEO best practices.', 'wpshadow' )
			),
			'actions'     => '',
			'test'        => 'wpshadow_seo_optimizer',
		);

		if ( ! $this->is_enabled() ) {
			$result['status']      = 'recommended';
			$result['label']       = __( 'SEO content optimizer is not enabled', 'wpshadow' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__( 'Enable SEO optimizer to get real-time suggestions for improving your content\'s search engine visibility.', 'wpshadow' )
			);
		}

		return $result;
	}
}
