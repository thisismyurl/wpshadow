<?php
/**
 * Treatment: Enable year/month upload folders
 *
 * WordPress can organize uploads into year/month subdirectories. Keeping all
 * uploads in one flat directory scales poorly over time and makes manual file
 * management harder. This treatment enables the native uploads_use_yearmonth_folders option.
 *
 * Undo: restores the previous uploads_use_yearmonth_folders value.
 *
 * @package WPShadow
 * @since   0.7056
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enables year/month upload folders.
 */
class Treatment_Media_Year_Month_Folders_Enabled extends Treatment_Base {

	/** @var string */
	protected static $slug = 'media-year-month-folders-enabled';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Enable uploads_use_yearmonth_folders.
	 *
	 * @return array
	 */
	public static function apply(): array {
		return static::apply_option_with_backup(
			'uploads_use_yearmonth_folders',
			1,
			'wpshadow_uploads_yearmonth_prev',
			__( 'Year/month upload folders are already enabled. No changes made.', 'wpshadow' ),
			__( 'Year/month upload folders enabled. New uploads will be organized into dated subdirectories.', 'wpshadow' )
		);
	}

	/**
	 * Restore the previous uploads_use_yearmonth_folders value.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return static::restore_option_from_backup(
			'uploads_use_yearmonth_folders',
			'wpshadow_uploads_yearmonth_prev',
			__( 'No previous upload folder organization setting was stored.', 'wpshadow' ),
			static function ( $previous ): string {
				return (int) $previous
					? __( 'Year/month upload folders restored to enabled.', 'wpshadow' )
					: __( 'Year/month upload folders restored to disabled.', 'wpshadow' );
			}
		);
	}
}