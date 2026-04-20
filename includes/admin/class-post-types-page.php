<?php
/**
 * Post Types Admin Page
 *
 * Scoped management interface for WP Shadow controlled post type behavior.
 *
 * @package WPShadow
 * @subpackage Admin
 * @since 0.6095
 */

namespace WPShadow\Admin;

use WPShadow\Content\Post_Types\Site_Content_Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage CPT-specific feature toggles and activation state.
 */
class Post_Types_Page {

	/**
	 * Admin page slug.
	 */
	const PAGE_SLUG = 'wpshadow-post-types';

	/**
	 * Query var used for selected CPT slug on this admin page.
	 */
	const CPT_QUERY_VAR = 'cpt';

	/**
	 * Option key for persisted CPT feature settings.
	 */
	const OPTION_KEY = 'wpshadow_post_type_feature_settings';

	/**
	 * Option key for persisted CPT activation settings.
	 */
	const ACTIVATION_OPTION_KEY = 'wpshadow_post_type_activation_settings';

	/**
	 * Register submenu page.
	 *
	 * @return void
	 */
	public static function subscribe(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Post Types', 'wpshadow' ),
			__( 'Post Types', 'wpshadow' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Enqueue CSS only for this screen.
	 *
	 * @param string $hook Page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( false === strpos( $hook, self::PAGE_SLUG ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-post-types-page',
			WPSHADOW_URL . 'assets/css/post-types-page.css',
			array(),
			file_exists( WPSHADOW_PATH . 'assets/css/post-types-page.css' )
				? (string) filemtime( WPSHADOW_PATH . 'assets/css/post-types-page.css' )
				: WPSHADOW_VERSION
		);
	}

	/**
	 * Render overview or detail page.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
		}

		$post_types = Site_Content_Models::get_post_type_definitions();
		$selected = '';
		if ( isset( $_GET[ self::CPT_QUERY_VAR ] ) ) {
			$selected = sanitize_key( wp_unslash( $_GET[ self::CPT_QUERY_VAR ] ) );
		}

		if ( isset( $_POST['wpshadow_post_type_action'] ) ) {
			self::handle_save( $post_types );
		}

		if ( '' !== $selected && isset( $post_types[ $selected ] ) ) {
			self::render_detail_page( $selected, $post_types[ $selected ] );
			return;
		}

		self::render_overview_page( $post_types );
	}

	/**
	 * Save CPT feature settings.
	 *
	 * @param array<string,array<string,mixed>> $post_types Known post types.
	 * @return void
	 */
	private static function handle_save( array $post_types ): void {
		if ( ! isset( $_POST['wpshadow_post_type_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['wpshadow_post_type_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'wpshadow_save_post_type_features' ) ) {
			return;
		}

		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';
		if ( '' === $post_type || ! isset( $post_types[ $post_type ] ) ) {
			return;
		}

		$catalog  = self::get_feature_catalog();
		$incoming = isset( $_POST['features'] ) ? (array) wp_unslash( $_POST['features'] ) : array();

		$current = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $current ) ) {
			$current = array();
		}

		$scoped = array();
		foreach ( $catalog as $feature_key => $feature_data ) {
			$scoped[ $feature_key ] = isset( $incoming[ $feature_key ] ) ? 1 : 0;
		}

		$current[ $post_type ] = $scoped;
		update_option( self::OPTION_KEY, $current, false );

		$activation_settings = self::get_activation_settings();
		$was_active          = self::is_post_type_active( $post_type );
		$is_active           = isset( $_POST['post_type_enabled'] ) ? 1 : 0;

		$activation_settings[ $post_type ] = $is_active;
		update_option( self::ACTIVATION_OPTION_KEY, $activation_settings, false );

		if ( (int) $was_active !== (int) $is_active ) {
			Site_Content_Models::mark_rewrite_stale();
		}

		$redirect = add_query_arg(
			array(
				'page'             => self::PAGE_SLUG,
				self::CPT_QUERY_VAR => $post_type,
				'updated'          => '1',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Render CPT overview cards.
	 *
	 * @param array<string,array<string,mixed>> $post_types Known post types.
	 * @return void
	 */
	private static function render_overview_page( array $post_types ): void {
		$settings = self::get_feature_settings();
		?>
		<div class="wrap wpshadow-post-types">
			<h1><?php esc_html_e( 'Post Types', 'wpshadow' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Manage activation and scoped feature presets for each imported content model.', 'wpshadow' ); ?></p>
			<div class="wpshadow-post-types-grid">
				<?php foreach ( $post_types as $post_type => $definition ) : ?>
					<?php
					$is_active        = self::is_post_type_active( $post_type );
					$enabled_features = isset( $settings[ $post_type ] ) && is_array( $settings[ $post_type ] )
						? array_sum( array_map( 'intval', $settings[ $post_type ] ) )
						: 0;
					$taxonomies       = Site_Content_Models::get_taxonomies_for_post_type( $post_type );
					?>
					<article class="wpshadow-post-type-card">
						<span class="wpshadow-status-pill <?php echo $is_active ? 'is-active' : 'is-inactive'; ?>">
							<?php echo $is_active ? esc_html__( 'Active', 'wpshadow' ) : esc_html__( 'Inactive', 'wpshadow' ); ?>
						</span>
						<h2><?php echo esc_html( self::get_label( $definition, 'name', $post_type ) ); ?></h2>
						<p><strong><?php esc_html_e( 'Slug:', 'wpshadow' ); ?></strong> <code><?php echo esc_html( $post_type ); ?></code></p>
						<p><strong><?php esc_html_e( 'Status:', 'wpshadow' ); ?></strong> <?php echo $is_active ? esc_html__( 'Active', 'wpshadow' ) : esc_html__( 'Inactive', 'wpshadow' ); ?></p>
						<p><strong><?php esc_html_e( 'Taxonomies:', 'wpshadow' ); ?></strong> <?php echo esc_html( implode( ', ', $taxonomies ) ); ?></p>
						<p><strong><?php esc_html_e( 'Enabled Features:', 'wpshadow' ); ?></strong> <?php echo esc_html( (string) $enabled_features ); ?></p>
						<p>
							<a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'page' => self::PAGE_SLUG, self::CPT_QUERY_VAR => $post_type ), admin_url( 'admin.php' ) ) ); ?>">
								<?php esc_html_e( 'Manage Features', 'wpshadow' ); ?>
							</a>
						</p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render CPT scoped management screen.
	 *
	 * @param string              $post_type  CPT slug.
	 * @param array<string,mixed> $definition CPT definition.
	 * @return void
	 */
	private static function render_detail_page( string $post_type, array $definition ): void {
		$catalog    = self::get_feature_catalog();
		$settings   = self::get_feature_settings();
		$current    = isset( $settings[ $post_type ] ) && is_array( $settings[ $post_type ] ) ? $settings[ $post_type ] : array();
		$is_active  = self::is_post_type_active( $post_type );
		$taxonomies = Site_Content_Models::get_taxonomies_for_post_type( $post_type );
		?>
		<div class="wrap wpshadow-post-types">
			<h1><?php echo esc_html( self::get_label( $definition, 'name', $post_type ) ); ?></h1>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::PAGE_SLUG ), admin_url( 'admin.php' ) ) ); ?>">&larr; <?php esc_html_e( 'Back to all post types', 'wpshadow' ); ?></a>
			</p>

			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Scoped feature settings saved.', 'wpshadow' ); ?></p></div>
			<?php endif; ?>

			<div class="wpshadow-post-type-layout">
				<section class="wpshadow-post-type-panel">
					<h2><?php esc_html_e( 'Post Type Availability', 'wpshadow' ); ?></h2>
					<form method="post" action="">
						<?php wp_nonce_field( 'wpshadow_save_post_type_features', 'wpshadow_post_type_nonce' ); ?>
						<input type="hidden" name="wpshadow_post_type_action" value="save_features" />
						<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />

						<label class="wpshadow-feature-toggle wpshadow-primary-toggle">
							<input
								type="checkbox"
								name="post_type_enabled"
								value="1"
								<?php checked( $is_active ); ?>
							/>
							<span>
								<strong><?php esc_html_e( 'Enable this post type', 'wpshadow' ); ?></strong>
								<small><?php esc_html_e( 'When disabled, this custom post type is not registered in WordPress admin or frontend routes.', 'wpshadow' ); ?></small>
							</span>
						</label>

						<h2><?php esc_html_e( 'Special Features', 'wpshadow' ); ?></h2>

						<?php foreach ( $catalog as $feature_key => $feature_data ) : ?>
							<label class="wpshadow-feature-toggle">
								<input
									type="checkbox"
									name="features[<?php echo esc_attr( $feature_key ); ?>]"
									value="1"
									<?php checked( ! empty( $current[ $feature_key ] ) ); ?>
								/>
								<span>
									<strong><?php echo esc_html( $feature_data['label'] ); ?></strong>
									<small><?php echo esc_html( $feature_data['description'] ); ?></small>
								</span>
							</label>
						<?php endforeach; ?>

						<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'wpshadow' ); ?></button></p>
					</form>
				</section>

				<section class="wpshadow-post-type-panel">
					<h2><?php esc_html_e( 'Scoped Context', 'wpshadow' ); ?></h2>
					<ul>
						<li><strong><?php esc_html_e( 'Post Type Slug:', 'wpshadow' ); ?></strong> <code><?php echo esc_html( $post_type ); ?></code></li>
						<li><strong><?php esc_html_e( 'Status:', 'wpshadow' ); ?></strong> <code><?php echo $is_active ? esc_html__( 'active', 'wpshadow' ) : esc_html__( 'inactive', 'wpshadow' ); ?></code></li>
						<li><strong><?php esc_html_e( 'REST Base:', 'wpshadow' ); ?></strong> <code><?php echo esc_html( self::get_label( $definition, 'rest_base', '' ) ); ?></code></li>
						<li><strong><?php esc_html_e( 'Archive:', 'wpshadow' ); ?></strong> <code><?php echo esc_html( (string) self::get_label( $definition, 'has_archive', '' ) ); ?></code></li>
						<li><strong><?php esc_html_e( 'Attached Taxonomies:', 'wpshadow' ); ?></strong> <code><?php echo esc_html( implode( ', ', $taxonomies ) ); ?></code></li>
					</ul>
				</section>
			</div>
		</div>
		<?php
	}

	/**
	 * Load option settings.
	 *
	 * @return array<string,array<string,int>>
	 */
	private static function get_feature_settings(): array {
		$settings = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $settings ) ) {
			return array();
		}

		return $settings;
	}

	/**
	 * Load activation settings.
	 *
	 * @return array<string,int>
	 */
	private static function get_activation_settings(): array {
		$settings = get_option( self::ACTIVATION_OPTION_KEY, array() );

		if ( ! is_array( $settings ) ) {
			return array();
		}

		return $settings;
	}

	/**
	 * Determine whether a post type is active.
	 *
	 * @param string $post_type Post type slug.
	 * @return bool
	 */
	private static function is_post_type_active( string $post_type ): bool {
		$settings = self::get_activation_settings();

		if ( ! array_key_exists( $post_type, $settings ) ) {
			return false;
		}

		return ! empty( $settings[ $post_type ] );
	}

	/**
	 * Feature catalog used in UI.
	 *
	 * @return array<string,array<string,string>>
	 */
	private static function get_feature_catalog(): array {
		return array(
			'force_rest'               => array(
				'label'       => __( 'Force REST Visibility', 'wpshadow' ),
				'description' => __( 'Ensures this post type is available to REST consumers.', 'wpshadow' ),
			),
			'disable_comments_support' => array(
				'label'       => __( 'Disable Comment Supports', 'wpshadow' ),
				'description' => __( 'Removes comment and trackback support for this post type.', 'wpshadow' ),
			),
			'exclude_from_search'      => array(
				'label'       => __( 'Exclude From Frontend Search', 'wpshadow' ),
				'description' => __( 'Prevents this post type from appearing in default frontend search results.', 'wpshadow' ),
			),
			'force_archive'            => array(
				'label'       => __( 'Force Archive Endpoint', 'wpshadow' ),
				'description' => __( 'Maintains an archive endpoint even when other code attempts to disable it.', 'wpshadow' ),
			),
			'taxonomy_rest'            => array(
				'label'       => __( 'Expose Attached Taxonomies in REST', 'wpshadow' ),
				'description' => __( 'Applies REST visibility to taxonomies attached to this post type.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Return normalized label from definition.
	 *
	 * @param array<string,mixed> $definition Data.
	 * @param string              $key        Index.
	 * @param string              $fallback   Fallback.
	 * @return string
	 */
	private static function get_label( array $definition, string $key, string $fallback ): string {
		if ( ! isset( $definition[ $key ] ) ) {
			return $fallback;
		}

		return is_string( $definition[ $key ] ) ? $definition[ $key ] : $fallback;
	}
}
