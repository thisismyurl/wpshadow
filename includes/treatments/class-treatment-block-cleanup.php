<?php
/**
 * Block Asset Cleanup Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to dequeue block assets on the front-end when not needed.
 */
class Treatment_Block_Cleanup extends Treatment_Base {
	public static function get_finding_id() {
		return 'block-assets-loaded';
	}

	public static function apply() {
		update_option( 'wpshadow_block_cleanup_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Block assets will be dequeued on the front-end when not needed.',
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_block_cleanup_enabled' );
		return array(
			'success' => true,
			'message' => 'Block asset cleanup disabled.',
		);
	}
}
