<?php
/**
 * Treatment: Disable Autoload for Large Options
 *
 * Disables autoload for large serialized options.
 *
 * Philosophy: Helpful Neighbor (#1) - Offers choice with clear impact
 * KB Link: https://wpshadow.com/kb/large-serialized-options
 * Training: https://wpshadow.com/training/large-serialized-options
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Autoload for Large Options treatment
 */
class Treatment_Disable_Autoload_Large_Options extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = array() ): bool {
		global $wpdb;

		// Get large autoloaded serialized options
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uses wpdb table property, no user input
		$large_autoloaded = $wpdb->get_results(
			"SELECT 
				option_name,
				LENGTH(option_value) as size
			FROM {$wpdb->options}
			WHERE LENGTH(option_value) > 102400
			AND autoload = 'yes'
			AND (option_value LIKE 'a:%' OR option_value LIKE 'O:%')
			ORDER BY size DESC",
			ARRAY_A
		);

		if ( empty( $large_autoloaded ) ) {
			return false;
		}

		// Create backup
		$backup = array(
			'options'   => array_column( $large_autoloaded, 'option_name' ),
			'timestamp' => time(),
		);
		self::create_backup( $backup );

		// Disable autoload for each
		$updated = 0;
		foreach ( $large_autoloaded as $option ) {
			$result = $wpdb->update(
				$wpdb->options,
				array( 'autoload' => 'no' ),
				array( 'option_name' => $option['option_name'] ),
				array( '%s' ),
				array( '%s' )
			);

			if ( $result ) {
				++$updated;
			}
		}

		// Track KPI
		if ( $updated > 0 ) {
			$size_saved    = array_sum( array_column( $large_autoloaded, 'size' ) );
			$size_saved_kb = round( $size_saved / 1024, 2 );

			KPI_Tracker::record_treatment_applied( __CLASS__, 3 );
		}

		return $updated > 0;
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		global $wpdb;

		$backup = self::restore_backup();
		if ( ! $backup || empty( $backup['options'] ) ) {
			return false;
		}

		// Re-enable autoload for backed up options
		$restored = 0;
		foreach ( $backup['options'] as $option_name ) {
			$result = $wpdb->update(
				$wpdb->options,
				array( 'autoload' => 'yes' ),
				array( 'option_name' => $option_name ),
				array( '%s' ),
				array( '%s' )
			);

			if ( $result ) {
				++$restored;
			}
		}

		return $restored > 0;
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Disable Autoload for Large Options', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Disables autoload for large serialized options (> 100 KB). These options will be loaded on-demand instead of every page load. Can reduce page load time by 100-300ms. <a href="%s" target="_blank">Learn about autoload optimization</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/large-serialized-options'
		);
	}
}
