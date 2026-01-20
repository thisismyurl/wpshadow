<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Content_Optimizer extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'content-optimizer',
				'name'               => __( 'Complete Content Quality Optimizer', 'wpshadow' ),
				'description'        => __( 'Get 35+ real-time checks to create perfect content: SEO, readability, accessibility, images, social media, categories, and more. Your all-in-one content quality assistant.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '2.0.0',
				'widget_group'       => 'content',
				'minimum_capability' => 'edit_posts',
				'aliases'            => array(
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
					'check_title_length'        => array(
						'name'               => __( 'Title Length Check', 'wpshadow' ),
						'description_short'  => __( 'Verify title is right length for Google', 'wpshadow' ),
						'description_long'   => __( 'Google displays titles between 50-60 characters on desktop. This check ensures your post title is in the optimal range so it displays fully in search results without being cut off or too short to stand out.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure your post title shows fully in Google search results.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_meta_description'    => array(
						'name'               => __( 'Meta Description Check', 'wpshadow' ),
						'description_short'  => __( 'Verify summary text is provided', 'wpshadow' ),
						'description_long'   => __( 'Meta descriptions are the summary text that appears under your title in Google search results. This check ensures you\'ve written a description for your post to make people want to click on it.', 'wpshadow' ),
						'description_wizard' => __( 'Create a summary that appears in search results to encourage people to click on your post.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_heading_structure'   => array(
						'name'               => __( 'Heading Structure Check', 'wpshadow' ),
						'description_short'  => __( 'Verify section titles are organized correctly', 'wpshadow' ),
						'description_long'   => __( 'Headings should flow logically from H1 to H2 to H3, like an outline. This helps both Google and screen readers understand your content structure. Properly organized headings improve both search rankings and accessibility.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure your section titles are organized properly, like a good outline.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_keyword_density'     => array(
						'name'               => __( 'Keyword Density Check', 'wpshadow' ),
						'description_short'  => __( 'Verify main words appear enough times', 'wpshadow' ),
						'description_long'   => __( 'Uses your focus keyword to check if you mention your main topic enough times throughout the post. Too few mentions means Google might not understand what your post is about. Too many repetitions looks like spam.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure you mention your main topic enough times for Google to notice.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_content_length'      => array(
						'name'               => __( 'Content Length Check', 'wpshadow' ),
						'description_short'  => __( 'Verify content is long enough', 'wpshadow' ),
						'description_long'   => __( 'Longer posts (800+ words) typically rank better in Google. This check ensures your post is comprehensive enough to be considered an authoritative source on the topic and valuable to readers.', 'wpshadow' ),
						'description_wizard' => __( 'Write enough content (800+ words) to rank well in Google search results.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_internal_links'      => array(
						'name'               => __( 'Internal Links Check', 'wpshadow' ),
						'description_short'  => __( 'Verify links to other pages exist', 'wpshadow' ),
						'description_long'   => __( 'Internal links help Google find your other pages and understand your site structure. Links also keep visitors on your site longer by offering related content. This check ensures you\'re linking to relevant pages on your own site.', 'wpshadow' ),
						'description_wizard' => __( 'Link to other pages on your site to help Google and readers find more content.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_external_links'      => array(
						'name'               => __( 'External Links Check', 'wpshadow' ),
						'description_short'  => __( 'Verify links to other websites exist', 'wpshadow' ),
						'description_long'   => __( 'Linking to reputable external sources shows your content is well-researched and adds credibility. Google looks for outbound links as a signal of quality content. This check ensures you\'re citing sources appropriately.', 'wpshadow' ),
						'description_wizard' => __( 'Link to trusted external sources to back up your content and build credibility.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'suggest_publish_time'      => array(
						'name'               => __( 'Publish Time Suggestion', 'wpshadow' ),
						'description_short'  => __( 'Suggest best day and time to publish', 'wpshadow' ),
						'description_long'   => __( 'Analyzes when your audience is most active and suggests optimal publishing times. Publishing when more people are online increases views and engagement. Takes into account your audience\'s timezone and browsing patterns.', 'wpshadow' ),
						'description_wizard' => __( 'Get suggestions for the best time to publish for maximum visibility.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_readability'         => array(
						'name'               => __( 'Readability Score', 'wpshadow' ),
						'description_short'  => __( 'Calculate readability using Flesch-Kincaid', 'wpshadow' ),
						'description_long'   => __( 'Analyzes your content using the Flesch-Kincaid readability formula to determine if it\'s easy to understand. Aims for a score that\'s readable by most adults (60+). Simpler language means more people can understand your content and higher engagement.', 'wpshadow' ),
						'description_wizard' => __( 'Measure how easy your content is to read and understand.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_empty_tags'          => array(
						'name'               => __( 'Empty Tags Check', 'wpshadow' ),
						'description_short'  => __( 'Check for empty paragraphs and breaks', 'wpshadow' ),
						'description_long'   => __( 'Detects empty or unnecessary HTML tags that clutter your code and waste space. Cleaning these up improves page load speed and makes the HTML cleaner and easier to maintain.', 'wpshadow' ),
						'description_wizard' => __( 'Remove empty paragraphs and unnecessary spacing that wastes space and slows page load.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_paragraph_length'    => array(
						'name'               => __( 'Paragraph Length Check', 'wpshadow' ),
						'description_short'  => __( 'Check if paragraphs are too long', 'wpshadow' ),
						'description_long'   => __( 'Long paragraphs are hard to read, especially on phones. This check flags paragraphs that are too long and suggests breaking them up. Shorter paragraphs improve readability and keep readers engaged.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure your paragraphs aren\'t too long and hard to read.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_sentence_length'     => array(
						'name'               => __( 'Sentence Length Check', 'wpshadow' ),
						'description_short'  => __( 'Check if sentences are too long', 'wpshadow' ),
						'description_long'   => __( 'Long, complex sentences are hard to understand. This check identifies sentences that are too long and suggests breaking them into shorter, punchier sentences. Shorter sentences make your writing clearer and more persuasive.', 'wpshadow' ),
						'description_wizard' => __( 'Keep your sentences short and easy to understand.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_passive_voice'       => array(
						'name'               => __( 'Passive Voice Check', 'wpshadow' ),
						'description_short'  => __( 'Detect excessive passive voice usage', 'wpshadow' ),
						'description_long'   => __( 'Passive voice (\"the article was written\") is weaker than active voice (\"I wrote the article\"). This check flags excessive passive voice and suggests rewriting in active voice. Active voice is more engaging, persuasive, and easier to understand.', 'wpshadow' ),
						'description_wizard' => __( 'Use active voice instead of passive voice to make your writing stronger and clearer.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_duplicate_content'   => array(
						'name'               => __( 'Duplicate Content Check', 'wpshadow' ),
						'description_short'  => __( 'Check for duplicate content on your site', 'wpshadow' ),
						'description_long'   => __( 'Scans your site to find posts or pages with very similar content. Duplicate content confuses Google about which version to rank. This check helps you find and consolidate similar content to improve rankings.', 'wpshadow' ),
						'description_wizard' => __( 'Find posts that are too similar to help Google rank your best content.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'check_featured_image'      => array(
						'name'               => __( 'Featured Image Check', 'wpshadow' ),
						'description_short'  => __( 'Verify featured image is set and optimized', 'wpshadow' ),
						'description_long'   => __( 'A good featured image makes your post stand out in search results and social media. This check ensures you have a featured image set and that it\'s the right size and format for best performance and appearance.', 'wpshadow' ),
						'description_wizard' => __( 'Add an eye-catching featured image to make your post stand out on social media.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_image_optimization'  => array(
						'name'               => __( 'Image Optimization Check', 'wpshadow' ),
						'description_short'  => __( 'Check if images are compressed and optimized', 'wpshadow' ),
						'description_long'   => __( 'Large, unoptimized images slow down your page. This check verifies images are compressed and in the right format (WEBP is better than JPEG). Optimized images load faster, improve user experience, and help your rankings.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure your images are compressed to load faster and improve site speed.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_image_filenames'     => array(
						'name'               => __( 'Image Filename Check', 'wpshadow' ),
						'description_short'  => __( 'Check if image filenames are descriptive', 'wpshadow' ),
						'description_long'   => __( 'Google can\'t see images, but it can read the filename. Using descriptive filenames (like \"apple-pie-recipe.jpg\" instead of \"IMG_1234.jpg\") helps Google understand what the image shows and can improve rankings in image search.', 'wpshadow' ),
						'description_wizard' => __( 'Name your image files descriptively so Google understands what they show.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_image_alt_text'      => array(
						'name'               => __( 'Image Alt Text Check', 'wpshadow' ),
						'description_short'  => __( 'Check if all images have alt text', 'wpshadow' ),
						'description_long'   => __( 'Alt text describes images for people using screen readers and helps Google understand images. Every image should have descriptive alt text. It\'s both good for accessibility and SEO - a win-win for your site.', 'wpshadow' ),
						'description_wizard' => __( 'Add descriptions to all images so everyone can understand what they show.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_video_embeds'        => array(
						'name'               => __( 'Video Embeds Check', 'wpshadow' ),
						'description_short'  => __( 'Validate video embeds and URLs', 'wpshadow' ),
						'description_long'   => __( 'Videos increase engagement and time on page, which improves rankings. This check validates that embedded videos are working properly and that video URLs are valid. Broken videos hurt user experience and rankings.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure embedded videos are working properly for better engagement.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_categories'          => array(
						'name'               => __( 'Categories Check', 'wpshadow' ),
						'description_short'  => __( 'Check if at least one category is assigned', 'wpshadow' ),
						'description_long'   => __( 'Categories organize your content and help Google understand your site structure. Every post should belong to at least one category. This helps both visitors and search engines navigate your site.', 'wpshadow' ),
						'description_wizard' => __( 'Organize your post into categories so visitors can find related content.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_tags'                => array(
						'name'               => __( 'Tags Check', 'wpshadow' ),
						'description_short'  => __( 'Check if tags are assigned', 'wpshadow' ),
						'description_long'   => __( 'Tags are like labels that describe your post topic. While optional, tags help organize content and create topic pages that improve navigation. Using 3-5 relevant tags per post is a good practice.', 'wpshadow' ),
						'description_wizard' => __( 'Add tags to help organize your content by topic.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'check_manual_excerpt'      => array(
						'name'               => __( 'Manual Excerpt Check', 'wpshadow' ),
						'description_short'  => __( 'Check if manual excerpt is provided', 'wpshadow' ),
						'description_long'   => __( 'Excerpts are summaries that appear in archives and on the homepage. Writing a custom excerpt gives you control over what preview text people see instead of WordPress automatically truncating your content.', 'wpshadow' ),
						'description_wizard' => __( 'Write a custom excerpt for better control over how your post appears on archive pages.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'check_url_slug'            => array(
						'name'               => __( 'URL Slug Check', 'wpshadow' ),
						'description_short'  => __( 'Check if URL is SEO-friendly', 'wpshadow' ),
						'description_long'   => __( 'Your post URL should be short, descriptive, and include your main keyword. Good URL: \"how-to-bake-cookies\" vs Bad URL: \"post123abc\". Descriptive URLs help Google and visitors understand what the page is about.', 'wpshadow' ),
						'description_wizard' => __( 'Use short, descriptive URLs that include your main topic.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_focus_keyword'       => array(
						'name'               => __( 'Focus Keyword Placement', 'wpshadow' ),
						'description_short'  => __( 'Check keyword placement in title, content, URL', 'wpshadow' ),
						'description_long'   => __( 'Includes your focus keyword in important places (title, first paragraph, headings, URL) tells Google what your post is about. This check ensures good keyword placement without overdoing it (which looks like spam).', 'wpshadow' ),
						'description_wizard' => __( 'Include your main keyword in important places like the title and first paragraph.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_schema_markup'       => array(
						'name'               => __( 'Schema Markup Check', 'wpshadow' ),
						'description_short'  => __( 'Validate JSON-LD schema markup', 'wpshadow' ),
						'description_long'   => __( 'Schema markup is code that helps Google understand your content better. It can enable rich results like star ratings, prices, and author info in search results. Valid schema markup improves CTR and SEO performance.', 'wpshadow' ),
						'description_wizard' => __( 'Add schema markup so Google can show your content better in search results.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_social_preview'      => array(
						'name'               => __( 'Social Preview Check', 'wpshadow' ),
						'description_short'  => __( 'Validate Open Graph and Twitter Card data', 'wpshadow' ),
						'description_long'   => __( 'When your post is shared on Facebook, Twitter, LinkedIn, etc., it shows a preview with your title, description, and image. This check ensures proper Open Graph and Twitter Card markup so your posts look professional when shared.', 'wpshadow' ),
						'description_wizard' => __( 'Make sure your post looks great when people share it on Facebook and Twitter.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_table_accessibility' => array(
						'name'               => __( 'Table Accessibility Check', 'wpshadow' ),
						'description_short'  => __( 'Check if tables have headers and captions', 'wpshadow' ),
						'description_long'   => __( 'Tables need proper headers and captions to be understandable by screen readers and to convey meaning to all users. This check ensures your data tables are accessible to everyone.', 'wpshadow' ),
						'description_wizard' => __( 'Add headers and captions to tables so everyone can understand the data.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_link_anchor_text'    => array(
						'name'               => __( 'Link Anchor Text Check', 'wpshadow' ),
						'description_short'  => __( 'Check link anchor text quality', 'wpshadow' ),
						'description_long'   => __( 'Link text tells Google and readers what the linked page is about. Generic text like \"click here\" doesn\'t help. Descriptive anchor text improves both SEO and user experience.', 'wpshadow' ),
						'description_wizard' => __( 'Use descriptive link text instead of generic \"click here\" phrases.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_call_to_action'      => array(
						'name'               => __( 'Call-to-Action Check', 'wpshadow' ),
						'description_short'  => __( 'Check if content has a call-to-action', 'wpshadow' ),
						'description_long'   => __( 'A clear call-to-action (CTA) tells readers what to do next: subscribe, buy, contact, etc. Posts without CTAs miss conversion opportunities. This check ensures your content guides readers toward desired actions.', 'wpshadow' ),
						'description_wizard' => __( 'Add a clear instruction telling readers what to do next (subscribe, buy, contact, etc.).', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_publication_date'    => array(
						'name'               => __( 'Publication Date Check', 'wpshadow' ),
						'description_short'  => __( 'Warn about backdating or future dating', 'wpshadow' ),
						'description_long'   => __( 'Manipulating publication dates can confuse Google and analytics. This check warns if you\'re publishing old content as new or setting dates far in the future. Always use honest publication dates.', 'wpshadow' ),
						'description_wizard' => __( 'Use real publication dates to avoid confusing search engines and analytics.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'check_grammar'             => array(
						'name'               => __( 'Grammar Check', 'wpshadow' ),
						'description_short'  => __( 'Check grammar and spelling', 'wpshadow' ),
						'description_long'   => __( 'Errors in grammar and spelling hurt credibility and user experience. This advanced check looks for common mistakes and suggests corrections to make your content professional and polished.', 'wpshadow' ),
						'description_wizard' => __( 'Catch grammar and spelling mistakes to keep your content professional.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'check_mobile_preview'      => __( 'Validate mobile responsiveness', 'wpshadow' ),
					'check_load_time'           => __( 'Estimate page load time', 'wpshadow' ),
					'check_legal_compliance'    => __( 'Check for affiliate/sponsored disclosures', 'wpshadow' ),
				),
			)
		);

		$this->register_default_settings(
			array(

				'check_title_length'        => true,
				'check_meta_description'    => true,
				'check_heading_structure'   => true,
				'check_keyword_density'     => false,
				'check_content_length'      => true,
				'check_internal_links'      => true,
				'check_external_links'      => false,
				'suggest_publish_time'      => false,

				'check_readability'         => true,
				'check_empty_tags'          => true,
				'check_paragraph_length'    => true,
				'check_sentence_length'     => true,
				'check_passive_voice'       => false,
				'check_duplicate_content'   => false,

				'check_featured_image'      => true,
				'check_image_optimization'  => true,
				'check_image_filenames'     => false,
				'check_image_alt_text'      => true,
				'check_video_embeds'        => false,

				'check_categories'          => true,
				'check_tags'                => false,
				'check_manual_excerpt'      => true,

				'check_url_slug'            => true,
				'check_focus_keyword'       => false,
				'check_schema_markup'       => false,

				'check_social_preview'      => true,

				'check_table_accessibility' => true,
				'check_link_anchor_text'    => false,

				'check_call_to_action'      => false,
				'check_publication_date'    => false,

				'check_grammar'             => false,
				'check_mobile_preview'      => false,
				'check_load_time'           => false,
				'check_legal_compliance'    => false,

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

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );

		add_action( 'wp_ajax_wpshadow_content_check', array( $this, 'ajax_run_content_check' ) );

		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		add_filter( 'wpshadow_pre_publish_checks', array( $this, 'add_content_checks_to_review' ), 10, 2 );

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	public function enqueue_editor_assets(): void {

		wp_add_inline_script(
			'wp-blocks',
			$this->get_content_panel_script(),
			'after'
		);

		wp_add_inline_style(
			'wp-edit-post',
			$this->get_content_panel_styles()
		);

		wp_localize_script(
			'wp-blocks',
			'wpshadowContentOptimizer',
			array(
				'enabled'                   => $this->is_enabled(),

				'checkTitleLength'          => $this->is_sub_feature_enabled( 'check_title_length', true ),
				'checkMetaDescription'      => $this->is_sub_feature_enabled( 'check_meta_description', true ),
				'checkHeadingStructure'     => $this->is_sub_feature_enabled( 'check_heading_structure', true ),
				'checkKeywordDensity'       => $this->is_sub_feature_enabled( 'check_keyword_density', false ),
				'checkContentLength'        => $this->is_sub_feature_enabled( 'check_content_length', true ),
				'checkInternalLinks'        => $this->is_sub_feature_enabled( 'check_internal_links', true ),
				'checkExternalLinks'        => $this->is_sub_feature_enabled( 'check_external_links', false ),
				'suggestPublishTime'        => $this->is_sub_feature_enabled( 'suggest_publish_time', false ),

				'checkReadability'          => $this->is_sub_feature_enabled( 'check_readability', true ),
				'checkEmptyTags'            => $this->is_sub_feature_enabled( 'check_empty_tags', true ),
				'checkParagraphLength'      => $this->is_sub_feature_enabled( 'check_paragraph_length', true ),
				'checkSentenceLength'       => $this->is_sub_feature_enabled( 'check_sentence_length', true ),
				'checkPassiveVoice'         => $this->is_sub_feature_enabled( 'check_passive_voice', false ),
				'checkDuplicateContent'     => $this->is_sub_feature_enabled( 'check_duplicate_content', false ),

				'checkFeaturedImage'        => $this->is_sub_feature_enabled( 'check_featured_image', true ),
				'checkImageOptimization'    => $this->is_sub_feature_enabled( 'check_image_optimization', true ),
				'checkImageFilenames'       => $this->is_sub_feature_enabled( 'check_image_filenames', false ),
				'checkImageAltText'         => $this->is_sub_feature_enabled( 'check_image_alt_text', true ),
				'checkVideoEmbeds'          => $this->is_sub_feature_enabled( 'check_video_embeds', false ),

				'checkCategories'           => $this->is_sub_feature_enabled( 'check_categories', true ),
				'checkTags'                 => $this->is_sub_feature_enabled( 'check_tags', false ),
				'checkManualExcerpt'        => $this->is_sub_feature_enabled( 'check_manual_excerpt', true ),

				'checkUrlSlug'              => $this->is_sub_feature_enabled( 'check_url_slug', true ),
				'checkFocusKeyword'         => $this->is_sub_feature_enabled( 'check_focus_keyword', false ),
				'checkSchemaMarkup'         => $this->is_sub_feature_enabled( 'check_schema_markup', false ),

				'checkSocialPreview'        => $this->is_sub_feature_enabled( 'check_social_preview', true ),

				'checkTableAccessibility'   => $this->is_sub_feature_enabled( 'check_table_accessibility', true ),
				'checkLinkAnchorText'       => $this->is_sub_feature_enabled( 'check_link_anchor_text', false ),

				'checkCallToAction'         => $this->is_sub_feature_enabled( 'check_call_to_action', false ),
				'checkPublicationDate'      => $this->is_sub_feature_enabled( 'check_publication_date', false ),

				'checkGrammar'              => $this->is_sub_feature_enabled( 'check_grammar', false ),
				'checkMobilePreview'        => $this->is_sub_feature_enabled( 'check_mobile_preview', false ),
				'checkLoadTime'             => $this->is_sub_feature_enabled( 'check_load_time', false ),
				'checkLegalCompliance'      => $this->is_sub_feature_enabled( 'check_legal_compliance', false ),

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

	private function get_content_panel_script(): string {
		return <<<'JAVASCRIPT'
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

			if (settings.checkEmptyTags) {
				const emptyParagraphs = (content.match(/<p[^>]*>\\s*(&nbsp;)*\\s*<\\/p>/g) || []).length;
				const emptySpans = (content.match(/<span[^>]*>\\s*<\\/span>/g) || []).length;
				const totalEmpty = emptyParagraphs + emptySpans;

				if (totalEmpty > 0) {
					issues.push(`${totalEmpty} empty paragraph(s) or tag(s) found. Clean them up for better performance.`);
					score -= 5;
				}
			}

			if (settings.checkContentLength) {
				const wordCount = content.replace(/<[^>]*>/g, '').split(/\\s+/).filter(w => w.length > 0).length;
				if (wordCount < settings.minContentWords) {
					issues.push(`Content is short (${wordCount} words). Longer content (${settings.minContentWords}+ words) ranks better on Google.`);
					score -= 10;
				} else {
					suggestions.push(`✓ Content length is good (${wordCount} words)`);
				}
			}

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

			score = Math.max(0, score);

			this.setState({
				score: score,
				issues: issues,
				suggestions: suggestions
			});
		}

		render() {
			const { score, issues, suggestions } = this.state;

			let scoreColor = '#00a32a'; 
			if (score < 70) scoreColor = '#dba617'; 
			if (score < 50) scoreColor = '#d63638'; 

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
JAVASCRIPT;
	}

	private function get_seo_panel_styles(): string {
		return <<<'CSS'
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
CSS;
	}

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

	private function run_seo_checks( int $post_id, string $title, string $content, string $excerpt ): array {
		$issues      = array();
		$suggestions = array();
		$score       = 100;

		if ( $this->is_sub_feature_enabled( 'check_title_length', true ) ) {
			$title_length = mb_strlen( $title );
			$min_length   = (int) $this->get_setting( 'min_title_length', 30 );
			$max_length   = (int) $this->get_setting( 'max_title_length', 60 );

			if ( $title_length < $min_length ) {
				$issues[] = sprintf(

					__( 'Title is too short (%1$d characters). Aim for %2$d-%3$d.', 'wpshadow' ),
					$title_length,
					$min_length,
					$max_length
				);
				$score   -= 10;
			} elseif ( $title_length > $max_length ) {
				$issues[] = sprintf(

					__( 'Title is too long (%d characters). Google may cut it off.', 'wpshadow' ),
					$title_length
				);
				$score   -= 5;
			} else {
				$suggestions[] = __( '✓ Title length is perfect for Google search results', 'wpshadow' );
			}
		}

		if ( $this->is_sub_feature_enabled( 'check_meta_description', true ) ) {
			$excerpt_length = mb_strlen( $excerpt );
			$min_length     = (int) $this->get_setting( 'min_meta_length', 120 );
			$max_length     = (int) $this->get_setting( 'max_meta_length', 160 );

			if ( empty( $excerpt ) ) {
				$issues[] = __( 'No summary text (meta description) provided. Add one for better search results.', 'wpshadow' );
				$score   -= 15;
			} elseif ( $excerpt_length < $min_length ) {
				$issues[] = sprintf(

					__( 'Summary text is too short (%1$d characters). Aim for %2$d-%3$d.', 'wpshadow' ),
					$excerpt_length,
					$min_length,
					$max_length
				);
				$score   -= 10;
			} elseif ( $excerpt_length > $max_length ) {
				$issues[] = sprintf(

					__( 'Summary text is too long (%d characters). Google may cut it off.', 'wpshadow' ),
					$excerpt_length
				);
				$score   -= 5;
			} else {
				$suggestions[] = __( '✓ Summary text length is ideal', 'wpshadow' );
			}
		}

		if ( $this->is_sub_feature_enabled( 'check_heading_structure', true ) ) {
			$heading_result = $this->check_heading_structure( $content );
			if ( isset( $heading_result['issue'] ) ) {
				$issues[] = $heading_result['issue'];
				$score   -= $heading_result['penalty'];
			} else {
				$suggestions[] = $heading_result['suggestion'];
			}
		}

		if ( $this->is_sub_feature_enabled( 'check_empty_tags', true ) ) {
			$empty_count = $this->count_empty_tags( $content );
			if ( $empty_count > 0 ) {
				$issues[] = sprintf(

					_n( '%d empty paragraph or tag found. Clean them up for better performance.', '%d empty paragraphs or tags found. Clean them up for better performance.', $empty_count, 'wpshadow' ),
					$empty_count
				);
				$score   -= 5;
			}
		}

		if ( $this->is_sub_feature_enabled( 'check_content_length', true ) ) {
			$word_count = str_word_count( wp_strip_all_tags( $content ) );
			$min_words  = (int) $this->get_setting( 'min_content_words', 300 );

			if ( $word_count < $min_words ) {
				$issues[] = sprintf(

					__( 'Content is short (%1$d words). Longer content (%2$d+ words) ranks better on Google.', 'wpshadow' ),
					$word_count,
					$min_words
				);
				$score   -= 10;
			} else {
				$suggestions[] = sprintf(

					__( '✓ Content length is good (%d words)', 'wpshadow' ),
					$word_count
				);
			}
		}

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

	private function count_empty_tags( string $content ): int {
		$empty_p     = preg_match_all( '/<p[^>]*>\s*(&nbsp;)*\s*<\/p>/i', $content );
		$empty_span  = preg_match_all( '/<span[^>]*>\s*<\/span>/i', $content );
		$empty_div   = preg_match_all( '/<div[^>]*>\s*<\/div>/i', $content );

		return $empty_p + $empty_span + $empty_div;
	}

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

					__( 'Only %1$d link(s) to other pages on your site. Add %2$d+ for better SEO.', 'wpshadow' ),
					$internal_count,
					$min_links
				),
				'penalty' => 5,
			);
		}

		return array(
			'suggestion' => sprintf(

				__( '✓ %d internal link(s) found', 'wpshadow' ),
				$internal_count
			),
		);
	}

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

		foreach ( $results['issues'] as $issue ) {
			$checks[] = array(
				'type'    => 'warning',
				'message' => '[SEO] ' . $issue,
			);
		}

		return $checks;
	}

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

	public function rest_run_checks( $request ) {
		$post_id = $request->get_param( 'post_id' );
		$title   = $request->get_param( 'title' );
		$content = $request->get_param( 'content' );
		$excerpt = $request->get_param( 'excerpt' );

		$results = $this->run_seo_checks( (int) $post_id, $title, $content, $excerpt );

		return rest_ensure_response( $results );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_seo_optimizer'] = array(
			'label' => __( 'SEO Content Optimizer', 'wpshadow' ),
			'test'  => array( $this, 'site_health_test' ),
		);

		return $tests;
	}

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
