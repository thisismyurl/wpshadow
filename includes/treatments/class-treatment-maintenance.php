<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Interface;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Maintenance implements Treatment_Interface {

	public static function get_finding_id(): string {
		return 'maintenance';
	}

	public static function can_apply(): bool {
		return current_user_can( 'update_core' );
	}

	public static function apply(): array {
		$maint_file = ABSPATH . '.maintenance';

		if ( ! file_exists( $maint_file ) ) {
			return array(
				'success' => false,
				'message' => __( 'No maintenance file found.', 'wpshadow' ),
			);
		}

		$backup_file = ABSPATH . '.maintenance.bak.' . time();
		if ( ! copy( $maint_file, $backup_file ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to backup maintenance file.', 'wpshadow' ),
			);
		}

		if ( ! unlink( $maint_file ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to remove maintenance file. Check file permissions.', 'wpshadow' ),
			);
		}

		KPI_Tracker::log_fix_applied( 'maintenance', 'stability' );

		return array(
			'success' => true,
			'message' => __( 'Maintenance file removed successfully. Site is now accessible. Backup saved.', 'wpshadow' ),
		);
	}

	public static function undo(): array {
		$backups = glob( ABSPATH . '.maintenance.bak.*' );
		if ( empty( $backups ) ) {
			return array(
				'success' => false,
				'message' => __( 'No maintenance backup found to restore.', 'wpshadow' ),
			);
		}

		rsort( $backups );
		$latest_backup = $backups[0];
		$maint_file    = ABSPATH . '.maintenance';

		if ( copy( $latest_backup, $maint_file ) ) {
			return array(
				'success' => true,
				'message' => __( 'Maintenance file restored from backup.', 'wpshadow' ),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'Failed to restore maintenance file.', 'wpshadow' ),
		);
	}
}
