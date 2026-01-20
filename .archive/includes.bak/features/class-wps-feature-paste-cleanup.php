<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Paste_Cleanup extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'paste-cleanup',
				'name'               => __( 'Clean Up Pasted Content', 'wpshadow' ),
				'description'        => __( 'Remove extra code and formatting when you copy and paste from Word documents or other websites. Keeps your content clean and consistent.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'content',
				'minimum_capability' => 'edit_posts',
				'aliases'            => array(
					'word formatting',
					'copy paste',
					'messy content',
					'word cleanup',
					'google docs',
					'remove formatting',
					'clean paste',
					'strip styles',
					'inline styles',
					'clean content',
				),
				'sub_features'    => array(
					'remove_inline_styles'  => array(
						'name'               => __( 'Remove Inline Styles', 'wpshadow' ),
						'description_short'  => __( 'Strip color and formatting from pasted text', 'wpshadow' ),
						'description_long'   => __( 'Removes inline CSS styles that Microsoft Word and other programs add when you copy and paste content. These styles often include colors, fonts, spacing, and formatting that don\'t match your site design and bloat the HTML. Removing them keeps your content clean and consistent with your theme\'s styling.', 'wpshadow' ),
						'description_wizard' => __( 'Word and other programs add lots of hidden formatting. Remove it to keep your content clean and matching your site style.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_classes'        => array(
						'name'               => __( 'Remove CSS Classes', 'wpshadow' ),
						'description_short'  => __( 'Strip CSS class names from pasted content', 'wpshadow' ),
						'description_long'   => __( 'Removes CSS class names that Word and other programs embed in HTML when pasting. These classes are usually based on Word\'s internal style names and are meaningless for web content. They add HTML bloat and can cause unexpected styling if your site has CSS rules that match these class names.', 'wpshadow' ),
						'description_wizard' => __( 'Word adds CSS class names like MsoNormal and style123 that serve no purpose on the web. Remove them to clean up your HTML.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_empty_tags'     => array(
						'name'               => __( 'Remove Empty Tags', 'wpshadow' ),
						'description_short'  => __( 'Delete empty paragraph and span tags', 'wpshadow' ),
						'description_long'   => __( 'Removes HTML tags that contain no content, which Word often adds when cleaning formatting. Empty paragraphs, spans, and divs add HTML size without contributing anything to the page. Removing them reduces the amount of cleanup needed.', 'wpshadow' ),
						'description_wizard' => __( 'Word leaves behind empty tags when converting formatting. Remove them to clean up HTML size.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_word_metadata'  => array(
						'name'               => __( 'Remove Word Metadata', 'wpshadow' ),
						'description_short'  => __( 'Strip Microsoft Word tracking and metadata', 'wpshadow' ),
						'description_long'   => __( 'Removes metadata and tracking information that Microsoft Word embeds in HTML. This includes revision markers, comments, tracked changes, and Word-specific XML. These tags are often invisible but can reveal information about document history and edits. Removing them improves security and privacy.', 'wpshadow' ),
						'description_wizard' => __( 'Word embeds invisible metadata about edits and changes. Remove this to clean up the HTML and improve security.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'clean_links'           => array(
						'name'               => __( 'Clean Link URLs', 'wpshadow' ),
						'description_short'  => __( 'Simplify and fix pasted link addresses', 'wpshadow' ),
						'description_long'   => __( 'Cleans up link URLs that often get mangled when pasted from documents. Word often adds unnecessary parameters and tracking codes to links. This removes those extras and ensures links are properly formatted for the web.', 'wpshadow' ),
						'description_wizard' => __( 'Links from documents often have unnecessary tracking codes. This cleans them up to simple, proper URLs.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'preserve_formatting'   => array(
						'name'               => __( 'Preserve Basic Formatting', 'wpshadow' ),
						'description_short'  => __( 'Keep bold, italic, and underline formatting', 'wpshadow' ),
						'description_long'   => __( 'Preserves essential text formatting like bold, italic, and underline that are important to content meaning. While removing Word\'s bloated styles, this keeps meaningful formatting using semantic HTML tags. Most sites want to keep emphasis formatting but remove the unnecessary styling.', 'wpshadow' ),
						'description_wizard' => __( 'Keep important text formatting like bold and italic while removing Word\'s unnecessary styling.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'image_cleanup'         => array(
						'name'               => __( 'Clean Pasted Images', 'wpshadow' ),
						'description_short'  => __( 'Resize and optimize images from documents', 'wpshadow' ),
						'description_long'   => __( 'Cleans up images pasted directly from documents. Word often pastes images at huge resolutions with unnecessary metadata. This feature resizes them to reasonable web dimensions and removes embedded metadata. Disabled by default as it requires image processing overhead.', 'wpshadow' ),
						'description_wizard' => __( 'Images from Word are often huge and bloated. Enable to automatically resize and optimize them for the web. Requires image processing.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'table_formatting'      => array(
						'name'               => __( 'Clean Table Formatting', 'wpshadow' ),
						'description_short'  => __( 'Normalize table structure and remove styles', 'wpshadow' ),
						'description_long'   => __( 'Cleans up table formatting when copying tables from Word documents. Removes excessive cell padding, backgrounds, and styling while preserving table structure. Makes pasted tables compatible with your site\'s table CSS. Disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Tables from Word need cleanup to match your site design. Enable to automatically normalize table formatting.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'list_normalization'    => array(
						'name'               => __( 'Normalize Lists', 'wpshadow' ),
						'description_short'  => __( 'Standardize bullet and number lists', 'wpshadow' ),
						'description_long'   => __( 'Standardizes list formatting from pasted content. Word often creates broken or nested lists with excessive formatting. This normalizes them into proper HTML lists. Disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Lists from Word are often broken or over-nested. Enable to fix list structure automatically.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'heading_hierarchy'     => array(
						'name'               => __( 'Fix Heading Hierarchy', 'wpshadow' ),
						'description_short'  => __( 'Enforce proper heading levels (H1, H2, H3...)', 'wpshadow' ),
						'description_long'   => __( 'Ensures headings follow proper semantic hierarchy (H1, then H2, then H3, etc.). Documents often have skipped levels or improper nesting. Proper hierarchy is important for accessibility and SEO. Disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Word often has broken heading hierarchies. Enable to automatically fix them for better accessibility and SEO.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'character_encoding'    => array(
						'name'               => __( 'Fix Special Characters', 'wpshadow' ),
						'description_short'  => __( 'Convert smart quotes and special characters', 'wpshadow' ),
						'description_long'   => __( 'Converts Word\'s smart quotes, em-dashes, and other special characters to proper HTML entities. Word uses proprietary characters that don\'t always display correctly on the web. This converts them to standard characters that work everywhere. Disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Word\'s smart quotes and special characters don\'t always work on the web. Enable to convert them to standard characters.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'whitespace_cleanup'    => array(
						'name'               => __( 'Clean Whitespace', 'wpshadow' ),
						'description_short'  => __( 'Collapse extra spaces and line breaks', 'wpshadow' ),
						'description_long'   => __( 'Removes excessive whitespace from pasted content including multiple spaces, unnecessary line breaks, and indentation. Documents often have formatting whitespace that doesn\'t make sense on the web. Disabled by default.', 'wpshadow' ),
						'description_wizard' => __( 'Extra spaces and line breaks from documents waste HTML space. Enable to clean them up.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'preview_before_after'  => array(
						'name'               => __( 'Show Preview', 'wpshadow' ),
						'description_short'  => __( 'Display before/after cleanup preview', 'wpshadow' ),
						'description_long'   => __( 'Shows a side-by-side preview of content before and after cleanup, so you can review what will be cleaned and reject changes if needed. Disabled by default to keep the editor lightweight.', 'wpshadow' ),
						'description_wizard' => __( 'See what cleanup will do before it\'s applied. Useful for troubleshooting or disabling specific cleanup steps.', 'wpshadow' ),
						'default_enabled'    => false,
					),
				),
			)
		);

		$this->register_default_settings(
			array(
				'remove_inline_styles'  => true,
				'remove_classes'        => true,
				'remove_empty_tags'     => true,
				'remove_word_metadata'  => true,
				'clean_links'           => true,
				'preserve_formatting'   => true,
				'image_cleanup'         => false,
				'table_formatting'      => false,
				'list_normalization'    => false,
				'heading_hierarchy'     => false,
				'character_encoding'    => false,
				'whitespace_cleanup'    => false,
				'preview_before_after'  => false,
			)
		);

		$this->log_activity( 'feature_initialized', 'Paste Cleanup feature initialized', 'info' );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_scripts' ) );

		add_filter( 'tiny_mce_before_init', array( $this, 'add_tinymce_paste_filter' ) );

		add_filter( 'content_save_pre', array( $this, 'cleanup_content_on_save' ), 10, 1 );

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && class_exists( '\WP_CLI' ) ) {
			$this->register_cli_commands();
		}
	}

	public function enqueue_editor_scripts(): void {
		$script_path = WPSHADOW_PATH . 'assets/js/paste-cleanup.js';
		$script_url  = WPSHADOW_URL . 'assets/js/paste-cleanup.js';

		if ( ! file_exists( $script_path ) ) {
			wp_add_inline_script(
				'wp-blocks',
				$this->get_inline_paste_cleanup_script(),
				'after'
			);
		} else {
			wp_enqueue_script(
				'wpshadow-paste-cleanup',
				$script_url,
				array( 'wp-blocks', 'wp-dom-ready', 'wp-hooks' ),
				WPSHADOW_VERSION,
				true
			);
		}

		wp_localize_script(
			'wp-blocks',
			'wpshadowPasteCleanup',
			array(
				'enabled'            => $this->is_enabled(),
				'removeInlineStyles' => $this->is_sub_feature_enabled( 'remove_inline_styles', true ),
				'removeClasses'      => $this->is_sub_feature_enabled( 'remove_classes', true ),
				'removeEmptyTags'    => $this->is_sub_feature_enabled( 'remove_empty_tags', true ),
				'removeWordMetadata' => $this->is_sub_feature_enabled( 'remove_word_metadata', true ),
				'cleanLinks'         => $this->is_sub_feature_enabled( 'clean_links', true ),
				'preserveFormatting' => $this->is_sub_feature_enabled( 'preserve_formatting', true ),
			)
		);

		$this->log_activity( 'paste_cleanup_scripts_loaded', 'Paste cleanup scripts enqueued for editor', 'info' );
	}

	private function get_inline_paste_cleanup_script(): string {
		return "
(function() {
	if (!window.wpshadowPasteCleanup || !window.wpshadowPasteCleanup.enabled) {
		return;
	}

	const settings = window.wpshadowPasteCleanup;

	function cleanPastedHTML(html) {
		const parser = new DOMParser();
		const doc = parser.parseFromString(html, 'text/html');

		if (settings.removeWordMetadata) {
			const wordElements = doc.querySelectorAll('[class*=\"Mso\"], [style*=\"mso-\"]');
			wordElements.forEach(el => {
				if (el.tagName === 'P' || el.tagName === 'DIV' || el.tagName === 'SPAN') {

					el.replaceWith(...el.childNodes);
				} else {
					el.remove();
				}
			});
		}

		if (settings.removeInlineStyles) {
			doc.querySelectorAll('[style]').forEach(el => {

				if (settings.preserveFormatting) {
					const style = el.getAttribute('style');
					const preservedStyles = [];
					if (style.includes('font-weight') && (style.includes('bold') || style.includes('700'))) {
						preservedStyles.push('font-weight:bold');
					}
					if (style.includes('font-style') && style.includes('italic')) {
						preservedStyles.push('font-style:italic');
					}
					if (preservedStyles.length > 0) {
						el.setAttribute('style', preservedStyles.join(';'));
					} else {
						el.removeAttribute('style');
					}
				} else {
					el.removeAttribute('style');
				}
			});
		}

		if (settings.removeClasses) {
			doc.querySelectorAll('[class]').forEach(el => {
				el.removeAttribute('class');
			});
		}

		if (settings.cleanLinks) {
			doc.querySelectorAll('a').forEach(link => {
				const href = link.getAttribute('href');
				if (href) {

					try {
						const url = new URL(href, window.location.origin);
						const cleanParams = new URLSearchParams();
						url.searchParams.forEach((value, key) => {

							if (!key.match(/^(utm_|fbclid|gclid|mc_|_ga)/i)) {
								cleanParams.append(key, value);
							}
						});
						url.search = cleanParams.toString();
						link.setAttribute('href', url.toString());
					} catch (e) {

					}
				}

				Array.from(link.attributes).forEach(attr => {
					if (attr.name !== 'href' && attr.name !== 'title') {
						link.removeAttribute(attr.name);
					}
				});
			});
		}

		if (settings.removeEmptyTags) {
			doc.querySelectorAll('p, span, div').forEach(el => {
				if (!el.textContent.trim() && !el.querySelector('img, br')) {
					el.remove();
				}
			});
		}

		doc.querySelectorAll('*').forEach(el => {
			el.removeAttribute('id');
			el.removeAttribute('data-*');
			if (el.hasAttribute('lang') && el.getAttribute('lang') === 'EN-US') {
				el.removeAttribute('lang');
			}
		});

		return doc.body.innerHTML;
	}

	if (window.wp && window.wp.hooks) {
		wp.hooks.addFilter(
			'editor.pastedContent',
			'wpshadow/paste-cleanup',
			function(content) {
				if (typeof content === 'string') {
					return cleanPastedHTML(content);
				}
				return content;
			}
		);
	}

	document.addEventListener('paste', function(e) {
		if (!e.target.closest('.editor-styles-wrapper, .block-editor-writing-flow')) {
			return;
		}

		const clipboardData = e.clipboardData || window.clipboardData;
		const pastedHTML = clipboardData.getData('text/html');

		if (pastedHTML) {
			const cleaned = cleanPastedHTML(pastedHTML);
			if (cleaned !== pastedHTML) {
				console.log('WPShadow: Cleaned pasted content');
			}
		}
	}, true);
})();
";
	}

	public function add_tinymce_paste_filter( array $init ): array {

		$init['paste_preprocess'] = 'function(plugin, args) {
			var content = args.content;

			if (' . ( $this->is_sub_feature_enabled( 'remove_word_metadata', true ) ? 'true' : 'false' ) . ') {
				content = content.replace(/<(\w+)[^>]*class="?Mso[^"]*"?[^>]*>/gi, "");
				content = content.replace(/<(\w+)[^>]*style="[^"]*mso-[^"]*"[^>]*>/gi, "");
			}

			if (' . ( $this->is_sub_feature_enabled( 'remove_inline_styles', true ) ? 'true' : 'false' ) . ') {
				content = content.replace(/style="[^"]*"/gi, "");
			}

			if (' . ( $this->is_sub_feature_enabled( 'remove_classes', true ) ? 'true' : 'false' ) . ') {
				content = content.replace(/class="[^"]*"/gi, "");
			}

			args.content = content;
		}';

		return $init;
	}

	public function cleanup_content_on_save( string $content ): string {
		if ( ! $this->is_enabled() ) {
			return $content;
		}

		if ( $this->is_sub_feature_enabled( 'remove_word_metadata', true ) ) {
			$content = preg_replace( '/class=["\'][^"\']*Mso[^"\']*["\']/i', '', $content );
			$content = preg_replace( '/<o:p>.*?<\/o:p>/is', '', $content );
		}

		if ( $this->is_sub_feature_enabled( 'remove_inline_styles', true ) ) {
			$content = preg_replace( '/style=["\'][^"\']*["\']/i', '', $content );
		}

		if ( $this->is_sub_feature_enabled( 'remove_empty_tags', true ) ) {
			$content = preg_replace( '/<p[^>]*>(\s|&nbsp;)*<\/p>/i', '', $content );
		}

		do_action( 'wpshadow_paste_cleanup_cleaned', $content );

		return $content;
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_paste_cleanup'] = array(
			'label' => __( 'Paste Content Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'site_health_test' ),
		);

		return $tests;
	}

	private function register_cli_commands(): void {
		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		$feature = $this;

		\WP_CLI::add_command(
			'wpshadow paste-clean',
			new class( $feature ) {
				private WPSHADOW_Feature_Paste_Cleanup $feature;

				public function __construct( WPSHADOW_Feature_Paste_Cleanup $feature ) {
					$this->feature = $feature;
				}

				public function __invoke( array $args, array $assoc_args ): void {
					$content = (string) ( $assoc_args['content'] ?? '' );
					if ( '' === $content ) {
						\WP_CLI::error( 'Please provide --content to clean.' );
					}
					$cleaned = $this->feature->cleanup_content_on_save( $content );
					\WP_CLI::line( $cleaned );
				}
			}
		);
	}

	public function site_health_test(): array {
		$result = array(
			'label'       => __( 'Paste cleanup is active', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Content', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Content pasted from Word documents and websites is automatically cleaned to remove extra formatting and code.', 'wpshadow' )
			),
			'actions'     => '',
			'test'        => 'wpshadow_paste_cleanup',
		);

		if ( ! $this->is_enabled() ) {
			$result['status']      = 'recommended';
			$result['label']       = __( 'Paste cleanup is not enabled', 'wpshadow' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__( 'Enable paste cleanup to automatically remove extra formatting when copying content from Word documents or other websites.', 'wpshadow' )
			);
			$result['actions'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features' ),
				__( 'Enable Paste Cleanup', 'wpshadow' )
			);
		} else {
			$active_features = array();

			if ( $this->is_sub_feature_enabled( 'remove_inline_styles', true ) ) {
				$active_features[] = __( 'Inline styles removal', 'wpshadow' );
			}
			if ( $this->is_sub_feature_enabled( 'remove_word_metadata', true ) ) {
				$active_features[] = __( 'Word metadata cleanup', 'wpshadow' );
			}
			if ( $this->is_sub_feature_enabled( 'clean_links', true ) ) {
				$active_features[] = __( 'Link cleanup', 'wpshadow' );
			}

			if ( ! empty( $active_features ) ) {
				$result['description'] = sprintf(
					'<p>%s</p><p><strong>%s:</strong> %s</p>',
					__( 'Pasted content is being automatically cleaned.', 'wpshadow' ),
					__( 'Active features', 'wpshadow' ),
					implode( ', ', $active_features )
				);
			}
		}

		return $result;
	}
}
