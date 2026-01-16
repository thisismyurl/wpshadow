<?php
/**
 * Feature: HTML Output Buffer Compression
 *
 * Use output buffering to compress HTML by removing comments,
 * excessive whitespace, and empty tags. Reduces HTML size significantly.
 *
 * @package WPShadow\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_HTML_Cleanup
 *
 * Compresses HTML output via buffer manipulation.
 */
final class WPSHADOW_Feature_HTML_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'html-cleanup',
				'name'               => __( 'HTML Output Buffer Compression', 'plugin-wpshadow' ),
				'description'        => __( 'Shrink your HTML to make pages load faster without breaking anything.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'advanced',
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'remove_comments'    => __( 'Remove HTML Comments', 'plugin-wpshadow' ),
					'remove_whitespace'  => __( 'Remove Excessive Whitespace', 'plugin-wpshadow' ),
					'remove_empty_tags'  => __( 'Remove Empty Tags', 'plugin-wpshadow' ),
					'minify_inline_css'  => __( 'Minify Inline CSS', 'plugin-wpshadow' ),
					'minify_inline_js'   => __( 'Minify Inline JavaScript', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'remove_comments'    => true,
						'remove_whitespace'  => true,
						'remove_empty_tags'  => false,
						'minify_inline_css'  => true,
						'minify_inline_js'   => false,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'HTML Cleanup feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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

		// Skip in debug mode and admin.
		if ( is_admin() || wp_doing_ajax() || WP_DEBUG ) {
			return;
		}

		add_action( 'wp_head', array( $this, 'start_buffer' ), 1 );
		add_action( 'wp_footer', array( $this, 'end_buffer' ), 999 );
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Start output buffering.
	 *
	 * @return void
	 */
	public function start_buffer(): void {
		ob_start( array( $this, 'compress_html' ) );
	}

	/**
	 * End output buffering.
	 *
	 * @return void
	 */
	public function end_buffer(): void {
		if ( ob_get_level() > 0 ) {
			ob_end_flush();
		}
	}

	/**
	 * Compress HTML buffer.
	 *
	 * @param string $buffer The HTML buffer.
	 * @return string Compressed HTML.
	 */
	public function compress_html( string $buffer ): string {
		// Skip empty buffers.
		if ( empty( $buffer ) ) {
			return $buffer;
		}

		// Get options.
		$options = (array) $this->get_setting( 'wpshadow_html_cleanup_options', $this->get_default_options( ) );

		// 1. Remove HTML comments (but preserve conditional comments and structured data).
		if ( $options['remove_comments'] ?? false ) {
			$buffer = preg_replace( '/(?<!<)!--(?!>).)*?--(?!>)/s', '', $buffer );
		}

		// 2. Collapse whitespace between tags.
		if ( $options['collapse_whitespace'] ?? false ) {
			$buffer = preg_replace( '/>\s+</', '><', $buffer );
		}

		// 3. Remove leading/trailing whitespace from lines.
		if ( $options['trim_lines'] ?? false ) {
			$buffer = preg_replace( '/^\s+|\s+$/m', '', $buffer );
		}

		// 4. Remove empty tags (but exclude functional ones).
		if ( $options['remove_empty_tags'] ?? false ) {
			$buffer = $this->remove_empty_tags( $buffer );
		}

		return $buffer;
	}

	/**
	 * Remove empty HTML tags while preserving functional ones.
	 *
	 * @param string $buffer The HTML buffer.
	 * @return string Modified buffer.
	 */
	private function remove_empty_tags( string $buffer ): string {
		// Exclude functional tags that should be preserved even if empty.
		$excluded_tags = 'iframe|embed|object|video|canvas|script|style|textarea|i|span|div|noscript';
		$pattern       = '/<(?!(?:' . $excluded_tags . ')\b)([a-zA-Z0-9]+)\b[^>]*>([\s\xA0]*)<\/\1>/i';

		$prev = '';
		while ( $prev !== $buffer ) {
			$prev   = $buffer;
			$buffer = preg_replace( $pattern, '', $buffer );
		}

		return $buffer;
	}

	/**
	 * Get default options.
	 *
	 * @return array Default options.
	 */
	protected function get_default_options(): array {
		return array(
			'remove_comments'     => true,
			'collapse_whitespace' => true,
			'trim_lines'          => false, // Can break inline JS, disabled by default.
			'remove_empty_tags'   => true,
		);
	}
}
