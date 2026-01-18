<?php
/**
 * Block Cleanup - Disable Separate Block Assets
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Block_Cleanup_Separate_Block_Assets extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-cleanup-separate-block-assets',
				'name'               => __( 'Disable Separate Block Assets', 'wpshadow' ),
				'description'        => __( 'Prevent WordPress from loading separate core block assets.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'block-cleanup',
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

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'block-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_block-cleanup_separate_block_assets';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_filter( 'should_load_separate_core_block_assets', array( $this, 'disable_separate_assets' ), 10 );
	}

	public function disable_separate_assets( $load ): bool {
		return false;
	}
}
