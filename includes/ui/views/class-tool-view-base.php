<?php
/**
 * Tool View Base Class
 *
 * Abstract base class for tool view templates providing common functionality
 * for rendering headers, footers, asset enqueuing, and security nonces.
 *
 * @package WPShadow\Views
 * @since 0.6093.1200
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
 * @since 0.6093.1200
 */
class Tool_View_Base {

	/**
	 * Enqueue tool-specific scripts and styles
	 *
	 * Common assets used by most tools. Can be overridden per tool if needed.
	 *
	 * @since 0.6093.1200
	 * @param string $tool_name Tool slug (e.g., 'deep-scan', 'color-contrast').
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

		// Enqueue common tool styles and scripts.
		wp_enqueue_style( 'wpshadow-tools-common', WPSHADOW_URL . 'assets/css/utilities-consolidated.css', array(), WPSHADOW_VERSION );
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
	 * @since 0.6093.1200
	 * @param  string $title       Tool title to display.
	 * @param  string $description Optional. Tool description/subtitle.
	 * @return void
	 */
	public static function render_header( string $title, string $description = '' ): void {
		?>
		<div class="wrap wps-page-container">
			<?php wpshadow_render_page_header( $title, $description ); ?>
		<?php
	}

	/**
	 * Render tool footer (closing wrapper)
	 *
	 * Closes the main tool container div opened by render_header().
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_footer(): void {
		if ( function_exists( 'wpshadow_render_page_activities' ) ) {
			wpshadow_render_page_activities( 'tools', 10 );
		}
		?>
		</div><!-- .wrap.wps-page-container -->
		<?php
	}

	/**
	 * Render a card panel with optional title
	 *
	 * Common card/panel structure used in tool UIs for grouping content.
	 *
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @param  string $capability Optional. Capability to check. Default 'read'.
	 * @return bool True if user has capability, false otherwise.
	 */
	public static function verify_access( string $capability = 'read' ): bool {
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
		}
		return true;
	}

	/**
	 * Render cloud registration required notice card.
	 *
	 * Displays a consistent warning card with cloud registration call-to-action.
	 *
	 * @since 0.6093.1200
	 * @param  string $description Description explaining why cloud access is required.
	 * @param  string $free_tier   Optional. Free tier text.
	 * @return void
	 */
	public static function render_cloud_registration_required_notice( string $description, string $free_tier = '' ): void {
		?>
		<div class="wps-card wps-card--warning">
			<div class="wps-card-body">
				<h3><?php esc_html_e( '🌐 Cloud Service Required', 'wpshadow' ); ?></h3>
				<p><?php echo esc_html( $description ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="wps-btn wps-btn--primary">
					<span class="dashicons dashicons-cloud"></span>
					<?php esc_html_e( 'Register for Free Cloud Access', 'wpshadow' ); ?>
				</a>
				<?php if ( '' !== $free_tier ) : ?>
					<p class="wps-help-text" style="margin-top: 15px;">
						<strong><?php esc_html_e( 'Free Tier:', 'wpshadow' ); ?></strong>
						<?php echo esc_html( $free_tier ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render cloud request error notice card.
	 *
	 * Displays a consistent error card for cloud API request failures.
	 *
	 * @since 0.6093.1200
	 * @param  string $message Error message to display.
	 * @return void
	 */
	public static function render_cloud_request_error_notice( string $message ): void {
		?>
		<div class="wps-card wps-card--error">
			<div class="wps-card-body">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render external servers info card.
	 *
	 * Displays a consistent informational card explaining why a feature runs
	 * on external infrastructure.
	 *
	 * @since 0.6093.1200
	 * @param  array  $reasons List of reason strings.
	 * @param  string $title   Optional. Card title.
	 * @return void
	 */
	public static function render_external_servers_info_card( array $reasons, string $title = '' ): void {
		if ( '' === $title ) {
			$title = __( 'Why This Runs on External Servers', 'wpshadow' );
		}

		?>
		<div class="wps-card wps-mt-6 wps-card--info">
			<div class="wps-card-body">
				<h3><?php echo esc_html( $title ); ?></h3>
				<ul style="list-style: disc; margin-left: 20px;">
					<?php foreach ( $reasons as $reason ) : ?>
						<li><?php echo esc_html( $reason ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Render cloud status summary card.
	 *
	 * Displays a standard card with title, status line, and optional details.
	 *
	 * @since 0.6093.1200
	 * @param  string $title        Card title.
	 * @param  string $status_text  Status value text.
	 * @param  string $details      Optional. Additional details.
	 * @param  string $status_label Optional. Label before status text.
	 * @return void
	 */
	public static function render_cloud_status_summary_card( string $title, string $status_text, string $details = '', string $status_label = '' ): void {
		if ( '' === $status_label ) {
			$status_label = __( 'Status:', 'wpshadow' );
		}

		?>
		<div class="wps-card">
			<div class="wps-card-header">
				<h3 class="wps-card-title"><?php echo esc_html( $title ); ?></h3>
			</div>
			<div class="wps-card-body">
				<p><strong><?php echo esc_html( $status_label ); ?></strong> <?php echo esc_html( $status_text ); ?></p>
				<?php if ( ! empty( $details ) ) : ?>
					<p class="wps-help-text"><?php echo esc_html( $details ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get JavaScript-safe opening HTML for report result card body.
	 *
	 * Used by report templates that assemble result markup in JavaScript.
	 *
	 * @since 0.6093.1200
	 * @return string Opening card/body HTML fragment.
	 */
	public static function get_js_result_card_open_html(): string {
		return '<div class="wps-card"><div class="wps-card-body">';
	}

	/**
	 * Get JavaScript-safe closing HTML for report result card body.
	 *
	 * Used by report templates that assemble result markup in JavaScript.
	 *
	 * @since 0.6093.1200
	 * @return string Closing card/body HTML fragment.
	 */
	public static function get_js_result_card_close_html(): string {
		return '</div></div>';
	}

	/**
	 * Get JavaScript-safe success notice card HTML.
	 *
	 * @since 0.6093.1200
	 * @param  string $message Success message text.
	 * @return string Success notice card markup.
	 */
	public static function get_js_success_notice_html( string $message ): string {
		return sprintf(
			'<div class="notice notice-success wps-card"><p><span class="dashicons dashicons-yes-alt"></span> %s</p></div>',
			esc_html( $message )
		);
	}

	/**
	 * Get JavaScript-safe opening HTML for a generic error notice.
	 *
	 * @since 0.6093.1200
	 * @return string Opening error notice HTML fragment.
	 */
	public static function get_js_error_notice_open_html(): string {
		return '<div class="notice notice-error"><p>';
	}

	/**
	 * Get JavaScript-safe closing HTML for a generic error notice.
	 *
	 * @since 0.6093.1200
	 * @return string Closing error notice HTML fragment.
	 */
	public static function get_js_error_notice_close_html(): string {
		return '</p></div>';
	}

	/**
	 * Get JavaScript helper functions for report scan lifecycle state.
	 *
	 * Includes functions to toggle loading/disabled states consistently
	 * while scans are running.
	 *
	 * @since 0.6093.1200
	 * @return string JavaScript function definitions.
	 */
	public static function get_js_scan_state_helpers(): string {
		return "function wpshadowReportScanStart( \$btn, \$progress, \$results ) {\n"
			. "\t\$btn.prop('disabled', true).addClass('wps-loading');\n"
			. "\t\$progress.removeClass('hidden');\n"
			. "\t\$results.empty();\n"
			. "}\n"
			. "\n"
			. "function wpshadowReportScanEnd( \$btn, \$progress ) {\n"
			. "\t\$btn.prop('disabled', false).removeClass('wps-loading');\n"
			. "\t\$progress.addClass('hidden');\n"
			. "}\n"
			. "\n"
			. "function wpshadowRunFamilyDiagnostics( family, nonce ) {\n"
			. "\treturn wp.ajax.post('wpshadow_run_family_diagnostics', {\n"
			. "\t\tfamily: family,\n"
			. "\t\tnonce: nonce\n"
			. "\t});\n"
			. "}\n"
			. "\n"
			. "function wpshadowRenderAutoFixButton( finding, label, classes ) {\n"
			. "\tif ( ! finding || ! finding.auto_fixable ) {\n"
			. "\t\treturn '';\n"
			. "\t}\n"
			. "\tconst buttonClass = classes || 'wps-btn wps-btn-sm wps-btn-success wps-mt-2';\n"
			. "\treturn '<button class=\"' + buttonClass + '\" data-finding=\"' + finding.id + '\">' + label + '</button>';\n"
			. "}\n"
			. "\n"
			. "function wpshadowRenderFindingCardStart( finding, options ) {\n"
			. "\tconst config = options || {};\n"
			. "\tconst severityClass = config.severityClass || 'info';\n"
			. "\tconst containerClass = config.containerClass || ('wps-mb-3 wps-p-3 wps-border wps-border-' + severityClass + ' wps-rounded');\n"
			. "\tconst iconClass = config.iconClass || 'dashicons-info';\n"
			. "\tconst titleTag = config.titleTag || 'h5';\n"
			. "\tconst titleClass = config.titleClass || 'wps-font-semibold';\n"
			. "\tconst descriptionClass = config.descriptionClass || 'wps-text-muted wps-text-sm';\n"
			. "\tlet html = '';\n"
			. "\thtml += '<div class=\"' + containerClass + '\">';\n"
			. "\thtml += '<div class=\"wps-flex wps-items-start wps-gap-3\">';\n"
			. "\thtml += '<span class=\"dashicons ' + iconClass + ' wps-text-' + severityClass + '\"></span>';\n"
			. "\thtml += '<div class=\"wps-flex-1\">';\n"
			. "\thtml += '<' + titleTag + ' class=\"' + titleClass + '\">' + finding.title + '</' + titleTag + '>';\n"
			. "\thtml += '<p class=\"' + descriptionClass + '\">' + finding.description + '</p>';\n"
			. "\treturn html;\n"
			. "}\n"
			. "\n"
			. "function wpshadowRenderFindingCardEnd() {\n"
			. "\treturn '</div></div></div>';\n"
			. "}\n"
			. "\n"
			. "function wpshadowRenderSectionHeading( title, count, options ) {\n"
			. "\tconst config = options || {};\n"
			. "\tconst headingTag = config.headingTag || 'h4';\n"
			. "\tconst headingClass = config.headingClass || 'wps-font-semibold wps-mb-2';\n"
			. "\tconst suffix = config.countSuffix ? (' ' + config.countSuffix) : '';\n"
			. "\treturn '<' + headingTag + ' class=\"' + headingClass + '\">' + title + ' (' + count + suffix + ')</' + headingTag + '>';\n"
			. "}\n"
			. "\n"
			. "function wpshadowRenderSummaryHeading( title, count, options ) {\n"
			. "\tconst config = options || {};\n"
			. "\tconst headingClass = config.headingClass || 'wps-text-lg wps-mb-3';\n"
			. "\treturn '<h3 class=\"' + headingClass + '\">' + title + ' (' + count + ')</h3>';\n"
			. "}\n";
	}
}
