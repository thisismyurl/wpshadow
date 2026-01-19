<?php
/**
 * Feature: HTML Output Buffer Compression
 *
 * Use output buffering to compress HTML by removing comments,
 * excessive whitespace, and empty tags. Reduces HTML size significantly.
 *
 * @package WPShadow\CoreSupport
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
				'id'              => 'html-cleanup',
				'name'            => __( 'Shrink Page Code', 'wpshadow' ),
				'description'     => __( 'Remove extra spaces and comments from your page code to make it smaller and load faster.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => false,
				'version'         => '1.0.0',
				'widget_group'    => 'advanced',
				'aliases'         => array( 'clean code', 'remove scripts', 'cleanup html', 'minify html', 'compress html', 'optimize code', 'reduce html size', 'whitespace removal', 'html minification', 'code compression', 'html optimization', 'reduce page size' ),
				'sub_features'    => array(
					'remove_comments'    => __( 'Remove hidden notes in code', 'wpshadow' ),
					'remove_whitespace'  => __( 'Remove extra blank spaces', 'wpshadow' ),
					'remove_empty_tags'  => __( 'Remove empty code tags', 'wpshadow' ),
					'minify_inline_css'  => __( 'Shrink styling code', 'wpshadow' ),
					'minify_inline_js'   => __( 'Shrink interactive code', 'wpshadow' ),
				),
			)
		);

		$this->register_default_settings(
			array(
				'remove_comments'    => true,
				'remove_whitespace'  => true,
				'remove_empty_tags'  => false,
				'minify_inline_css'  => true,
				'minify_inline_js'   => false,
			)
		);

		$this->log_activity( 'feature_initialized', 'HTML Cleanup feature initialized', 'info' );
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
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow html-cleanup', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Start output buffering.
	 *
	 * @return void
	 */
	public function start_buffer(): void {
		ob_start( array( $this, 'compress_html' ) );
		do_action( 'wpshadow_html_cleanup_buffer_started' );
	}

	/**
	 * End output buffering.
	 *
	 * @return void
	 */
	public function end_buffer(): void {
		if ( ob_get_level() > 0 ) {
			ob_end_flush();
			do_action( 'wpshadow_html_cleanup_buffer_flushed' );
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

		// 1. Remove HTML comments.
		if ( $this->is_sub_feature_enabled( 'remove_comments', true ) ) {
			$buffer = preg_replace( '/(?<!<)!--[^-].)*?--(?!>)/s', '', $buffer );
		}

		// 2. Collapse whitespace between tags.
		if ( $this->is_sub_feature_enabled( 'remove_whitespace', true ) ) {
			$buffer = preg_replace( '/>\s+</', '><', $buffer );
		}

		// 3. Remove empty tags.
		if ( $this->is_sub_feature_enabled( 'remove_empty_tags', false ) ) {
			$buffer = $this->remove_empty_tags( $buffer );
		}

		// 4. Minify inline CSS.
		if ( $this->is_sub_feature_enabled( 'minify_inline_css', true ) ) {
			$buffer = $this->minify_inline_css( $buffer );
		}

		// 5. Minify inline JS (optional, can break code).
		if ( $this->is_sub_feature_enabled( 'minify_inline_js', false ) ) {
			$buffer = $this->minify_inline_js( $buffer );
		}

		do_action( 'wpshadow_html_cleanup_processed', $buffer );

		return $buffer;
	}

	/**
	 * Minify inline CSS.
	 *
	 * @param string $buffer The HTML buffer.
	 * @return string Modified buffer.
	 */
	private function minify_inline_css( string $buffer ): string {
		return preg_replace_callback(
			'/<style[^>]*>(.+?)<\/style>/is',
			static function ( $matches ) {
				$css = $matches[1];
				// Remove CSS comments.
				$css = preg_replace( '/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $css );
				// Remove whitespace.
				$css = preg_replace( '/\s+/', ' ', $css );
				// Remove spaces around certain characters.
				$css = preg_replace( '/\s*([{}:;,])\s*/', '$1', $css );
				// Remove last semicolon before }.
				$css = preg_replace( '/;}/','}',$css );
				return '<style' . substr( $matches[0], 6, strpos( $matches[0], '>' ) - 6 ) . '>' . trim( $css ) . '</style>';
			},
			$buffer
		);
	}

	/**
	 * Minify inline JavaScript (basic minification).
	 *
	 * @param string $buffer The HTML buffer.
	 * @return string Modified buffer.
	 */
	private function minify_inline_js( string $buffer ): string {
		return preg_replace_callback(
			'/<script([^>]*)>(.+?)<\/script>/is',
			static function ( $matches ) {
				// Skip if src attribute exists (external script).
				if ( str_contains( $matches[1], 'src=' ) ) {
					return $matches[0];
				}

				$js = $matches[2];
				// Remove single-line comments (be careful with URLs).
				$js = preg_replace( '/(?<!:)\/\/.*$/m', '', $js );
				// Remove multi-line comments.
				$js = preg_replace( '/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $js );
				// Remove excessive whitespace.
				$js = preg_replace( '/\s+/', ' ', $js );
				return '<script' . $matches[1] . '>' . trim( $js ) . '</script>';
			},
			$buffer
		);
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
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['html_cleanup'] = array(
			'label' => __( 'HTML Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_html_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for HTML cleanup.
	 *
	 * @return array Test result.
	 */
	public function test_html_cleanup(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'HTML Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'HTML cleanup is disabled.', 'wpshadow' ),
				'test'        => 'html_cleanup',
			);
		}

		$enabled_sub_features = 0;

		if ( $this->is_sub_feature_enabled( 'remove_comments', true ) ) {
			$enabled_sub_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_whitespace', true ) ) {
			$enabled_sub_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_empty_tags', false ) ) {
			$enabled_sub_features++;
		}

		$status = $enabled_sub_features > 0 ? 'good' : 'recommended';

		return array(
			'label'       => __( 'HTML Compression Active', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				__( '%d HTML compression features enabled.', 'wpshadow' ),
				(int) $enabled_sub_features
			),
			'test'        => 'html_cleanup',
		);
	}

	/**
	 * Handle WP-CLI command for HTML cleanup.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args (unused).
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow html-cleanup status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'HTML Cleanup status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'remove_comments',
			'remove_whitespace',
			'remove_empty_tags',
			'minify_inline_css',
			'minify_inline_js',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'HTML cleanup inspected.', 'wpshadow' ) );
	}
}
