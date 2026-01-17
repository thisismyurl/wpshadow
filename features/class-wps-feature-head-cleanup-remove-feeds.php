<?php
/**
 * Head Cleanup - Remove Feed Links
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Head_Cleanup_Remove_Feeds extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'head-cleanup-remove-feeds',
				'name'               => __( 'Remove Feed Links', 'plugin-wpshadow' ),
				'description'        => __( 'Remove RSS/Atom feed links from page headers.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'parent'             => 'head-cleanup',
				'icon'               => 'dashicons-rss',
				'category'           => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
			)
		);
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'head-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_head-cleanup_remove_feeds';
		if ( false === get_option( $option_name ) ) {
			update_option( $option_name, false, false );
		}
		if ( ! get_option( $option_name, false ) ) {
			return;
		}

		add_action( 'init', array( $this, 'remove_feed_links' ) );
	}

	public function remove_feed_links(): void {
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'feed_links', 2 );
	}
}
