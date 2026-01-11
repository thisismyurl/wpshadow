<?php
/**
 * Media Hub Module
 *
 * This module is loaded by the WPS Core Module Loader.
 * It is NOT a WordPress plugin, but an extension of Core.
 *
 * @package wp_support
 * @subpackage WPS_MEDIA_HUB
 */

declare(strict_types=1);

namespace WPS\MediaSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure DRY hub initializer is available when module files load directly.
if ( ! class_exists( '\WPS\CoreSupport\WPS_Module_Hub_Initializer' ) ) {
	require_once dirname( __DIR__, 3 ) . '/includes/class-wps-module-hub-initializer.php';
}

// Plugin constants.
// Initialize constants via DRY initializer (defined in wp-support core).
		\WPS\CoreSupport\WPS_Module_Hub_Initializer::define_module_constants(
	__FILE__,
	'media-support-thisismyurl',
	'media-support-thisismyurl',
	'1.2601.0819',
	'8.1.29',
	'6.4.0',
	'WPS_MEDIA'
);

/**
 * Initialize Media Support.
 */
function WPS_media_init(): void {
	// Verify Core is present using DRY initializer.
	if ( ! \WPS\CoreSupport\WPS_Module_Hub_Initializer::check_core_availability(
		__( 'Media Support', WPS_MEDIA_TEXT_DOMAIN ),
		WPS_MEDIA_TEXT_DOMAIN
	) ) {
		return;
	}

	// Register hub module via DRY initializer.
	\WPS\CoreSupport\WPS_Module_Hub_Initializer::register_hub_module( array(
		'slug'         => 'media-support-thisismyurl',
		'name'         => __( 'Media Support', WPS_MEDIA_TEXT_DOMAIN ),
		'suite'        => 'media',
		'version'      => WPS_MEDIA_VERSION,
		'description'  => __( 'Media hub for non-image media processing, batching, and policies.', WPS_MEDIA_TEXT_DOMAIN ),
		'capabilities' => array( 'media_hub', 'batch', 'policies' ),
		'path'         => WPS_MEDIA_PATH,
		'url'          => WPS_MEDIA_URL,
		'basename'     => WPS_MEDIA_BASENAME,
		'text_domain'  => WPS_MEDIA_TEXT_DOMAIN,
	) );

	// Register hub feature via DRY initializer.
	\WPS\CoreSupport\WPS_Module_Hub_Initializer::register_hub_feature( 'media_hub', array(
		'name'        => __( 'Media Hub', WPS_MEDIA_TEXT_DOMAIN ),
		'description' => __( 'Shared media optimization and processing infrastructure', WPS_MEDIA_TEXT_DOMAIN ),
		'version'     => WPS_MEDIA_VERSION,
	) );

	// Minimal hub class extending Core Spoke Base (no subcomponents yet).
	if ( ! class_exists( __NAMESPACE__ . '\\WPS_Media_Support' ) ) {
		class WPS_Media_Support extends \WPS\Core\Spoke\WPS_Spoke_Base {
			public function __construct() {
				parent::__construct( 'media-support-thisismyurl', WPS_MEDIA_URL, 'WPS_media_settings_group', 'dashicons-admin-media', 'wp-support' );
				add_action( 'init', array( $this, 'setup_plugin' ), 20 );
				// Smart Upload Directory: adjust year/month to parent post publish date.
				add_filter( 'upload_dir', array( $this, 'filter_upload_dir_by_parent_date' ), 10, 1 );
			}

			public function setup_plugin(): void {
				$this->is_licensed();
				$this->init_settings_generator(
					array(
						'hub_settings' => array(
							'title'       => __( 'Media Hub Settings', WPS_MEDIA_TEXT_DOMAIN ),
							'description' => __( 'Central coordination for media (non-image) operations.', WPS_MEDIA_TEXT_DOMAIN ),
							'fields'      => array(
								'enabled' => array(
									'type'         => 'toggle',
									'label'        => __( 'Enable Media Hub', WPS_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Master switch for media processing.', WPS_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
								'smart_upload_dir' => array(
									'type'         => 'toggle',
									'label'        => __( 'Smart Upload Directory', WPS_MEDIA_TEXT_DOMAIN ),
									'description'  => __( 'Organize uploads by parent post publish date.', WPS_MEDIA_TEXT_DOMAIN ),
									'default'      => 1,
									'globalizable' => true,
								),
							),
						),
					)
				);
			}

			/**
			 * Smart Upload Directory: route uploads into year/month based on parent post publish date.
			 *
			 * @param array $uploads Upload directory data.
			 * @return array Possibly modified upload directory data.
			 */
			public function filter_upload_dir_by_parent_date( array $uploads ): array {
				// Respect WordPress setting to use year/month folders.
				$use_yearmonth = (bool) get_option( 'uploads_use_yearmonth_folders', true );
				if ( ! $use_yearmonth ) {
					return $uploads;
				}

				// Feature toggle via settings (defaults to enabled).
				$enabled = true;
				if ( method_exists( $this, 'get_option' ) ) {
					$opt = $this->get_option( 'smart_upload_dir' );
					$enabled = null === $opt ? true : (bool) $opt;
				}
				if ( ! $enabled ) {
					return $uploads;
				}

				// Identify parent post being edited (common during media uploads from editor).
				$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( $post_id <= 0 ) {
					return $uploads;
				}

				$parent = get_post( $post_id );
				if ( ! $parent || 'attachment' === $parent->post_type ) {
					return $uploads;
				}

				// Use scheduled/published date when available; fallback to post_date.
				$date_str = '';
				if ( ! empty( $parent->post_date_gmt ) && '0000-00-00 00:00:00' !== $parent->post_date_gmt ) {
					$date_str = get_date_from_gmt( $parent->post_date_gmt );
				} else {
					$date_str = $parent->post_date;
				}
				if ( empty( $date_str ) ) {
					return $uploads;
				}

				$ts = strtotime( $date_str );
				if ( false === $ts ) {
					return $uploads;
				}

				$year   = gmdate( 'Y', $ts );
				$month  = gmdate( 'm', $ts );
				$subdir = '/' . $year . '/' . $month;

				$uploads['subdir'] = $subdir;
				$uploads['path']   = trailingslashit( $uploads['basedir'] ) . $year . '/' . $month;
				$uploads['url']    = trailingslashit( $uploads['baseurl'] ) . $year . '/' . $month;

				return $uploads;
			}


		}
	}

	new WPS_Media_Support();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\WPS_media_init', 12 );



