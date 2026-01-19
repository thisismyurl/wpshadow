<?php
/**
 * Feature: Paste Cleanup for Block Editor
 *
 * Automatically cleans content pasted from Microsoft Word, Google Docs, and
 * other websites to remove extra classes, inline styles, and unwanted markup.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75003
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Paste_Cleanup
 *
 * Cleans pasted content in the block editor.
 */
final class WPSHADOW_Feature_Paste_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'paste-cleanup',
				'name'            => __( 'Clean Up Pasted Content', 'wpshadow' ),
				'description'     => __( 'Remove extra code and formatting when you copy and paste from Word documents or other websites. Keeps your content clean and consistent.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => true,
				'version'         => '1.0.0',
				'widget_group'    => 'content',
				'aliases'         => array(
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
					'remove_inline_styles'  => __( 'Remove colored text and fancy fonts', 'wpshadow' ),
					'remove_classes'        => __( 'Remove hidden code labels', 'wpshadow' ),
					'remove_empty_tags'     => __( 'Remove empty spaces and breaks', 'wpshadow' ),
					'remove_word_metadata'  => __( 'Remove Microsoft Word tracking data', 'wpshadow' ),
					'clean_links'           => __( 'Clean up messy link addresses', 'wpshadow' ),
					'preserve_formatting'   => __( 'Keep bold, italic, and basic formatting', 'wpshadow' ),
					'image_cleanup'         => __( 'Clean or resize pasted images', 'wpshadow' ),
					'table_formatting'      => __( 'Normalize tables from documents', 'wpshadow' ),
					'list_normalization'    => __( 'Standardize bullet and number lists', 'wpshadow' ),
					'heading_hierarchy'     => __( 'Enforce clean heading order (H2→H3→H4)', 'wpshadow' ),
					'character_encoding'    => __( 'Fix smart quotes and special characters', 'wpshadow' ),
					'whitespace_cleanup'    => __( 'Collapse extra spaces and line breaks', 'wpshadow' ),
					'preview_before_after'  => __( 'Show before/after cleaned content preview', 'wpshadow' ),
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

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Enqueue block editor scripts
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_scripts' ) );

		// Add filter for classic editor (TinyMCE)
		add_filter( 'tiny_mce_before_init', array( $this, 'add_tinymce_paste_filter' ) );

		// Server-side content cleanup filter
		add_filter( 'content_save_pre', array( $this, 'cleanup_content_on_save' ), 10, 1 );

		// Site health test
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		// WP-CLI commands
		if ( defined( 'WP_CLI' ) && class_exists( '\WP_CLI' ) ) {
			$this->register_cli_commands();
		}
	}

	/**
	 * Enqueue block editor scripts for paste cleanup.
	 *
	 * @return void
	 */
	public function enqueue_editor_scripts(): void {
		$script_path = WPSHADOW_PATH . 'assets/js/paste-cleanup.js';
		$script_url  = WPSHADOW_URL . 'assets/js/paste-cleanup.js';

		// Create inline script if file doesn't exist
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

		// Pass settings to JavaScript
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

	/**
	 * Get inline JavaScript for paste cleanup.
	 *
	 * @return string
	 */
	private function get_inline_paste_cleanup_script(): string {
		return "
(function() {
	if (!window.wpshadowPasteCleanup || !window.wpshadowPasteCleanup.enabled) {
		return;
	}

	const settings = window.wpshadowPasteCleanup;

	// Clean pasted HTML content
	function cleanPastedHTML(html) {
		const parser = new DOMParser();
		const doc = parser.parseFromString(html, 'text/html');
		
		// Remove Word metadata
		if (settings.removeWordMetadata) {
			const wordElements = doc.querySelectorAll('[class*=\"Mso\"], [style*=\"mso-\"]');
			wordElements.forEach(el => {
				if (el.tagName === 'P' || el.tagName === 'DIV' || el.tagName === 'SPAN') {
					// Keep text content, remove wrapper
					el.replaceWith(...el.childNodes);
				} else {
					el.remove();
				}
			});
		}

		// Remove inline styles
		if (settings.removeInlineStyles) {
			doc.querySelectorAll('[style]').forEach(el => {
				// Preserve basic formatting if enabled
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

		// Remove classes
		if (settings.removeClasses) {
			doc.querySelectorAll('[class]').forEach(el => {
				el.removeAttribute('class');
			});
		}

		// Clean links
		if (settings.cleanLinks) {
			doc.querySelectorAll('a').forEach(link => {
				const href = link.getAttribute('href');
				if (href) {
					// Remove tracking parameters
					try {
						const url = new URL(href, window.location.origin);
						const cleanParams = new URLSearchParams();
						url.searchParams.forEach((value, key) => {
							// Keep only essential parameters
							if (!key.match(/^(utm_|fbclid|gclid|mc_|_ga)/i)) {
								cleanParams.append(key, value);
							}
						});
						url.search = cleanParams.toString();
						link.setAttribute('href', url.toString());
					} catch (e) {
						// Invalid URL, leave as is
					}
				}
				// Remove link attributes except href and title
				Array.from(link.attributes).forEach(attr => {
					if (attr.name !== 'href' && attr.name !== 'title') {
						link.removeAttribute(attr.name);
					}
				});
			});
		}

		// Remove empty tags
		if (settings.removeEmptyTags) {
			doc.querySelectorAll('p, span, div').forEach(el => {
				if (!el.textContent.trim() && !el.querySelector('img, br')) {
					el.remove();
				}
			});
		}

		// Remove other unwanted attributes
		doc.querySelectorAll('*').forEach(el => {
			el.removeAttribute('id');
			el.removeAttribute('data-*');
			if (el.hasAttribute('lang') && el.getAttribute('lang') === 'EN-US') {
				el.removeAttribute('lang');
			}
		});

		return doc.body.innerHTML;
	}

	// Hook into Gutenberg paste event
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

	// Fallback for direct paste events
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

	/**
	 * Add paste filter for TinyMCE (Classic Editor).
	 *
	 * @param array<string, mixed> $init TinyMCE init settings.
	 * @return array<string, mixed>
	 */
	public function add_tinymce_paste_filter( array $init ): array {
		// Enable paste preprocessing
		$init['paste_preprocess'] = 'function(plugin, args) {
			var content = args.content;
			
			// Remove Word metadata
			if (' . ( $this->is_sub_feature_enabled( 'remove_word_metadata', true ) ? 'true' : 'false' ) . ') {
				content = content.replace(/<(\w+)[^>]*class="?Mso[^"]*"?[^>]*>/gi, "");
				content = content.replace(/<(\w+)[^>]*style="[^"]*mso-[^"]*"[^>]*>/gi, "");
			}
			
			// Remove inline styles
			if (' . ( $this->is_sub_feature_enabled( 'remove_inline_styles', true ) ? 'true' : 'false' ) . ') {
				content = content.replace(/style="[^"]*"/gi, "");
			}
			
			// Remove classes
			if (' . ( $this->is_sub_feature_enabled( 'remove_classes', true ) ? 'true' : 'false' ) . ') {
				content = content.replace(/class="[^"]*"/gi, "");
			}
			
			args.content = content;
		}';

		return $init;
	}

	/**
	 * Server-side content cleanup on save.
	 *
	 * @param string $content Post content.
	 * @return string Cleaned content.
	 */
	public function cleanup_content_on_save( string $content ): string {
		if ( ! $this->is_enabled() ) {
			return $content;
		}

		// Remove Word-specific classes
		if ( $this->is_sub_feature_enabled( 'remove_word_metadata', true ) ) {
			$content = preg_replace( '/class=["\'][^"\']*Mso[^"\']*["\']/i', '', $content );
			$content = preg_replace( '/<o:p>.*?<\/o:p>/is', '', $content );
		}

		// Remove inline styles if configured
		if ( $this->is_sub_feature_enabled( 'remove_inline_styles', true ) ) {
			$content = preg_replace( '/style=["\'][^"\']*["\']/i', '', $content );
		}

		// Remove empty paragraphs
		if ( $this->is_sub_feature_enabled( 'remove_empty_tags', true ) ) {
			$content = preg_replace( '/<p[^>]*>(\s|&nbsp;)*<\/p>/i', '', $content );
		}

		do_action( 'wpshadow_paste_cleanup_cleaned', $content );

		return $content;
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, array<string, mixed>> $tests Site Health tests.
	 * @return array<string, array<string, mixed>>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_paste_cleanup'] = array(
			'label' => __( 'Paste Content Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'site_health_test' ),
		);

		return $tests;
	}

	/**
	 * Register WP-CLI commands for paste cleanup.
	 */
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

				/**
				 * Clean provided HTML content via CLI.
				 *
				 * ## OPTIONS
				 * --content=<content>
				 * : The HTML content to clean.
				 *
				 * ## EXAMPLES
				 * wp wpshadow paste-clean --content="<p class='Mso'>Test</p>"
				 */
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

	/**
	 * Site Health test callback.
	 *
	 * @return array<string, mixed>
	 */
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
