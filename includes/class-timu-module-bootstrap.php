<?php
/**
 * Module Bootstrap - Core-driven detection and installation of child plugins.
 *
 * Detects missing required modules and offers one-click install/activate.
 *
 * @package TIMU_CORE_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Bootstrap Class
 *
 * Manages detection of missing child plugins and provides install/activate UI.
 */
class TIMU_Module_Bootstrap {

	/**
	 * Required child modules (slug => data).
	 */
	private const REQUIRED_MODULES = array();

	/**
	 * Optional child modules (only shown if enabled in settings).
	 */
	private const OPTIONAL_MODULES = array(
		'media-support-thisismyurl' => array(
			'name'        => 'Media Support',
			'description' => 'Provides shared media optimization and processing infrastructure',
			'repo'        => 'thisismyurl/media-support-thisismyurl',
			'install_url' => 'https://github.com/thisismyurl/media-support-thisismyurl/archive/refs/heads/main.zip',
		),
		'image-support-thisismyurl' => array(
			'name'        => 'Image Support',
			'description' => 'Hub for image format support and processing',
			'repo'        => 'thisismyurl/module-images-support-thisismyurl',
			'install_url' => 'https://github.com/thisismyurl/module-images-support-thisismyurl/archive/refs/heads/main.zip',
		),
		'vault-support-thisismyurl' => array(
			'name'        => 'Vault Support',
			'description' => 'Secure original storage with encryption, journaling, and cloud offload',
			'repo'        => 'thisismyurl/vault-support-thisismyurl',
			'install_url' => 'https://github.com/thisismyurl/vault-support-thisismyurl/archive/refs/heads/main.zip',
		),
	);

	/**
	 * Initialize bootstrap.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Show admin notices for missing required plugins.
		add_action( 'admin_notices', array( __CLASS__, 'show_missing_notices' ) );
		add_action( 'network_admin_notices', array( __CLASS__, 'show_missing_notices' ) );

		// Handle install/activate actions.
		add_action( 'admin_post_timu_install_module', array( __CLASS__, 'handle_install_module' ) );
		add_action( 'admin_post_timu_activate_module', array( __CLASS__, 'handle_activate_module' ) );

		// Show guided setup on first run (if no modules detected).
		add_action( 'admin_init', array( __CLASS__, 'maybe_show_guided_setup' ) );
	}

	/**
	 * Display admin notices for missing required plugins.
	 *
	 * @return void
	 */
	public static function show_missing_notices(): void {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			return;
		}

		$missing = self::get_missing_required_modules();

		foreach ( $missing as $slug => $module ) {
			$is_installed = self::is_plugin_installed( $slug );
			$action_label = $is_installed ? __( 'Activate', 'plugin-wp-support-thisismyurl' ) : __( 'Install & Activate', 'plugin-wp-support-thisismyurl' );
			$nonce        = wp_create_nonce( 'timu_module_action' );
			$action       = $is_installed ? 'timu_activate_module' : 'timu_install_module';
			$action_url   = add_query_arg(
				array(
					'action'   => $action,
					'module'   => $slug,
					'nonce'    => $nonce,
					'redirect' => rawurlencode( admin_url( 'admin.php?page=timu-core-dashboard' ) ),
				),
				admin_url( 'admin-post.php' )
			);
			?>
			<div class="notice notice-warning is-dismissible" role="alert" aria-live="polite">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: 1: Module name, 2: Module description */
							__( '<strong>Core Support:</strong> %1$s is recommended. %2$s', 'plugin-wp-support-thisismyurl' ),
							esc_html( $module['name'] ),
							esc_html( $module['description'] )
						)
					);
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( $action_url ); ?>" class="button button-primary" aria-label="<?php echo esc_attr( sprintf( __( '%1$s: %2$s', 'plugin-wp-support-thisismyurl' ), $action_label, $module['name'] ) ); ?>">
						<?php echo esc_html( $action_label ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Handle install module request.
	 *
	 * @return void
	 */
	public static function handle_install_module(): void {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) );
		}

		$nonce  = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
		$module = isset( $_REQUEST['module'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['module'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'timu_module_action' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'plugin-wp-support-thisismyurl' ) );
		}

		$all_modules = array_merge( self::REQUIRED_MODULES, self::OPTIONAL_MODULES );
		if ( ! isset( $all_modules[ $module ] ) ) {
			wp_die( esc_html__( 'Invalid module.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Install plugin from GitHub.
		$result = self::install_plugin( $module, $all_modules[ $module ]['install_url'] );

		if ( is_wp_error( $result ) ) {
			wp_die( wp_kses_post( $result->get_error_message() ) );
		}

		// Log action.
		self::log_action( 'install', $module, get_current_user_id() );

		// Redirect and activate.
		wp_redirect(
			add_query_arg(
				array(
					'action'   => 'timu_activate_module',
					'module'   => $module,
					'nonce'    => wp_create_nonce( 'timu_module_action' ),
					'redirect' => rawurlencode( isset( $_REQUEST['redirect'] ) ? sanitize_url( wp_unslash( $_REQUEST['redirect'] ) ) : admin_url( 'admin.php?page=timu-core-dashboard' ) ),
				),
				admin_url( 'admin-post.php' )
			)
		);
		exit;
	}

	/**
	 * Handle activate module request.
	 *
	 * @return void
	 */
	public static function handle_activate_module(): void {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) );
		}

		$nonce  = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
		$module = isset( $_REQUEST['module'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['module'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'timu_module_action' ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'plugin-wp-support-thisismyurl' ) );
		}

		$all_modules = array_merge( self::REQUIRED_MODULES, self::OPTIONAL_MODULES );
		if ( ! isset( $all_modules[ $module ] ) ) {
			wp_die( esc_html__( 'Invalid module.', 'plugin-wp-support-thisismyurl' ) );
		}

		if ( ! self::is_plugin_installed( $module ) ) {
			wp_die( esc_html__( 'Plugin not installed.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Activate plugin.
		$plugin_file = self::get_plugin_file( $module );
		if ( ! $plugin_file ) {
			wp_die( esc_html__( 'Plugin file not found.', 'plugin-wp-support-thisismyurl' ) );
		}

		$result = activate_plugin( $plugin_file );
		if ( is_wp_error( $result ) ) {
			wp_die( wp_kses_post( $result->get_error_message() ) );
		}

		// Log action.
		self::log_action( 'activate', $module, get_current_user_id() );

		// Redirect.
		$redirect = isset( $_REQUEST['redirect'] ) ? sanitize_url( wp_unslash( $_REQUEST['redirect'] ) ) : admin_url( 'admin.php?page=timu-core-dashboard' );
		wp_safe_remote_post(
			add_query_arg( 'timu_module_activated', $module, $redirect ),
			array( 'blocking' => false )
		);
		exit;
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @param string $slug Plugin slug (directory name).
	 * @return bool True if installed.
	 */
	private static function is_plugin_installed( string $slug ): bool {
		$plugin_file = self::get_plugin_file( $slug );
		return ! empty( $plugin_file );
	}

	/**
	 * Get plugin file path (relative to wp-content/plugins).
	 *
	 * @param string $slug Plugin slug.
	 * @return string|null Plugin file or null if not found.
	 */
	private static function get_plugin_file( string $slug ): ?string {
		$plugins = get_plugins();
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if ( strpos( $plugin_file, $slug . '/' ) === 0 ) {
				return $plugin_file;
			}
		}
		return null;
	}

	/**
	 * Install plugin from ZIP.
	 *
	 * @param string $slug       Plugin slug.
	 * @param string $zip_url    Download URL.
	 * @return true|WP_Error True on success or WP_Error on failure.
	 */
	private static function install_plugin( string $slug, string $zip_url ) {
		// Requires WP_Upgrader.
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );

		// Download plugin ZIP.
		$download_result = $upgrader->download_package( $zip_url );
		if ( is_wp_error( $download_result ) ) {
			return $download_result;
		}

		// Extract to plugins directory.
		$result = $upgrader->unpack_package( $download_result );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * Get missing required modules.
	 *
	 * @return array Modules that are not active.
	 */
	private static function get_missing_required_modules(): array {
		$missing = array();
		$active  = get_option( 'active_plugins', array() );

		foreach ( self::REQUIRED_MODULES as $slug => $module_data ) {
			$plugin_file = self::get_plugin_file( $slug );
			if ( ! $plugin_file || ! in_array( $plugin_file, $active, true ) ) {
				$missing[ $slug ] = $module_data;
			}
		}

		return $missing;
	}

	/**
	 * Show guided setup on first run.
	 *
	 * @return void
	 */
	public static function maybe_show_guided_setup(): void {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			return;
		}

		$setup_done = (bool) get_option( 'timu_core_setup_completed', false );
		if ( $setup_done ) {
			return;
		}

		// Mark setup as done.
		update_option( 'timu_core_setup_completed', time() );

		// Redirect to dashboard with setup flag.
		if ( 'timu-core-dashboard' !== ( $_GET['page'] ?? null ) ) {
			wp_safe_redirect( add_query_arg( 'timu_setup', '1', admin_url( 'admin.php?page=timu-core-dashboard' ) ) );
			exit;
		}
	}

	/**
	 * Log module action.
	 *
	 * @param string $action  Action (install, activate).
	 * @param string $module  Module slug.
	 * @param int    $user_id User ID.
	 * @return void
	 */
	private static function log_action( string $action, string $module, int $user_id ): void {
		$user      = get_user_by( 'id', $user_id );
		$user_name = $user ? $user->display_name : 'Unknown';

		if ( class_exists( 'TIMU\\CoreSupport\\TIMU_Vault' ) ) {
			TIMU_Vault::add_log(
				'info',
				0,
				sprintf( 'Module %s: %s', esc_html( $module ), esc_html( $action ) ),
				'module_' . $action,
				array(
					'module'  => $module,
					'user'    => $user_name,
					'user_id' => $user_id,
				)
			);
		}
	}
}
