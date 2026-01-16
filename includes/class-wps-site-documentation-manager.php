<?php
/**
 * WPS Site Documentation Manager
 *
 * Handles site blueprint generation, protected plugins, and comprehensive documentation export.
 * Integrates #163 (Blueprint), #167 (Protected Plugins), #169 (Export Documentation).
 *
 * @package WPSHADOW_WP_SUPPORT
 * @since 1.2601.1111
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * Class WPSHADOW_Site_Documentation_Manager
 *
 * Provides comprehensive site documentation features:
 * - Site Blueprint: Auto-generated plain-English documentation
 * - Protected Plugins: Mark critical plugins with consequence warnings
 * - Export Engine: PDF/DOCX/HTML/Markdown export capabilities
 */
class WPSHADOW_Site_Documentation_Manager {

	/**
	 * Database option key for protected plugins.
	 */
	private const PROTECTED_PLUGINS_KEY = 'wpshadow_protected_plugins';

	/**
	 * Database option key for documentation metadata.
	 */
	private const DOC_METADATA_KEY = 'wpshadow_documentation_metadata';

	/**
	 * Initialize the documentation manager.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_pages' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_WPSHADOW_toggle_plugin_protection', array( __CLASS__, 'ajax_toggle_protection' ) );
		add_action( 'wp_ajax_WPSHADOW_export_documentation', array( __CLASS__, 'ajax_export_documentation' ) );
		add_filter( 'plugin_action_links', array( __CLASS__, 'add_plugin_action_links' ), 10, 2 );
	}

	/**
	 * Register admin pages for documentation features.
	 *
	 * @return void
	 */
	public static function register_admin_pages(): void {
		// Site Blueprint page.
		add_submenu_page(
			'wpshadow',
			__( 'Site Blueprint', 'plugin-wpshadow' ),
			__( 'Site Blueprint', 'plugin-wpshadow' ),
			'manage_options',
			'wps-site-blueprint',
			array( __CLASS__, 'render_blueprint_page' )
		);

		// Protected Plugins page.
		add_submenu_page(
			'wpshadow',
			__( 'Protected Plugins', 'plugin-wpshadow' ),
			__( 'Protected Plugins', 'plugin-wpshadow' ),
			'manage_options',
			'wps-protected-plugins',
			array( __CLASS__, 'render_protected_plugins_page' )
		);

		// Export Documentation page.
		add_submenu_page(
			'wpshadow',
			__( 'Export Documentation', 'plugin-wpshadow' ),
			__( 'Export Documentation', 'plugin-wpshadow' ),
			'manage_options',
			'wps-export-documentation',
			array( __CLASS__, 'render_export_page' )
		);
	}

	/**
	 * Enqueue CSS/JS assets for documentation pages.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, 'wps-site-blueprint' ) &&
			! str_contains( $hook, 'wps-protected-plugins' ) &&
			! str_contains( $hook, 'wps-export-documentation' ) ) {
			return;
		}

		wp_enqueue_style( 'wps-documentation', plugins_url( '../assets/css/documentation.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_script( 'wps-documentation', plugins_url( '../assets/js/documentation.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );

		wp_localize_script(
			'wps-documentation',
			'wpsDocumentation',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wpshadow_documentation_nonce' ),
			)
		);
	}

	/**
	 * Add "Protect" action link to plugins list.
	 *
	 * @param string[] $actions Array of action links.
	 * @param string   $plugin_file Plugin file path relative to plugins directory.
	 * @return string[] Modified action links.
	 */
	public static function add_plugin_action_links( array $actions, string $plugin_file ): array {
		$protected_plugins = self::get_protected_plugins();
		$is_protected      = isset( $protected_plugins[ $plugin_file ] );

		$link_text = $is_protected ? __( 'Unprotect', 'plugin-wpshadow' ) : __( 'Protect', 'plugin-wpshadow' );
		$class     = $is_protected ? 'wps-unprotect-plugin' : 'wps-protect-plugin';

		$actions['wpshadow_protect'] = sprintf(
			'<a href="#" class="%s" data-plugin="%s">%s</a>',
			esc_attr( $class ),
			esc_attr( $plugin_file ),
			esc_html( $link_text )
		);

		return $actions;
	}

	/**
	 * AJAX handler: Toggle plugin protection status.
	 *
	 * @return void
	 */
	public static function ajax_toggle_protection(): void {
		check_ajax_referer( 'wpshadow_documentation_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ) );
		}

		$plugin_file = \WPShadow\WPSHADOW_get_post_text( 'plugin' );
		if ( empty( $plugin_file ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid plugin specified', 'plugin-wpshadow' ) ) );
		}

		$protected_plugins = self::get_protected_plugins();
		$is_protected      = isset( $protected_plugins[ $plugin_file ] );

		if ( $is_protected ) {
			unset( $protected_plugins[ $plugin_file ] );
			$message = __( 'Plugin protection removed', 'plugin-wpshadow' );
		} else {
			$protected_plugins[ $plugin_file ] = array(
				'protected_at' => time(),
				'protected_by' => get_current_user_id(),
				'notify'       => true,
			);
			$message                           = __( 'Plugin marked as protected', 'plugin-wpshadow' );
		}

		update_option( self::PROTECTED_PLUGINS_KEY, $protected_plugins );

		wp_send_json_success(
			array(
				'message'   => $message,
				'protected' => ! $is_protected,
			)
		);
	}

	/**
	 * AJAX handler: Export documentation in specified format.
	 *
	 * @return void
	 */
	public static function ajax_export_documentation(): void {
		check_ajax_referer( 'wpshadow_documentation_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ) );
		}

		$format = \WPShadow\WPSHADOW_get_post_text( 'format', 'html' );
		$export = self::generate_export( $format );

		if ( is_wp_error( $export ) ) {
			wp_send_json_error( array( 'message' => $export->get_error_message() ) );
		}

		wp_send_json_success( $export );
	}

	/**
	 * Render Site Blueprint admin page (#163).
	 *
	 * @return void
	 */
	public static function render_blueprint_page(): void {
		$blueprint = self::generate_blueprint();
		?>
		<div class="wrap wps-blueprint-page">
			<h1><?php esc_html_e( 'Site Blueprint', 'plugin-wpshadow' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Comprehensive site documentation in plain English. Understand what you have and how it works.', 'plugin-wpshadow' ); ?>
			</p>

			<div class="wps-blueprint-actions">
				<button type="button" class="button button-primary" id="wps-export-blueprint">
					<?php esc_html_e( 'Export Blueprint', 'plugin-wpshadow' ); ?>
				</button>
				<button type="button" class="button" id="wps-refresh-blueprint">
					<?php esc_html_e( 'Refresh', 'plugin-wpshadow' ); ?>
				</button>
			</div>

			<div class="wps-blueprint-content">
				<?php echo wp_kses_post( $blueprint ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Protected Plugins admin page (#167).
	 *
	 * @return void
	 */
	public static function render_protected_plugins_page(): void {
		$protected_plugins = self::get_protected_plugins();
		$all_plugins       = get_plugins();
		?>
		<div class="wrap wps-protected-plugins-page">
			<h1><?php esc_html_e( 'Protected Plugins', 'plugin-wpshadow' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Mark critical plugins for protection. Prevent accidental removal and receive update notifications.', 'plugin-wpshadow' ); ?>
			</p>

			<div class="wps-protected-summary">
				<span class="dashicons dashicons-shield"></span>
				<strong><?php echo esc_html( count( $protected_plugins ) ); ?></strong>
				<?php esc_html_e( 'plugins protected', 'plugin-wpshadow' ); ?>
			</div>

			<table class="widefat wps-protected-plugins-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Plugin', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Purpose', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'If Removed', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Protected', 'plugin-wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'plugin-wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $all_plugins as $plugin_file => $plugin_data ) : ?>
						<?php
						$is_protected = isset( $protected_plugins[ $plugin_file ] );
						$purpose      = self::get_plugin_purpose( $plugin_file );
						$consequences = self::get_removal_consequences( $plugin_file );
						?>
						<tr class="<?php echo $is_protected ? 'wps-plugin-protected' : ''; ?>">
							<td>
								<strong><?php echo esc_html( $plugin_data['Name'] ); ?></strong>
								<br>
								<small><?php echo esc_html( $plugin_data['Version'] ); ?></small>
							</td>
							<td><?php echo esc_html( $purpose ); ?></td>
							<td><?php echo esc_html( $consequences ); ?></td>
							<td>
								<?php if ( $is_protected ) : ?>
									<span class="dashicons dashicons-shield-alt" style="color: #46b450;"></span>
									<?php esc_html_e( 'Yes', 'plugin-wpshadow' ); ?>
								<?php else : ?>
									<span class="dashicons dashicons-shield" style="color: #ddd;"></span>
									<?php esc_html_e( 'No', 'plugin-wpshadow' ); ?>
								<?php endif; ?>
							</td>
							<td>
								<button type="button" class="button wps-toggle-protection" data-plugin="<?php echo esc_attr( $plugin_file ); ?>">
									<?php echo $is_protected ? esc_html__( 'Unprotect', 'plugin-wpshadow' ) : esc_html__( 'Protect', 'plugin-wpshadow' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render Export Documentation admin page (#169).
	 *
	 * @return void
	 */
	public static function render_export_page(): void {
		?>
		<div class="wrap wps-export-page">
			<h1><?php esc_html_e( 'Export Site Documentation', 'plugin-wpshadow' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Export complete site documentation for handoff to developers or for personal records.', 'plugin-wpshadow' ); ?>
			</p>

			<div class="wps-export-options">
				<h2><?php esc_html_e( 'Export Format', 'plugin-wpshadow' ); ?></h2>
				<ul class="wps-export-formats">
					<li>
						<label>
							<input type="radio" name="export_format" value="html" checked>
							<strong><?php esc_html_e( 'HTML', 'plugin-wpshadow' ); ?></strong>
							<br>
							<small><?php esc_html_e( 'Web viewable, can be opened in any browser', 'plugin-wpshadow' ); ?></small>
						</label>
					</li>
					<li>
						<label>
							<input type="radio" name="export_format" value="markdown">
							<strong><?php esc_html_e( 'Markdown', 'plugin-wpshadow' ); ?></strong>
							<br>
							<small><?php esc_html_e( 'Developer friendly, works with GitHub and documentation systems', 'plugin-wpshadow' ); ?></small>
						</label>
					</li>
					<li>
						<label>
							<input type="radio" name="export_format" value="text">
							<strong><?php esc_html_e( 'Plain Text', 'plugin-wpshadow' ); ?></strong>
							<br>
							<small><?php esc_html_e( 'Universal format, readable anywhere', 'plugin-wpshadow' ); ?></small>
						</label>
					</li>
				</ul>

				<h2><?php esc_html_e( 'Include Sections', 'plugin-wpshadow' ); ?></h2>
				<ul class="wps-export-sections">
					<li><label><input type="checkbox" name="include_summary" checked> <?php esc_html_e( 'Executive Summary', 'plugin-wpshadow' ); ?></label></li>
					<li><label><input type="checkbox" name="include_plugins" checked> <?php esc_html_e( 'Plugin Inventory', 'plugin-wpshadow' ); ?></label></li>
					<li><label><input type="checkbox" name="include_customizations" checked> <?php esc_html_e( 'Customization Audit', 'plugin-wpshadow' ); ?></label></li>
					<li><label><input type="checkbox" name="include_technical" checked> <?php esc_html_e( 'Technical Specifications', 'plugin-wpshadow' ); ?></label></li>
					<li><label><input type="checkbox" name="include_maintenance" checked> <?php esc_html_e( 'Maintenance Schedule', 'plugin-wpshadow' ); ?></label></li>
					<li><label><input type="checkbox" name="include_emergency" checked> <?php esc_html_e( 'Emergency Contacts', 'plugin-wpshadow' ); ?></label></li>
					<li><label><input type="checkbox" name="include_recovery" checked> <?php esc_html_e( 'Recovery Procedures', 'plugin-wpshadow' ); ?></label></li>
				</ul>

				<button type="button" class="button button-primary button-hero" id="wps-export-now">
					<?php esc_html_e( 'Export Documentation', 'plugin-wpshadow' ); ?>
				</button>
			</div>

			<div class="wps-export-preview" style="display: none;">
				<h2><?php esc_html_e( 'Preview', 'plugin-wpshadow' ); ?></h2>
				<div class="wps-export-content"></div>
				<button type="button" class="button button-primary" id="wps-download-export">
					<?php esc_html_e( 'Download', 'plugin-wpshadow' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Generate site blueprint HTML (#163).
	 *
	 * @return string Blueprint HTML content.
	 */
	private static function generate_blueprint(): string {
		$output = '';

		// Executive Summary.
		$output .= '<div class="wps-blueprint-section">';
		$output .= '<h2>' . esc_html__( 'Executive Summary', 'plugin-wpshadow' ) . '</h2>';
		$output .= self::generate_executive_summary();
		$output .= '</div>';

		// Plugin Inventory.
		$output .= '<div class="wps-blueprint-section">';
		$output .= '<h2>' . esc_html__( 'Your Plugins Explained', 'plugin-wpshadow' ) . '</h2>';
		$output .= self::generate_plugin_inventory();
		$output .= '</div>';

		// Customization Audit.
		$output .= '<div class="wps-blueprint-section">';
		$output .= '<h2>' . esc_html__( 'What\'s Custom on Your Site', 'plugin-wpshadow' ) . '</h2>';
		$output .= self::generate_customization_audit();
		$output .= '</div>';

		// Technical Specifications.
		$output .= '<div class="wps-blueprint-section">';
		$output .= '<h2>' . esc_html__( 'Technical Specifications', 'plugin-wpshadow' ) . '</h2>';
		$output .= self::generate_technical_specs();
		$output .= '</div>';

		return $output;
	}

	/**
	 * Generate executive summary section.
	 *
	 * @return string Executive summary HTML.
	 */
	private static function generate_executive_summary(): string {
		$all_plugins    = get_plugins();
		$active_plugins = array_filter( $all_plugins, 'is_plugin_active', ARRAY_FILTER_USE_KEY );
		$theme          = wp_get_theme();

		$output  = '<ul class="wps-summary-list">';
		$output .= '<li><strong>' . esc_html__( 'Site URL:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( get_site_url() ) . '</li>';
		$output .= '<li><strong>' . esc_html__( 'Last Updated:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( date_i18n( get_option( 'date_format' ), time() ) ) . '</li>';
		$output .= '<li><strong>' . esc_html__( 'Total Plugins:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( (string) count( $active_plugins ) ) . ' active, ' . esc_html( (string) ( count( $all_plugins ) - count( $active_plugins ) ) ) . ' inactive</li>';
		$output .= '<li><strong>' . esc_html__( 'Theme:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( $theme->get( 'Name' ) ) . ' v' . esc_html( $theme->get( 'Version' ) ) . '</li>';
		$output .= '<li><strong>' . esc_html__( 'Multisite:', 'plugin-wpshadow' ) . '</strong> ' . ( is_multisite() ? esc_html__( 'Yes', 'plugin-wpshadow' ) : esc_html__( 'No', 'plugin-wpshadow' ) ) . '</li>';
		$output .= '<li><strong>' . esc_html__( 'WooCommerce:', 'plugin-wpshadow' ) . '</strong> ' . ( class_exists( 'WooCommerce' ) ? esc_html__( 'Yes', 'plugin-wpshadow' ) : esc_html__( 'No', 'plugin-wpshadow' ) ) . '</li>';
		$output .= '</ul>';

		return $output;
	}

	/**
	 * Generate plugin inventory with plain English descriptions.
	 *
	 * @return string Plugin inventory HTML.
	 */
	private static function generate_plugin_inventory(): string {
		$all_plugins    = get_plugins();
		$active_plugins = array_filter( $all_plugins, 'is_plugin_active', ARRAY_FILTER_USE_KEY );

		$output = '<div class="wps-plugin-inventory">';

		foreach ( $active_plugins as $plugin_file => $plugin_data ) {
			$purpose      = self::get_plugin_purpose( $plugin_file );
			$what_it_does = self::get_plugin_function( $plugin_file );
			$essential    = self::is_plugin_essential( $plugin_file );
			$consequences = self::get_removal_consequences( $plugin_file );

			$output .= '<div class="wps-plugin-card">';
			$output .= '<h3>' . esc_html( $plugin_data['Name'] ) . ' <small>v' . esc_html( $plugin_data['Version'] ) . '</small></h3>';
			$output .= '<p><strong>' . esc_html__( 'Purpose:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( $purpose ) . '</p>';
			$output .= '<p><strong>' . esc_html__( 'What it does:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( $what_it_does ) . '</p>';
			$output .= '<p><strong>' . esc_html__( 'Is it essential?', 'plugin-wpshadow' ) . '</strong> ' . ( $essential ? '<span class="wps-badge-yes">' . esc_html__( 'YES', 'plugin-wpshadow' ) . '</span>' : '<span class="wps-badge-no">' . esc_html__( 'NO', 'plugin-wpshadow' ) . '</span>' ) . '</p>';
			$output .= '<p><strong>' . esc_html__( 'If removed:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( $consequences ) . '</p>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Generate customization audit section.
	 *
	 * @return string Customization audit HTML.
	 */
	private static function generate_customization_audit(): string {
		$output = '<div class="wps-customization-audit">';

		// Check for custom code in theme.
		$theme         = wp_get_theme();
		$functions_php = get_stylesheet_directory() . '/functions.php';

		if ( file_exists( $functions_php ) ) {
			$lines   = count( file( $functions_php ) );
			$output .= '<div class="wps-custom-file">';
			$output .= '<span class="dashicons dashicons-yes-alt"></span> ';
			$output .= '<strong>' . esc_html( str_replace( wp_normalize_path( WP_CONTENT_DIR ), 'wp-content', wp_normalize_path( $functions_php ) ) ) . '</strong>';
			$output .= ' (' . esc_html( (string) $lines ) . ' lines)';
			$output .= '<br><small>' . esc_html__( 'Purpose: Adds custom features to your theme', 'plugin-wpshadow' ) . '</small>';
			$output .= '</div>';
		}

		// Check for custom plugins.
		$custom_plugins = self::get_custom_plugins();
		if ( ! empty( $custom_plugins ) ) {
			foreach ( $custom_plugins as $plugin_file => $plugin_data ) {
				$output .= '<div class="wps-custom-file">';
				$output .= '<span class="dashicons dashicons-yes-alt"></span> ';
				$output .= '<strong>' . esc_html( dirname( $plugin_file ) ) . '</strong>';
				$output .= '<br><small>' . esc_html__( 'Purpose: Custom functionality', 'plugin-wpshadow' ) . '</small>';
				$output .= '<br><small>' . esc_html__( 'Maintenance: May require developer for updates', 'plugin-wpshadow' ) . '</small>';
				$output .= '</div>';
			}
		}

		// Standard setup items.
		$output .= '<h4>' . esc_html__( 'Standard Setup (No Customization)', 'plugin-wpshadow' ) . '</h4>';
		$output .= '<ul>';
		$output .= '<li><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__( 'Menus', 'plugin-wpshadow' ) . '</li>';
		$output .= '<li><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__( 'Widgets', 'plugin-wpshadow' ) . '</li>';
		$output .= '<li><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__( 'Pages/Posts', 'plugin-wpshadow' ) . '</li>';
		$output .= '</ul>';

		$output .= '</div>';

		return $output;
	}

	/**
	 * Generate technical specifications section.
	 *
	 * @return string Technical specifications HTML.
	 */
	private static function generate_technical_specs(): string {
		global $wpdb;

		$theme        = wp_get_theme();
		$all_plugins  = get_plugins();
		$active_count = count( array_filter( $all_plugins, 'is_plugin_active', ARRAY_FILTER_USE_KEY ) );
		$table_count  = $wpdb->get_var( "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '" . esc_sql( DB_NAME ) . "'" );
		$upload_dir   = wp_upload_dir();
		$disk_space   = 0;

		if ( isset( $upload_dir['basedir'] ) && is_dir( $upload_dir['basedir'] ) ) {
			$disk_space = self::get_directory_size( $upload_dir['basedir'] );
		}

		$output  = '<ul class="wps-technical-specs">';
		$output .= '<li><strong>' . esc_html__( 'WordPress Version:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( get_bloginfo( 'version' ) ) . '</li>';
		$output .= '<li><strong>' . esc_html__( 'PHP Version:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( phpversion() ) . '</li>';
		$output .= '<li><strong>' . esc_html__( 'MySQL Version:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( $wpdb->db_version() ) . '</li>';
		$output .= '<li><strong>' . esc_html__( 'Theme:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( $theme->get( 'Name' ) ) . '</li>';
		$output .= '<li><strong>' . esc_html__( 'Plugins:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( (string) $active_count ) . ' active, ' . esc_html( (string) ( count( $all_plugins ) - $active_count ) ) . ' inactive</li>';
		$output .= '<li><strong>' . esc_html__( 'Database Tables:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( (string) $table_count ) . '</li>';
		$output .= '<li><strong>' . esc_html__( 'Uploads Disk Space:', 'plugin-wpshadow' ) . '</strong> ' . esc_html( size_format( $disk_space ) ) . '</li>';
		$output .= '</ul>';

		return $output;
	}

	/**
	 * Generate complete documentation export in specified format.
	 *
	 * @param string $format Export format (html, markdown, text).
	 * @return array{content: string, filename: string}|\WP_Error Export data or error.
	 */
	private static function generate_export( string $format ): array|\WP_Error {
		if ( ! in_array( $format, array( 'html', 'markdown', 'text' ), true ) ) {
			return new \WP_Error( 'invalid_format', __( 'Invalid export format', 'plugin-wpshadow' ) );
		}

		$content = '';

		switch ( $format ) {
			case 'html':
				$content = self::generate_html_export();
				break;
			case 'markdown':
				$content = self::generate_markdown_export();
				break;
			case 'text':
				$content = self::generate_text_export();
				break;
		}

		$site_name = get_bloginfo( 'name' );
		$date      = gmdate( 'Y-m-d' );
		$filename  = sanitize_file_name( $site_name . '-documentation-' . $date . '.' . ( 'markdown' === $format ? 'md' : $format ) );

		return array(
			'content'  => $content,
			'filename' => $filename,
		);
	}

	/**
	 * Generate HTML export.
	 *
	 * @return string HTML export content.
	 */
	private static function generate_html_export(): string {
		$site_name = get_bloginfo( 'name' );
		$date      = date_i18n( get_option( 'date_format' ) );

		$output  = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
		$output .= '<title>' . esc_html( $site_name ) . ' - Site Documentation</title>';
		$output .= '<style>body{font-family:Arial,sans-serif;max-width:1200px;margin:40px auto;padding:20px;line-height:1.6;}h1{border-bottom:3px solid #0073aa;}h2{color:#0073aa;margin-top:30px;}.plugin-card{background:#f5f5f5;padding:15px;margin:15px 0;border-left:4px solid #0073aa;}</style>';
		$output .= '</head><body>';
		$output .= '<h1>' . esc_html( $site_name ) . ' - Site Documentation</h1>';
		$output .= '<p><em>' . esc_html__( 'Generated:', 'plugin-wpshadow' ) . ' ' . esc_html( $date ) . '</em></p>';
		$output .= self::generate_blueprint();
		$output .= '<hr><h2>' . esc_html__( 'Maintenance Schedule', 'plugin-wpshadow' ) . '</h2>';
		$output .= self::generate_maintenance_schedule();
		$output .= '<h2>' . esc_html__( 'Emergency Contacts', 'plugin-wpshadow' ) . '</h2>';
		$output .= self::generate_emergency_contacts();
		$output .= '</body></html>';

		return $output;
	}

	/**
	 * Generate Markdown export.
	 *
	 * @return string Markdown export content.
	 */
	private static function generate_markdown_export(): string {
		$site_name = get_bloginfo( 'name' );
		$date      = date_i18n( get_option( 'date_format' ) );

		$output  = '# ' . $site_name . " - Site Documentation\n\n";
		$output .= '*Generated: ' . $date . "*\n\n";
		$output .= "## Executive Summary\n\n";
		$output .= wp_strip_all_tags( self::generate_executive_summary() ) . "\n\n";
		$output .= "## Plugin Inventory\n\n";
		$output .= wp_strip_all_tags( self::generate_plugin_inventory() ) . "\n\n";
		$output .= "## Technical Specifications\n\n";
		$output .= wp_strip_all_tags( self::generate_technical_specs() ) . "\n\n";
		$output .= "## Maintenance Schedule\n\n";
		$output .= wp_strip_all_tags( self::generate_maintenance_schedule() ) . "\n\n";
		$output .= "## Emergency Contacts\n\n";
		$output .= wp_strip_all_tags( self::generate_emergency_contacts() ) . "\n";

		return $output;
	}

	/**
	 * Generate plain text export.
	 *
	 * @return string Plain text export content.
	 */
	private static function generate_text_export(): string {
		return wp_strip_all_tags( self::generate_markdown_export() );
	}

	/**
	 * Generate maintenance schedule section.
	 *
	 * @return string Maintenance schedule HTML.
	 */
	private static function generate_maintenance_schedule(): string {
		$output  = '<div class="wps-maintenance-schedule">';
		$output .= '<h3>' . esc_html__( 'Monthly (First Monday)', 'plugin-wpshadow' ) . '</h3>';
		$output .= '<ul>';
		$output .= '<li>' . esc_html__( 'Check for plugin updates', 'plugin-wpshadow' ) . '</li>';
		$output .= '<li>' . esc_html__( 'Run site audit', 'plugin-wpshadow' ) . '</li>';
		$output .= '<li>' . esc_html__( 'Check activity logs', 'plugin-wpshadow' ) . '</li>';
		$output .= '<li>' . esc_html__( 'Verify backups working', 'plugin-wpshadow' ) . '</li>';
		$output .= '</ul>';
		$output .= '<p><em>' . esc_html__( 'Expected time: 1-2 hours', 'plugin-wpshadow' ) . '</em></p>';

		$output .= '<h3>' . esc_html__( 'Quarterly', 'plugin-wpshadow' ) . '</h3>';
		$output .= '<ul>';
		$output .= '<li>' . esc_html__( 'Full security audit', 'plugin-wpshadow' ) . '</li>';
		$output .= '<li>' . esc_html__( 'Performance optimization', 'plugin-wpshadow' ) . '</li>';
		$output .= '<li>' . esc_html__( 'Database cleanup', 'plugin-wpshadow' ) . '</li>';
		$output .= '</ul>';
		$output .= '<p><em>' . esc_html__( 'Expected time: 3-4 hours', 'plugin-wpshadow' ) . '</em></p>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Generate emergency contacts section.
	 *
	 * @return string Emergency contacts HTML.
	 */
	private static function generate_emergency_contacts(): string {
		$output  = '<div class="wps-emergency-contacts">';
		$output .= '<h3>' . esc_html__( 'Emergency Support', 'plugin-wpshadow' ) . '</h3>';
		$output .= '<p><strong>' . esc_html__( 'WPS Dashboard Support', 'plugin-wpshadow' ) . '</strong><br>';
		$output .= esc_html__( 'Email:', 'plugin-wpshadow' ) . ' christopher@wpshadow.com<br>';
		$output .= esc_html__( 'Support Portal:', 'plugin-wpshadow' ) . ' <a href="https://wpshadow.com/support" target="_blank">https://wpshadow.com/support</a><br>';
		$output .= esc_html__( '24/7 Emergency SOS available', 'plugin-wpshadow' ) . '</p>';

		$output .= '<h3>' . esc_html__( 'Community Support', 'plugin-wpshadow' ) . '</h3>';
		$output .= '<p>' . esc_html__( 'WordPress.org Forums:', 'plugin-wpshadow' ) . ' <a href="https://wordpress.org/support/" target="_blank">https://wordpress.org/support/</a></p>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get protected plugins from database.
	 *
	 * @return array<string, array<string, mixed>> Protected plugins array.
	 */
	private static function get_protected_plugins(): array {
		$protected = get_option( self::PROTECTED_PLUGINS_KEY, array() );
		return is_array( $protected ) ? $protected : array();
	}

	/**
	 * Get plugin purpose description.
	 *
	 * @param string $plugin_file Plugin file path.
	 * @return string Plugin purpose description.
	 */
	private static function get_plugin_purpose( string $plugin_file ): string {
		// Map common plugins to their purposes.
		$purposes = array(
			'contact-form-7/'                => __( 'Contact form management', 'plugin-wpshadow' ),
			'woocommerce/'                   => __( 'E-commerce shop functionality', 'plugin-wpshadow' ),
			'wordpress-seo/'                 => __( 'SEO optimization and ranking', 'plugin-wpshadow' ),
			'akismet/'                       => __( 'Spam protection', 'plugin-wpshadow' ),
			'elementor/'                     => __( 'Page builder and design', 'plugin-wpshadow' ),
			'jetpack/'                       => __( 'Security, performance, and marketing', 'plugin-wpshadow' ),
			'wordfence/'                     => __( 'Security and firewall', 'plugin-wpshadow' ),
			'wpshadow/'        => __( 'Site management and support', 'plugin-wpshadow' ),
			'plugin-wpshadow/' => __( 'Site management and support', 'plugin-wpshadow' ),
		);

		foreach ( $purposes as $slug => $purpose ) {
			if ( str_contains( $plugin_file, $slug ) ) {
				return $purpose;
			}
		}

		return __( 'General plugin functionality', 'plugin-wpshadow' );
	}

	/**
	 * Get plugin function description.
	 *
	 * @param string $plugin_file Plugin file path.
	 * @return string Plugin function description.
	 */
	private static function get_plugin_function( string $plugin_file ): string {
		$functions = array(
			'contact-form-7/'                => __( 'Collects form submissions and sends email notifications', 'plugin-wpshadow' ),
			'woocommerce/'                   => __( 'Manages products, orders, and payments', 'plugin-wpshadow' ),
			'wordpress-seo/'                 => __( 'Analyzes content and suggests SEO improvements', 'plugin-wpshadow' ),
			'akismet/'                       => __( 'Automatically filters spam comments', 'plugin-wpshadow' ),
			'elementor/'                     => __( 'Provides drag-and-drop page design tools', 'plugin-wpshadow' ),
			'jetpack/'                       => __( 'Adds security features, backups, and traffic tools', 'plugin-wpshadow' ),
			'wordfence/'                     => __( 'Scans for malware and blocks attacks', 'plugin-wpshadow' ),
			'wpshadow/'        => __( 'Provides dashboard insights and maintenance tools', 'plugin-wpshadow' ),
			'plugin-wpshadow/' => __( 'Provides dashboard insights and maintenance tools', 'plugin-wpshadow' ),
		);

		foreach ( $functions as $slug => $function ) {
			if ( str_contains( $plugin_file, $slug ) ) {
				return $function;
			}
		}

		return __( 'Adds additional features to your site', 'plugin-wpshadow' );
	}

	/**
	 * Check if plugin is essential.
	 *
	 * @param string $plugin_file Plugin file path.
	 * @return bool True if essential, false otherwise.
	 */
	private static function is_plugin_essential( string $plugin_file ): bool {
		$essential_patterns = array(
			'woocommerce/',
			'contact-form-7/',
			'wordfence/',
			'wpshadow/',
			'plugin-wpshadow/',
		);

		foreach ( $essential_patterns as $pattern ) {
			if ( str_contains( $plugin_file, $pattern ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get consequences of removing plugin.
	 *
	 * @param string $plugin_file Plugin file path.
	 * @return string Consequences description.
	 */
	private static function get_removal_consequences( string $plugin_file ): string {
		$consequences = array(
			'contact-form-7/'                => __( 'Contact forms will stop working', 'plugin-wpshadow' ),
			'woocommerce/'                   => __( 'Your entire shop will be disabled', 'plugin-wpshadow' ),
			'wordpress-seo/'                 => __( 'You lose SEO guidance (site still works)', 'plugin-wpshadow' ),
			'akismet/'                       => __( 'Spam comments will appear on your site', 'plugin-wpshadow' ),
			'elementor/'                     => __( 'Page layouts may break or render incorrectly', 'plugin-wpshadow' ),
			'jetpack/'                       => __( 'Security features and backups will be disabled', 'plugin-wpshadow' ),
			'wordfence/'                     => __( 'Site security monitoring will stop', 'plugin-wpshadow' ),
			'wpshadow/'        => __( 'Dashboard management tools will be unavailable', 'plugin-wpshadow' ),
			'plugin-wpshadow/' => __( 'Dashboard management tools will be unavailable', 'plugin-wpshadow' ),
		);

		foreach ( $consequences as $slug => $consequence ) {
			if ( str_contains( $plugin_file, $slug ) ) {
				return $consequence;
			}
		}

		return __( 'Some site features may stop working', 'plugin-wpshadow' );
	}

	/**
	 * Get custom plugins (non-WordPress.org plugins).
	 *
	 * @return array<string, array<string, mixed>> Custom plugins array.
	 */
	private static function get_custom_plugins(): array {
		$all_plugins    = get_plugins();
		$custom_plugins = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			// Check if plugin has no PluginURI or author URI pointing to WordPress.org.
			$plugin_uri = isset( $plugin_data['PluginURI'] ) ? $plugin_data['PluginURI'] : '';
			$author_uri = isset( $plugin_data['AuthorURI'] ) ? $plugin_data['AuthorURI'] : '';

			if ( empty( $plugin_uri ) ||
				( ! str_contains( $plugin_uri, 'wordpress.org' ) && ! str_contains( $author_uri, 'wordpress.org' ) ) ) {
				$custom_plugins[ $plugin_file ] = $plugin_data;
			}
		}

		return $custom_plugins;
	}

	/**
	 * Get directory size recursively.
	 *
	 * @param string $directory Directory path.
	 * @return int Directory size in bytes.
	 */
	private static function get_directory_size( string $directory ): int {
		$size = 0;

		if ( ! is_dir( $directory ) ) {
			return 0;
		}

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $files as $file ) {
			if ( $file->isFile() ) {
				$size += $file->getSize();
			}
		}

		return $size;
	}
}



