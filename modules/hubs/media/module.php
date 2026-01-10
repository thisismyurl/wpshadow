<?php
/**
 * Media Hub Module
 *
 * This module is loaded by the TIMU Core Module Loader.
 * It is NOT a WordPress plugin, but an extension of Core.
 *
 * @package TIMU_CORE
 * @subpackage TIMU_MEDIA_HUB
 */

declare(strict_types=1);

namespace TIMU\MediaSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
// Initialize constants via DRY initializer (defined in wp-support core).
\TIMU\CoreSupport\TIMU_Module_Hub_Initializer::define_module_constants(
	__FILE__,
	'media-support-thisismyurl',
	'media-support-thisismyurl',
	'1.2601.0819',
	'8.1.29',
	'6.4.0'
);

/**
 * Initialize Media Support.
 */
function timu_media_init(): void {
	// Verify Core is present using DRY initializer.
	if ( ! \TIMU\CoreSupport\TIMU_Module_Hub_Initializer::check_core_availability(
		__( 'Media Support', TIMU_MEDIA_TEXT_DOMAIN ),
		TIMU_MEDIA_TEXT_DOMAIN
	) ) {
		return;
	}

	// Register hub module via DRY initializer.
	\TIMU\CoreSupport\TIMU_Module_Hub_Initializer::register_hub_module( array(
		'slug'         => 'media-support-thisismyurl',
		'name'         => __( 'Media Support', TIMU_MEDIA_TEXT_DOMAIN ),
		'suite'        => 'media',
		'version'      => TIMU_MEDIA_VERSION,
		'description'  => __( 'Media hub for non-image media processing, batching, and policies.', TIMU_MEDIA_TEXT_DOMAIN ),
		'capabilities' => array( 'media_hub', 'batch', 'policies' ),
		'path'         => TIMU_MEDIA_PATH,
		'url'          => TIMU_MEDIA_URL,
		'basename'     => TIMU_MEDIA_BASENAME,
		'text_domain'  => TIMU_MEDIA_TEXT_DOMAIN,
	) );

	// Register hub feature via DRY initializer.
	\TIMU\CoreSupport\TIMU_Module_Hub_Initializer::register_hub_feature( 'media_hub', array(
		'name'        => __( 'Media Hub', TIMU_MEDIA_TEXT_DOMAIN ),
		'description' => __( 'Shared media optimization and processing infrastructure', TIMU_MEDIA_TEXT_DOMAIN ),
		'version'     => TIMU_MEDIA_VERSION,
	) );

	// Minimal hub class extending Core Spoke Base (no subcomponents yet).
	if ( ! class_exists( __NAMESPACE__ . '\\TIMU_Media_Support' ) ) {
		class TIMU_Media_Support extends \TIMU\Core\Spoke\TIMU_Spoke_Base {
			public function __construct() {
				parent::__construct( 'media-support-thisismyurl', TIMU_MEDIA_URL, 'timu_media_settings_group', 'dashicons-admin-media', 'wp-support' );
				add_action( 'init', array( $this, 'setup_plugin' ), 20 );
			}

			public function setup_plugin(): void {
				$this->is_licensed();
				$this->init_settings_generator(
					array(
						'hub_settings' => array(
							'title'       => __( 'Media Hub Settings', TIMU_MEDIA_TEXT_DOMAIN ),
							'description' => __( 'Central coordination for media (non-image) operations.', TIMU_MEDIA_TEXT_DOMAIN ),
							'fields'      => array(
								'enabled' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Media Hub', TIMU_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Master switch for media processing.', TIMU_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
							),
						),
					)
				);
			}

			public function add_admin_menu(): void {
				// Menu items are now auto-registered by Core's timu_core_register_hub_submenus()
				// This method is kept for backward compatibility but does nothing.
			}

			public function render_media_settings_redirect(): void {
				$redirect_url = admin_url( 'admin.php?page=wp-support&tab=media-support-thisismyurl' );
				?>
				<script type="text/javascript">
					window.location.href = '<?php echo esc_url( $redirect_url ); ?>';
				</script>
				<?php
			}

			public function render_settings_page(): void {
				$this->render_settings_page_base( strtoupper( __( 'Media Support', TIMU_MEDIA_TEXT_DOMAIN ) ) );
			}
		}
	}

	new TIMU_Media_Support();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\timu_media_init', 12 );

/**
 * Render Media Hub dashboard.
 *
 * @return void
 */
function render_dashboard(): void {
	// Get module catalog for spoke modules.
	// Get spoke modules via DRY initializer.
	$modules = \TIMU\CoreSupport\TIMU_Module_Hub_Initializer::get_hub_spoke_modules( 'media-support-thisismyurl' );

	// Get activity logs if Vault is available.
	// Get activity logs via DRY initializer.
	$activity_logs = \TIMU\CoreSupport\TIMU_Module_Hub_Initializer::get_vault_activity_logs( 10 );

	require_once TIMU_MEDIA_PATH . 'views/dashboard.php';
}

