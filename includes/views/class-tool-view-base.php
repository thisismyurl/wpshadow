<?php
/**
 * Tool View Base Class
 *
 * Abstract base class for tool view templates providing common functionality
 * for rendering headers, footers, asset enqueuing, and security nonces.
 *
 * @package WPShadow\Views
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tool_View_Base Class
 *
 * Provides shared functionality for tool view templates to reduce duplication.
 * Common patterns: asset enqueuing, nonce generation, header/footer markup.
 *
 * Usage in tool views:
 *   require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';
 *   Tool_View_Base::render_header( __( 'Tool Title', 'wpshadow' ) );
 *   // ... tool-specific content ...
 *   Tool_View_Base::render_footer();
 *
 * @since 1.2601.2148
 */
class Tool_View_Base {

	/**
	 * Enqueue tool-specific scripts and styles
	 *
	 * Common assets used by most tools. Can be overridden per tool if needed.
	 *
	 * @since 1.2601.2148
	 * @param string $tool_name Tool slug (e.g., 'quick-scan', 'color-contrast').
	 * @return void
	 */
	public static function enqueue_assets( string $tool_name ): void {
		// Enqueue tool-specific stylesheet if exists
		$css_file = WPSHADOW_URL . 'assets/css/tool-' . sanitize_file_name( $tool_name ) . '.css';
		if ( wp_remote_head( $css_file )['response']['code'] === 200 ) {
			wp_enqueue_style(
				'wpshadow-tool-' . $tool_name,
				$css_file,
				array(),
				WPSHADOW_VERSION
			);
		}

		// Enqueue tool-specific script if exists
		$js_file = WPSHADOW_URL . 'assets/js/tool-' . sanitize_file_name( $tool_name ) . '.js';
		if ( wp_remote_head( $js_file )['response']['code'] === 200 ) {
			wp_enqueue_script(
				'wpshadow-tool-' . $tool_name,
				$js_file,
				array( 'jquery' ),
				WPSHADOW_VERSION,
				true
			);
		}

		// Enqueue common tool styles and scripts
		wp_enqueue_style( 'wpshadow-tools-common', WPSHADOW_URL . 'assets/css/tools-common.css', array(), WPSHADOW_VERSION );
		wp_enqueue_script( 'wpshadow-tools-common', WPSHADOW_URL . 'assets/js/tools-common.js', array( 'jquery' ), WPSHADOW_VERSION, true );

		// Localize AJAX URL
		wp_localize_script(
			'wpshadow-tools-common',
			'wpshadowTools',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Render tool header (opening wrapper and title)
	 *
	 * Standard header markup with title, version tag, and description.
	 *
	 * @since  1.2601.2148
	 * @param  string $title       Tool title to display.
	 * @param  string $description Optional. Tool description/subtitle.
	 * @return void
	 */
	public static function render_header( string $title, string $description = '' ): void {
		?>
		<div class="wrap wpshadow-tool-container">
			<h1><?php echo esc_html( $title ); ?></h1>
			<p class="wps-version-tag">v<?php echo esc_html( WPSHADOW_VERSION ); ?></p>
			<?php
			if ( ! empty( $description ) ) {
				?>
				<p><?php echo esc_html( $description ); ?></p>
				<?php
			}
			?>
		<?php
	}

	/**
	 * Render tool footer (closing wrapper)
	 *
	 * Closes the main tool container div opened by render_header().
	 *
	 * @since 1.2601.2148
	 * @return void
	 */
	public static function render_footer(): void {
		?>
		</div><!-- .wrap.wpshadow-tool-container -->
		<?php
	}

	/**
	 * Render a card panel with optional title
	 *
	 * Common card/panel structure used in tool UIs for grouping content.
	 *
	 * @since  1.2601.2148
	 * @param  string $title       Optional. Card title.
	 * @param  string $class       Optional. Additional CSS classes for the card.
	 * @param  string $open_or_close 'open' to render opening tag, 'close' to render closing tag.
	 * @return void
	 */
	public static function render_card( string $title = '', string $class = '', string $open_or_close = 'open' ): void {
		if ( 'open' === $open_or_close ) {
			$classes = 'wpshadow-card ' . $class;
			?>
			<div class="<?php echo esc_attr( $classes ); ?>">
				<?php
				if ( ! empty( $title ) ) {
					?>
					<h3><?php echo esc_html( $title ); ?></h3>
					<?php
				}
		} else {
			?>
			</div><!-- .wpshadow-card -->
			<?php
		}
	}

	/**
	 * Generate security nonce for AJAX requests
	 *
	 * Creates a nonce for use in tool-specific AJAX handlers.
	 *
	 * @since  1.2601.2148
	 * @param  string $action Nonce action name (e.g., 'wpshadow_scan_nonce').
	 * @return string Generated nonce.
	 */
	public static function get_nonce( string $action ): string {
		return wp_create_nonce( $action );
	}

	/**
	 * Render a form group with label and input
	 *
	 * Common form field markup pattern used across tools.
	 *
	 * @since  1.2601.2148
	 * @param  string $label       Input label text.
	 * @param  string $name        Input name attribute.
	 * @param  string $type        Input type (text, email, number, etc).
	 * @param  string $placeholder Optional. Placeholder text.
	 * @param  string $help_text   Optional. Help text below input.
	 * @param  bool   $required    Optional. Whether field is required.
	 * @return void
	 */
	public static function render_form_group( string $label, string $name, string $type = 'text', string $placeholder = '', string $help_text = '', bool $required = false ): void {
		$id = 'wpshadow-' . sanitize_key( $name );
		?>
		<div class="wps-form-group">
			<label class="wps-label" for="<?php echo esc_attr( $id ); ?>">
				<?php echo esc_html( $label ); ?>
				<?php if ( $required ) { ?>
					<span class="required" aria-label="<?php esc_attr_e( 'required', 'wpshadow' ); ?>">*</span>
				<?php } ?>
			</label>
			<input 
				type="<?php echo esc_attr( $type ); ?>" 
				id="<?php echo esc_attr( $id ); ?>" 
				name="<?php echo esc_attr( $name ); ?>" 
				class="regular-text" 
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				<?php if ( $required ) { echo 'required'; } ?>
			/>
			<?php
			if ( ! empty( $help_text ) ) {
				?>
				<span class="wps-help-text" id="<?php echo esc_attr( $id ); ?>-help">
					<?php echo esc_html( $help_text ); ?>
				</span>
				<?php
			}
			?>
		</div><!-- .wps-form-group -->
		<?php
	}

	/**
	 * Verify current user can access tools
	 *
	 * Checks capability and displays error if insufficient permissions.
	 * Call this at the start of tool view templates.
	 *
	 * @since  1.2601.2148
	 * @param  string $capability Optional. Capability to check. Default 'read'.
	 * @return bool True if user has capability, false otherwise.
	 */
	public static function verify_access( string $capability = 'read' ): bool {
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
		}
		return true;
	}
}
