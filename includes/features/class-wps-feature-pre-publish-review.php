<?php
/**
 * Feature: Pre-Publish Content Review
 *
 * Runs automatic content quality checks before publishing posts, including
 * paste cleanup detection, broken link checking, and content validation.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75004
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * WPSHADOW_Feature_Pre_Publish_Review
 *
 * Pre-publish content review and validation system.
 */
final class WPSHADOW_Feature_Pre_Publish_Review extends WPSHADOW_Abstract_Feature {

	/**
	 * Registered external reviewers from other features.
	 *
	 * @var array
	 */
	private static array $external_reviewers = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'pre-publish-review',
				'name'               => __( 'Check Content Before Publishing', 'wpshadow' ),
				'description'        => __( 'Automatically check your posts for problems before they go live. Finds broken links, messy formatting, and missing information.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'content',
				'minimum_capability' => 'edit_posts',
				'aliases'            => array(
					'content review',
					'quality check',
					'broken link checker',
					'404 links',
					'dead links',
					'paste cleanup',
					'word formatting',
					'alt text',
					'accessibility check',
					'image descriptions',
					'validation',
					'publish wizard',
				),
				'sub_features'    => array(
					'check_broken_links'     => array(
						'name'               => __( 'Check for Broken Links', 'wpshadow' ),
						'description_short'  => __( 'Verify all links in content are working', 'wpshadow' ),
						'description_long'   => __( 'Scans your post for links and verifies they all work and don\'t return 404 errors. Broken links hurt SEO and provide a poor user experience. Finding and fixing them before publishing ensures visitors won\'t hit dead links.', 'wpshadow' ),
						'description_wizard' => __( 'Check for broken links before publishing so readers don\'t hit dead links.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_paste_cleanup'    => array(
						'name'               => __( 'Check for Messy Paste Content', 'wpshadow' ),
						'description_short'  => __( 'Detect poorly-formatted pasted content', 'wpshadow' ),
						'description_long'   => __( 'Detects if your post contains content that appears to have been pasted from Word or other documents without cleanup. Looks for signatures of poor paste like excessive inline styles, Word classes, or unusual HTML structure. Warns you to review or clean up the content for consistency.', 'wpshadow' ),
						'description_wizard' => __( 'Warn if you have messy pasted content that should be cleaned up before publishing.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_missing_alt_text' => array(
						'name'               => __( 'Check for Missing Alt Text', 'wpshadow' ),
						'description_short'  => __( 'Verify all images have descriptions', 'wpshadow' ),
						'description_long'   => __( 'Verifies all images in your post have alt text for accessibility. Screen reader users rely on alt text to understand images. Google also uses alt text for image search rankings. Missing alt text is both an accessibility violation and SEO miss.', 'wpshadow' ),
						'description_wizard' => __( 'Missing alt text hurts both accessibility and SEO. Check before publishing so all images are properly described.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_empty_headings'   => array(
						'name'               => __( 'Check for Empty Headings', 'wpshadow' ),
						'description_short'  => __( 'Find headings with no text', 'wpshadow' ),
						'description_long'   => __( 'Detects heading tags that have no text content. Empty headings create confusion for screen reader users and hurt SEO. They often indicate formatting mistakes or incomplete content that should be fixed before publishing.', 'wpshadow' ),
						'description_wizard' => __( 'Empty headings break accessibility and look like mistakes. Find and fix them before publishing.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'check_word_count'       => array(
						'name'               => __( 'Check Content Length', 'wpshadow' ),
						'description_short'  => __( 'Warn if content is too short', 'wpshadow' ),
						'description_long'   => __( 'Checks that your post meets a minimum word count (default 300 words). Thin content performs poorly in search results. This warning helps you remember to write substantial posts with enough information for readers and search engines to find valuable. Disabled by default - enable based on your content strategy.', 'wpshadow' ),
						'description_wizard' => __( 'Warn if posts are too short. Google generally prefers longer, more detailed content. Set minimum word count based on your site\'s content strategy.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'show_editor_panel'      => array(
						'name'               => __( 'Show Review Panel', 'wpshadow' ),
						'description_short'  => __( 'Display review panel in editor sidebar', 'wpshadow' ),
						'description_long'   => __( 'Shows a sidebar panel in the block editor displaying all active review checks and their status. You can run checks, see results, and fix issues directly in the editor. The panel updates in real-time as you edit.', 'wpshadow' ),
						'description_wizard' => __( 'Show check results in the editor sidebar so you can see and fix issues while writing.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'block_on_errors'        => array(
						'name'               => __( 'Block Publishing on Errors', 'wpshadow' ),
						'description_short'  => __( 'Prevent publishing with critical issues', 'wpshadow' ),
						'description_long'   => __( 'Prevents you from publishing posts that have critical issues found by review checks. This is a strict mode that ensures only quality content gets published. You can still override if needed but the default is to require fixes first.', 'wpshadow' ),
						'description_wizard' => __( 'Force quality by preventing publishing of content with critical issues. Good for sites with content standards. Disabled by default.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'allow_user_preferences' => array(
						'name'               => __( 'Allow User Preferences', 'wpshadow' ),
						'description_short'  => __( 'Let users customize checks', 'wpshadow' ),
						'description_long'   => __( 'Allows individual users to customize which checks run for their own posts. Different authors may have different needs - some might want word count checks while others don\'t. This lets each user customize their checks independently.', 'wpshadow' ),
						'description_wizard' => __( 'Allow authors to customize which checks apply to their own posts. Useful for sites with varied content types and authors.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'show_dismiss_option'    => array(
						'name'               => __( 'Show Dismiss Option', 'wpshadow' ),
						'description_short'  => __( 'Allow hiding review warnings', 'wpshadow' ),
						'description_long'   => __( 'Shows a "never show again" checkbox in the review panel so users can hide warnings they don\'t find useful. Keeps the UI clean for authors who don\'t want to see certain checks repeated.', 'wpshadow' ),
						'description_wizard' => __( 'Let users dismiss checks they don\'t find useful. Reduces notification fatigue.', 'wpshadow' ),
						'default_enabled'    => true,
					),
				),
			)
		);

		$this->register_default_settings(
			array(
				'check_broken_links'     => true,
				'check_paste_cleanup'    => true,
				'check_missing_alt_text' => true,
				'check_empty_headings'   => true,
				'check_word_count'       => false,
				'show_editor_panel'      => true,
				'block_on_errors'        => false,
				'allow_user_preferences' => true,
				'show_dismiss_option'    => true,
				'min_word_count'         => 300,
			)
		);

		$this->log_activity( 'feature_initialized', 'Pre-Publish Review feature initialized', 'info' );
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

		// Enqueue editor scripts for review panel
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );

		// AJAX endpoint for running checks
		add_action( 'wp_ajax_wpshadow_pre_publish_check', array( $this, 'ajax_run_pre_publish_check' ) );

		// AJAX endpoint for saving user preferences
		add_action( 'wp_ajax_wpshadow_save_review_preferences', array( $this, 'ajax_save_user_preferences' ) );

		// Hook into post save to validate
		add_filter( 'wp_insert_post_data', array( $this, 'validate_post_before_publish' ), 10, 2 );

		// Add admin notices for validation results
		add_action( 'admin_notices', array( $this, 'display_validation_notices' ) );

		// REST API endpoint for checks
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Site health test
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		// Enqueue inline script for editor panel
		wp_add_inline_script(
			'wp-blocks',
			$this->get_editor_panel_script(),
			'after'
		);

		// Add styles for review panel
		wp_add_inline_style(
			'wp-edit-post',
			$this->get_editor_panel_styles()
		);

		// Get user preferences
		$user_prefs = $this->get_user_preferences();

		// Pass settings to JavaScript
		wp_localize_script(
			'wp-blocks',
			'wpshadowPrePublish',
			array(
				'enabled'             => $this->is_enabled(),
				'checkBrokenLinks'    => $this->is_check_enabled_for_user( 'check_broken_links', $user_prefs ),
				'checkPasteCleanup'   => $this->is_check_enabled_for_user( 'check_paste_cleanup', $user_prefs ),
				'checkMissingAltText' => $this->is_check_enabled_for_user( 'check_missing_alt_text', $user_prefs ),
				'checkEmptyHeadings'  => $this->is_check_enabled_for_user( 'check_empty_headings', $user_prefs ),
				'checkWordCount'      => $this->is_check_enabled_for_user( 'check_word_count', $user_prefs ),
				'showEditorPanel'     => $this->is_panel_visible_for_user( $user_prefs ),
				'blockOnErrors'       => $this->is_sub_feature_enabled( 'block_on_errors', false ),
				'allowUserPrefs'      => $this->is_sub_feature_enabled( 'allow_user_preferences', true ),
				'showDismissOption'   => $this->is_sub_feature_enabled( 'show_dismiss_option', true ),
				'minWordCount'        => (int) $this->get_setting( 'min_word_count', 300 ),
				'userPreferences'     => $user_prefs,
				'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
				'nonce'               => wp_create_nonce( 'wpshadow_pre_publish_check' ),
				'prefsNonce'          => wp_create_nonce( 'wpshadow_save_review_prefs' ),
			)
		);
	}

	/**
	 * Get JavaScript for editor panel.
	 *
	 * @return string
	 */
	private function get_editor_panel_script(): string {
		return <<<'JAVASCRIPT'
(function() {
	if (!window.wpshadowPrePublish || !window.wpshadowPrePublish.enabled) {
		return;
	}

	const { registerPlugin } = wp.plugins;
	const { PluginPrePublishPanel } = wp.editPost;
	const { Component } = wp.element;
	const { __ } = wp.i18n;
	const { Spinner } = wp.components;
	const settings = window.wpshadowPrePublish;

	class WPShadowPrePublishReview extends Component {
		constructor(props) {
			super(props);
			this.state = {
				checking: false,
				issues: [],
				lastCheck: null,
				userPrefs: settings.userPreferences || {},
				showPreferences: false
			};
		}

		componentDidMount() {
			if (settings.showEditorPanel) {
				this.runChecks();
			}
		}

		dismissPermanently = () => {
			if (confirm('Are you sure? This will hide the pre-publish review panel until you re-enable it in WPShadow settings.')) {
				this.saveUserPreference('panel_dismissed', true);
				this.setState({ userPrefs: { ...this.state.userPrefs, panel_dismissed: true } });
			}
		}

		toggleCheckPreference = (checkName) => {
			const newValue = !this.state.userPrefs[checkName];
			this.saveUserPreference(checkName, newValue);
			this.setState({ 
				userPrefs: { ...this.state.userPrefs, [checkName]: newValue }
			}, () => {
				this.runChecks();
			});
		}

		saveUserPreference = (key, value) => {
			fetch(settings.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({
					action: 'wpshadow_save_review_preferences',
					nonce: settings.prefsNonce,
					key: key,
					value: value ? '1' : '0'
				})
			});
		}

		runChecks = () => {
			this.setState({ checking: true });
			
			const content = wp.data.select('core/editor').getEditedPostContent();
			const title = wp.data.select('core/editor').getEditedPostAttribute('title');
			
			const issues = [];

			// Check word count
			if (settings.checkWordCount) {
				const wordCount = content.replace(/<[^>]*>/g, '').split(/\\s+/).filter(w => w.length > 0).length;
				if (wordCount < settings.minWordCount) {
					issues.push({
						type: 'warning',
						message: `Content is only ${wordCount} words (recommended: ${settings.minWordCount}+)`
					});
				}
			}

			// Check for missing alt text
			if (settings.checkMissingAltText) {
				const imgMatches = content.match(/<img[^>]*>/g) || [];
				let missingAlt = 0;
				imgMatches.forEach(img => {
					if (!img.includes('alt=') || img.includes('alt=\"\"')) {
						missingAlt++;
					}
				});
				if (missingAlt > 0) {
					issues.push({
						type: 'error',
						message: `${missingAlt} image(s) missing descriptions for accessibility`
					});
				}
			}

			// Check for empty headings
			if (settings.checkEmptyHeadings) {
				const headingMatches = content.match(/<h[1-6][^>]*>\\s*<\\/h[1-6]>/g);
				if (headingMatches && headingMatches.length > 0) {
					issues.push({
						type: 'warning',
						message: `${headingMatches.length} empty section title(s) found`
					});
				}
			}

			// Check for paste cleanup indicators (inline styles, classes)
			if (settings.checkPasteCleanup) {
				const hasInlineStyles = content.includes('style=\"') || content.includes(\"style='\");
				const hasMsoClasses = content.match(/class=[\"'][^\"']*Mso[^\"']*[\"']/i);
				if (hasInlineStyles || hasMsoClasses) {
					issues.push({
						type: 'warning',
						message: 'Content may have messy formatting from pasting (inline styles or Word code detected)'
					});
				}
			}

			// Check for broken links (basic client-side check)
			if (settings.checkBrokenLinks && !this.state.userPrefs.check_broken_links_disabled) {
				const linkMatches = content.match(/href=[\"']([^\"']+)[\"']/g) || [];
				let suspiciousLinks = 0;
				linkMatches.forEach(link => {
					const url = link.replace(/href=[\"']([^\"']+)[\"']/, '$1');
					if (url.includes('localhost') || url.includes('127.0.0.1') || url.includes('.local')) {
						suspiciousLinks++;
					}
				});
				if (suspiciousLinks > 0) {
					issues.push({
						type: 'error',
						message: `${suspiciousLinks} link(s) point to local/development addresses`
					});
				}
			}

			this.setState({
				checking: false,
				issues: issues,
				lastCheck: new Date()
			});
		}

		render() {
			const { checking, issues, lastCheck, userPrefs, showPreferences } = this.state;

			// If user dismissed permanently, don't render
			if (userPrefs.panel_dismissed) {
				return null;
			}

			return wp.element.createElement(
				'div',
				{ className: 'wpshadow-pre-publish-review' },
				wp.element.createElement('h3', {}, 'WPShadow Content Review'),
				checking && wp.element.createElement(Spinner),
				!checking && issues.length === 0 && wp.element.createElement(
					'div',
					{ className: 'wpshadow-review-success' },
					'✓ No issues found! Your content looks great.'
				),
				!checking && issues.length > 0 && wp.element.createElement(
					'div',
					{ className: 'wpshadow-review-issues' },
					wp.element.createElement('p', { style: { fontWeight: 'bold' } }, 
						`Found ${issues.length} issue(s):`
					),
					wp.element.createElement(
						'ul',
						{ style: { marginLeft: '20px' } },
						issues.map((issue, i) => 
							wp.element.createElement(
								'li',
								{ 
									key: i,
									className: `wpshadow-issue-${issue.type}`,
									style: { 
										marginBottom: '8px',
										color: issue.type === 'error' ? '#d63638' : '#dba617'
									}
								},
								issue.message
							)
						)
					)
				),
				!checking && wp.element.createElement(
					'div',
					{ style: { marginTop: '10px', display: 'flex', gap: '8px', flexWrap: 'wrap' } },
					wp.element.createElement(
						'button',
						{
							className: 'button button-secondary',
							onClick: this.runChecks
						},
						'Run Checks Again'
					),
					settings.allowUserPrefs && wp.element.createElement(
						'button',
						{
							className: 'button button-secondary',
							onClick: () => this.setState({ showPreferences: !showPreferences })
						},
						showPreferences ? 'Hide Options' : 'Customize Checks'
					),
					settings.showDismissOption && wp.element.createElement(
						'button',
						{
							className: 'button button-link-delete',
							onClick: this.dismissPermanently,
							style: { marginLeft: 'auto' }
						},
						'Never Show Again'
					)
				),
				settings.allowUserPrefs && showPreferences && wp.element.createElement(
					'div',
					{ 
						className: 'wpshadow-review-preferences',
						style: { 
							marginTop: '12px', 
							padding: '12px', 
							background: '#f6f7f7',
							borderRadius: '4px'
						}
					},
					wp.element.createElement('strong', { style: { display: 'block', marginBottom: '8px' } }, 'Customize Your Checks:'),
					wp.element.createElement(
						'label',
						{ style: { display: 'block', marginBottom: '6px' } },
						wp.element.createElement('input', {
							type: 'checkbox',
							checked: !userPrefs.check_broken_links_disabled,
							onChange: () => this.toggleCheckPreference('check_broken_links_disabled')
						}),
						' Check for broken links'
					),
					wp.element.createElement(
						'label',
						{ style: { display: 'block', marginBottom: '6px' } },
						wp.element.createElement('input', {
							type: 'checkbox',
							checked: !userPrefs.check_paste_cleanup_disabled,
							onChange: () => this.toggleCheckPreference('check_paste_cleanup_disabled')
						}),
						' Check for messy pasted content'
					),
					wp.element.createElement(
						'label',
						{ style: { display: 'block', marginBottom: '6px' } },
						wp.element.createElement('input', {
							type: 'checkbox',
							checked: !userPrefs.check_missing_alt_disabled,
							onChange: () => this.toggleCheckPreference('check_missing_alt_disabled')
						}),
						' Check for missing image descriptions'
					),
					wp.element.createElement(
						'label',
						{ style: { display: 'block', marginBottom: '6px' } },
						wp.element.createElement('input', {
							type: 'checkbox',
							checked: !userPrefs.check_empty_headings_disabled,
							onChange: () => this.toggleCheckPreference('check_empty_headings_disabled')
						}),
						' Check for empty headings'
					),
					wp.element.createElement(
						'p',
						{ style: { marginTop: '8px', fontSize: '12px', color: '#666', fontStyle: 'italic' } },
						'These settings only affect you. Admins can disable checks for all users in WPShadow settings.'
					)
				)
			);
		}
	}

	const WPShadowPrePublishPanel = () => {
		return wp.element.createElement(
			PluginPrePublishPanel,
			{
				title: 'Content Review',
				initialOpen: true
			},
			wp.element.createElement(WPShadowPrePublishReview)
		);
	};

	if (settings.showEditorPanel) {
		registerPlugin('wpshadow-pre-publish-review', {
			render: WPShadowPrePublishPanel
		});
	}
})();
JAVASCRIPT;
	}

	/**
	 * Get CSS styles for editor panel.
	 *
	 * @return string
	 */
	private function get_editor_panel_styles(): string {
		return <<<'CSS'
.wpshadow-pre-publish-review {
	padding: 12px;
}
.wpshadow-review-success {
	padding: 12px;
	background: #d7f0d7;
	border-left: 4px solid #00a32a;
	margin: 8px 0;
}
.wpshadow-review-issues {
	padding: 12px;
	background: #fcf9e8;
	border-left: 4px solid #dba617;
	margin: 8px 0;
}
.wpshadow-issue-error {
	list-style-type: '⚠️ ';
}
.wpshadow-issue-warning {
	list-style-type: '⚡ ';
}
CSS;
	}

	/**
	 * AJAX handler for pre-publish check.
	 *
	 * @return void
	 */
	public function ajax_run_pre_publish_check(): void {
		check_ajax_referer( 'wpshadow_pre_publish_check', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';

		$issues = $this->run_content_checks( $post_id, $content );

		wp_send_json_success( array(
			'issues' => $issues,
			'count'  => count( $issues ),
		) );
	}

	/**
	 * Run all enabled content checks.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $content Post content.
	 * @return array<int, array<string, string>>
	 */
	private function run_content_checks( int $post_id, string $content ): array {
		$issues = array();

		// Check for broken links
		if ( $this->is_sub_feature_enabled( 'check_broken_links', true ) ) {
			$link_issues = $this->check_for_broken_links( $content );
			$issues      = array_merge( $issues, $link_issues );
		}

		// Check for paste cleanup needed
		if ( $this->is_sub_feature_enabled( 'check_paste_cleanup', true ) ) {
			$paste_issues = $this->check_for_paste_issues( $content );
			$issues       = array_merge( $issues, $paste_issues );
		}

		// Check for missing alt text
		if ( $this->is_sub_feature_enabled( 'check_missing_alt_text', true ) ) {
			$alt_issues = $this->check_for_missing_alt_text( $content );
			$issues     = array_merge( $issues, $alt_issues );
		}

		// Check for empty headings
		if ( $this->is_sub_feature_enabled( 'check_empty_headings', true ) ) {
			$heading_issues = $this->check_for_empty_headings( $content );
			$issues         = array_merge( $issues, $heading_issues );
		}

		// Check word count
		if ( $this->is_sub_feature_enabled( 'check_word_count', false ) ) {
			$word_count_issues = $this->check_word_count( $content );
			$issues            = array_merge( $issues, $word_count_issues );
		}

		// Run external reviewers registered by other features
		if ( $post_id > 0 ) {
			$post = get_post( $post_id );
			if ( $post ) {
				$external_reviews = self::run_external_reviews( $post );
				foreach ( $external_reviews as $review ) {
					if ( ! empty( $review['issues'] ) && is_array( $review['issues'] ) ) {
						$issues = array_merge( $issues, $review['issues'] );
					}
				}
			}
		}

		// Allow other features to add their checks (legacy filter)
		$issues = apply_filters( 'wpshadow_pre_publish_checks', $issues, $post_id );

		return $issues;
	}

	/**
	 * Check for broken links in content.
	 *
	 * @param string $content Post content.
	 * @return array<int, array<string, string>>
	 */
	private function check_for_broken_links( string $content ): array {
		$issues = array();

		// Extract all links
		preg_match_all( '/<a[^>]+href=["\'](.*?)["\']/', $content, $matches );

		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $url ) {
				// Check for local development URLs
				if ( strpos( $url, 'localhost' ) !== false || strpos( $url, '127.0.0.1' ) !== false || strpos( $url, '.local' ) !== false ) {
					$issues[] = array(
						'type'    => 'error',
						'message' => sprintf( __( 'Development URL found: %s', 'wpshadow' ), esc_url( $url ) ),
					);
				}

				// Check for malformed URLs
				if ( ! filter_var( $url, FILTER_VALIDATE_URL ) && strpos( $url, '#' ) !== 0 && strpos( $url, 'mailto:' ) !== 0 ) {
					$issues[] = array(
						'type'    => 'warning',
						'message' => sprintf( __( 'Invalid URL format: %s', 'wpshadow' ), esc_html( $url ) ),
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Check for paste cleanup issues.
	 *
	 * @param string $content Post content.
	 * @return array<int, array<string, string>>
	 */
	private function check_for_paste_issues( string $content ): array {
		$issues = array();

		// Check for inline styles
		if ( preg_match( '/style=["\'][^"\']+["\']/', $content ) ) {
			$issues[] = array(
				'type'    => 'warning',
				'message' => __( 'Inline styles detected - content may have messy formatting from pasting', 'wpshadow' ),
			);
		}

		// Check for Word-specific classes
		if ( preg_match( '/class=["\'][^"\']*Mso[^"\']*["\']/', $content ) ) {
			$issues[] = array(
				'type'    => 'warning',
				'message' => __( 'Microsoft Word formatting detected - consider running paste cleanup', 'wpshadow' ),
			);
		}

		return $issues;
	}

	/**
	 * Check for missing alt text.
	 *
	 * @param string $content Post content.
	 * @return array<int, array<string, string>>
	 */
	private function check_for_missing_alt_text( string $content ): array {
		$issues = array();

		preg_match_all( '/<img[^>]*>/', $content, $matches );

		if ( ! empty( $matches[0] ) ) {
			$missing_alt = 0;
			foreach ( $matches[0] as $img_tag ) {
				if ( ! preg_match( '/alt=["\'][^"\']+["\']/', $img_tag ) ) {
					$missing_alt++;
				}
			}

			if ( $missing_alt > 0 ) {
				$issues[] = array(
					'type'    => 'error',
					'message' => sprintf(
						/* translators: %d: Number of images without alt text */
						_n( '%d image missing description for accessibility', '%d images missing descriptions for accessibility', $missing_alt, 'wpshadow' ),
						$missing_alt
					),
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for empty headings.
	 *
	 * @param string $content Post content.
	 * @return array<int, array<string, string>>
	 */
	private function check_for_empty_headings( string $content ): array {
		$issues = array();

		if ( preg_match_all( '/<h[1-6][^>]*>\s*<\/h[1-6]>/', $content, $matches ) ) {
			$issues[] = array(
				'type'    => 'warning',
				'message' => sprintf(
					/* translators: %d: Number of empty headings */
					_n( '%d empty section title found', '%d empty section titles found', count( $matches[0] ), 'wpshadow' ),
					count( $matches[0] )
				),
			);
		}

		return $issues;
	}

	/**
	 * Check word count.
	 *
	 * @param string $content Post content.
	 * @return array<int, array<string, string>>
	 */
	private function check_word_count( string $content ): array {
		$issues = array();

		$text       = wp_strip_all_tags( $content );
		$word_count = count( preg_split( '/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY ) );
		$min_count  = (int) $this->get_setting( 'min_word_count', 300 );

		if ( $word_count < $min_count ) {
			$issues[] = array(
				'type'    => 'warning',
				'message' => sprintf(
					/* translators: 1: Current word count, 2: Minimum recommended count */
					__( 'Content is only %1$d words (recommended: %2$d+)', 'wpshadow' ),
					$word_count,
					$min_count
				),
			);
		}

		return $issues;
	}

	/**
	 * Validate post before publish.
	 *
	 * @param array<string, mixed> $data    Post data.
	 * @param array<string, mixed> $postarr Post array.
	 * @return array<string, mixed>
	 */
	public function validate_post_before_publish( array $data, array $postarr ): array {
		// Only check when publishing
		if ( $data['post_status'] !== 'publish' ) {
			return $data;
		}

		// Skip auto-saves and revisions
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $data;
		}

		$post_id = isset( $postarr['ID'] ) ? $postarr['ID'] : 0;
		$issues  = $this->run_content_checks( $post_id, $data['post_content'] );

		// Store issues for display
		if ( ! empty( $issues ) ) {
			set_transient( 'wpshadow_publish_issues_' . get_current_user_id(), $issues, 60 );

			// If blocking is enabled and there are errors, prevent publish
			if ( $this->is_sub_feature_enabled( 'block_on_errors', false ) ) {
				$has_errors = false;
				foreach ( $issues as $issue ) {
					if ( $issue['type'] === 'error' ) {
						$has_errors = true;
						break;
					}
				}

				if ( $has_errors ) {
					$data['post_status'] = 'draft';
					$this->log_activity( 'publish_blocked', sprintf( 'Post %d publish blocked due to %d validation errors', $post_id, count( $issues ) ), 'warning' );
				}
			}
		}

		return $data;
	}

	/**
	 * Display validation notices.
	 *
	 * @return void
	 */
	public function display_validation_notices(): void {
		$issues = get_transient( 'wpshadow_publish_issues_' . get_current_user_id() );

		if ( ! empty( $issues ) ) {
			delete_transient( 'wpshadow_publish_issues_' . get_current_user_id() );

			$error_count   = 0;
			$warning_count = 0;

			foreach ( $issues as $issue ) {
				if ( $issue['type'] === 'error' ) {
					$error_count++;
				} else {
					$warning_count++;
				}
			}

			$class = $error_count > 0 ? 'error' : 'warning';
			?>
			<div class="notice notice-<?php echo esc_attr( $class ); ?> is-dismissible">
				<p><strong><?php esc_html_e( 'WPShadow Content Review:', 'wpshadow' ); ?></strong></p>
				<ul style="list-style: disc; margin-left: 20px;">
					<?php foreach ( $issues as $issue ) : ?>
						<li><?php echo esc_html( $issue['message'] ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php if ( $this->is_sub_feature_enabled( 'block_on_errors', false ) && $error_count > 0 ) : ?>
					<p><em><?php esc_html_e( 'Publish was prevented. Please fix the errors above and try again.', 'wpshadow' ); ?></em></p>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'wpshadow/v1',
			'/pre-publish-check',
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
		$content = $request->get_param( 'content' );

		$issues = $this->run_content_checks( (int) $post_id, $content );

		return rest_ensure_response(
			array(
				'issues' => $issues,
				'count'  => count( $issues ),
			)
		);
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, array<string, mixed>> $tests Site Health tests.
	 * @return array<string, array<string, mixed>>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_pre_publish_review'] = array(
			'label' => __( 'Pre-Publish Content Review', 'wpshadow' ),
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
			'label'       => __( 'Pre-publish review is active', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Content Quality', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Content is automatically checked for issues before publishing.', 'wpshadow' )
			),
			'actions'     => '',
			'test'        => 'wpshadow_pre_publish_review',
		);

		if ( ! $this->is_enabled() ) {
			$result['status']      = 'recommended';
			$result['label']       = __( 'Pre-publish review is not enabled', 'wpshadow' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__( 'Enable pre-publish review to automatically check content for broken links, formatting issues, and accessibility problems before publishing.', 'wpshadow' )
			);
			$result['actions'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features' ),
				__( 'Enable Pre-Publish Review', 'wpshadow' )
			);
		} else {
			$active_checks = array();

			if ( $this->is_sub_feature_enabled( 'check_broken_links', true ) ) {
				$active_checks[] = __( 'Broken links', 'wpshadow' );
			}
			if ( $this->is_sub_feature_enabled( 'check_paste_cleanup', true ) ) {
				$active_checks[] = __( 'Paste cleanup', 'wpshadow' );
			}
			if ( $this->is_sub_feature_enabled( 'check_missing_alt_text', true ) ) {
				$active_checks[] = __( 'Missing image descriptions', 'wpshadow' );
			}

			$result['description'] = sprintf(
				'<p>%s</p><p><strong>%s:</strong> %s</p>',
				__( 'Posts are checked before publishing.', 'wpshadow' ),
				__( 'Active checks', 'wpshadow' ),
				implode( ', ', $active_checks )
			);

			if ( $this->is_sub_feature_enabled( 'block_on_errors', false ) ) {
				$result['description'] .= sprintf(
					'<p><em>%s</em></p>',
					__( 'Publishing is blocked when errors are found.', 'wpshadow' )
				);
			}
		}

		return $result;
	}

	/**
	 * Get user preferences for review checks.
	 *
	 * @return array<string, bool>
	 */
	private function get_user_preferences(): array {
		$user_id = get_current_user_id();
		$prefs   = get_user_meta( $user_id, 'wpshadow_review_preferences', true );

		if ( ! is_array( $prefs ) ) {
			$prefs = array();
		}

		return $prefs;
	}

	/**
	 * Check if a specific check is enabled for the current user.
	 *
	 * @param string $check_name The check name.
	 * @param array<string, bool> $user_prefs User preferences.
	 * @return bool
	 */
	private function is_check_enabled_for_user( string $check_name, array $user_prefs ): bool {
		// Admin setting takes precedence
		if ( ! $this->is_sub_feature_enabled( $check_name, true ) ) {
			return false;
		}

		// If user preferences are not allowed, use admin setting
		if ( ! $this->is_sub_feature_enabled( 'allow_user_preferences', true ) ) {
			return true;
		}

		// Check user's personal preference
		$disabled_key = $check_name . '_disabled';
		return ! isset( $user_prefs[ $disabled_key ] ) || ! $user_prefs[ $disabled_key ];
	}

	/**
	 * Check if panel should be visible for current user.
	 *
	 * @param array<string, bool> $user_prefs User preferences.
	 * @return bool
	 */
	private function is_panel_visible_for_user( array $user_prefs ): bool {
		// Admin setting must be enabled
		if ( ! $this->is_sub_feature_enabled( 'show_editor_panel', true ) ) {
			return false;
		}

		// Check if user dismissed the panel
		return ! isset( $user_prefs['panel_dismissed'] ) || ! $user_prefs['panel_dismissed'];
	}

	/**
	 * AJAX handler for saving user preferences.
	 *
	 * @return void
	 */
	public function ajax_save_user_preferences(): void {
		check_ajax_referer( 'wpshadow_save_review_prefs', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$key   = isset( $_POST['key'] ) ? sanitize_key( $_POST['key'] ) : '';
		$value = isset( $_POST['value'] ) && $_POST['value'] === '1';

		if ( empty( $key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid preference key.', 'wpshadow' ) ) );
		}

		$user_id = get_current_user_id();
		$prefs   = $this->get_user_preferences();

		$prefs[ $key ] = $value;

		update_user_meta( $user_id, 'wpshadow_review_preferences', $prefs );

		wp_send_json_success( array(
			'message'     => __( 'Preference saved.', 'wpshadow' ),
			'preferences' => $prefs,
		) );
	}

	/**
	 * Register an external content reviewer from another feature.
	 *
	 * Allows other WPShadow features to add custom checks to the pre-publish review system.
	 *
	 * @param string $reviewer_id Unique reviewer identifier (e.g., 'seo-analyzer').
	 * @param array  $config      Reviewer configuration.
	 *                            - name (string) Display name for the reviewer
	 *                            - description (string) Description of what it checks
	 *                            - priority (int) Display priority (lower = higher priority)
	 *                            - callback (callable) Function to execute review
	 *                            - post_types (array) Post types to show reviewer for
	 *                            - severity (string) Default severity: 'info', 'warning', 'error'
	 *
	 * @return bool True if registered successfully.
	 */
	public static function register_external_reviewer( string $reviewer_id, array $config ): bool {
		if ( empty( $reviewer_id ) || empty( $config['callback'] ) || ! is_callable( $config['callback'] ) ) {
			return false;
		}

		$reviewer = array_merge(
			array(
				'id'          => $reviewer_id,
				'name'        => $config['name'] ?? $reviewer_id,
				'description' => $config['description'] ?? '',
				'priority'    => $config['priority'] ?? 10,
				'post_types'  => $config['post_types'] ?? array( 'post', 'page' ),
				'callback'    => $config['callback'],
				'severity'    => $config['severity'] ?? 'info',
			),
			$config
		);

		self::$external_reviewers[ $reviewer_id ] = $reviewer;
		return true;
	}

	/**
	 * Get registered external reviewers for a post type.
	 *
	 * @param string $post_type Post type to get reviewers for.
	 * @return array Array of registered reviewers for the post type.
	 */
	public static function get_external_reviewers_for_post_type( string $post_type ): array {
		$reviewers = array_filter(
			self::$external_reviewers,
			function ( $reviewer ) use ( $post_type ) {
				return in_array( $post_type, (array) $reviewer['post_types'], true );
			}
		);

		// Sort by priority
		uasort(
			$reviewers,
			function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			}
		);

		return $reviewers;
	}

	/**
	 * Run external reviewers for a post.
	 *
	 * Executes all registered external reviewer callbacks and aggregates results.
	 *
	 * @param \WP_Post $post Post object to review.
	 * @return array Array of external review results.
	 */
	public static function run_external_reviews( \WP_Post $post ): array {
		$reviewers = self::get_external_reviewers_for_post_type( $post->post_type );
		$reviews   = array();

		foreach ( $reviewers as $reviewer ) {
			try {
				$result = call_user_func( $reviewer['callback'], $post );

				if ( is_array( $result ) ) {
					$reviews[ $reviewer['id'] ] = array_merge(
						array(
							'id'       => $reviewer['id'],
							'name'     => $reviewer['name'],
							'severity' => $reviewer['severity'],
							'issues'   => array(),
							'passed'   => true,
						),
						$result
					);
				}
			} catch ( \Exception $e ) {
				// Log error but don't break the review process
				error_log( sprintf(
					'WPShadow Pre-Publish Review: External reviewer %s failed: %s',
					$reviewer['id'],
					$e->getMessage()
				) );
			}
		}

		return $reviews;
	}

	/**
	 * Get all registered external reviewers.
	 *
	 * @return array All registered reviewers sorted by priority.
	 */
	public static function get_all_external_reviewers(): array {
		$reviewers = self::$external_reviewers;
		
		uasort(
			$reviewers,
			function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			}
		);
		
		return $reviewers;
	}
}
