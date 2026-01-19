<?php
/**
 * WPShadow Privacy & GDPR Handler
 *
 * Integrates with WordPress Core's Personal Data Export and Erase API
 * to support GDPR compliance and user privacy requests.
 *
 * @package WPShadow\Core
 * @since 1.2601.75001
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Privacy handler for WordPress personal data export/erase integration
 */
final class WPSHADOW_Privacy_Handler {

	/**
	 * Initialize privacy hooks.
	 */
	public static function init(): void {
		// Register exporters for data collection
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporters' ) );

		// Register erasers for data removal
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_erasers' ) );
	}

	/**
	 * Register personal data exporters.
	 *
	 * Registers callbacks that collect personal data for export when a user
	 * requests their data via WordPress's privacy tools.
	 *
	 * @param array $exporters Array of registered exporters.
	 * @return array Modified array with WPShadow exporters.
	 */
	public static function register_exporters( array $exporters ): array {
		$exporters['wpshadow-core'] = array(
			'exporter_friendly_name' => __( 'WPShadow Plugin Data', 'wpshadow' ),
			'callback'               => array( __CLASS__, 'export_user_data' ),
		);

		return $exporters;
	}

	/**
	 * Register personal data erasers.
	 *
	 * Registers callbacks that remove or anonymize personal data when a user
	 * requests erasure via WordPress's privacy tools.
	 *
	 * @param array $erasers Array of registered erasers.
	 * @return array Modified array with WPShadow erasers.
	 */
	public static function register_erasers( array $erasers ): array {
		$erasers['wpshadow-core'] = array(
			'eraser_friendly_name' => __( 'WPShadow Plugin Data', 'wpshadow' ),
			'callback'             => array( __CLASS__, 'erase_user_data' ),
		);

		return $erasers;
	}

	/**
	 * Export user personal data.
	 *
	 * Collects all personal data associated with a user from WPShadow,
	 * including activity logs, sessions, and feature access tracking.
	 *
	 * @param string $email_address Email address of the user to export data for.
	 * @param int    $page         Page number for pagination (1-based).
	 * @return array {
	 *     Array with export success status and personal data.
	 *
	 *     @type bool   $success Whether the export succeeded.
	 *     @type array  $data    Array of personal data items to export.
	 * }
	 */
	public static function export_user_data( string $email_address, int $page = 1 ): array {
		$user = get_user_by( 'email', $email_address );

		if ( ! $user ) {
			return array(
				'success' => false,
				'data'    => array(),
			);
		}

		$items = array();

		// Export feature activity logs
		$activity_items = self::export_feature_logs( $user->ID );
		if ( ! empty( $activity_items ) ) {
			$items[] = array(
				'group_id'          => 'wpshadow-feature-logs',
				'group_label'       => __( 'Feature Activity Logs', 'wpshadow' ),
				'group_description' => __( 'Records of WPShadow feature usage and configuration changes.', 'wpshadow' ),
				'item_id'           => 'user-feature-logs',
				'data'              => $activity_items,
			);
		}

		// Export feature access tracking
		$access_items = self::export_feature_access( $user->ID );
		if ( ! empty( $access_items ) ) {
			$items[] = array(
				'group_id'          => 'wpshadow-feature-access',
				'group_label'       => __( 'Feature Access History', 'wpshadow' ),
				'group_description' => __( 'Records of which WPShadow features you have accessed.', 'wpshadow' ),
				'item_id'           => 'user-feature-access',
				'data'              => $access_items,
			);
		}

		// Export session data
		$session_items = self::export_user_sessions( $user->ID );
		if ( ! empty( $session_items ) ) {
			$items[] = array(
				'group_id'          => 'wpshadow-sessions',
				'group_label'       => __( 'User Sessions', 'wpshadow' ),
				'group_description' => __( 'Temporary session data used to maintain your WPShadow preferences during your visit.', 'wpshadow' ),
				'item_id'           => 'user-sessions',
				'data'              => $session_items,
			);
		}

		/**
		 * Filter exported WPShadow user data.
		 *
		 * Allows add-ons and plugins to contribute additional personal data
		 * to the export for this user.
		 *
		 * @param array $items Array of exported data items.
		 * @param int   $user_id User ID.
		 */
		$items = apply_filters( 'wpshadow_privacy_export_user_data', $items, $user->ID );

		return array(
			'success' => true,
			'data'    => $items,
		);
	}

	/**
	 * Export feature activity logs for user.
	 *
	 * @param int $user_id User ID.
	 * @return array Personal data entries.
	 */
	private static function export_feature_logs( int $user_id ): array {
		$data = array();
		$all_logs = get_option( 'wpshadow_feature_logs', array() );

		if ( empty( $all_logs ) || ! is_array( $all_logs ) ) {
			return $data;
		}

		// Iterate through all feature logs
		foreach ( $all_logs as $feature_id => $logs ) {
			if ( ! is_array( $logs ) ) {
				continue;
			}

			foreach ( $logs as $log ) {
				// Check if this log entry belongs to the user
				if ( isset( $log['user_id'] ) && intval( $log['user_id'] ) === $user_id ) {
					$timestamp = isset( $log['timestamp'] ) ? intval( $log['timestamp'] ) : 0;
					$action = isset( $log['action'] ) ? sanitize_text_field( $log['action'] ) : '';
					$message = isset( $log['details'] ) ? sanitize_text_field( $log['details'] ) : '';

					$data[] = array(
						'name'  => sprintf(
							/* translators: %s is the feature ID */
							__( 'Feature Activity: %s', 'wpshadow' ),
							esc_html( $feature_id )
						),
						'value' => wp_sprintf(
							'%1$s - %2$s%3$s',
							$action ? ucfirst( $action ) : 'Activity',
							gmdate( 'Y-m-d H:i:s', $timestamp ),
							$message ? ' (' . $message . ')' : ''
						),
					);
				}
			}
		}

		return $data;
	}

	/**
	 * Export feature access history for user.
	 *
	 * @param int $user_id User ID.
	 * @return array Personal data entries.
	 */
	private static function export_feature_access( int $user_id ): array {
		$data = array();

		// Get user meta for feature access tracking
		$accessed = get_user_meta( $user_id, 'wpshadow_accessed_features', true );

		if ( empty( $accessed ) || ! is_array( $accessed ) ) {
			return $data;
		}

		foreach ( $accessed as $feature_id => $count ) {
			$data[] = array(
				'name'  => sprintf(
					/* translators: %s is the feature ID */
					__( 'Feature Access Count: %s', 'wpshadow' ),
					esc_html( $feature_id )
				),
				'value' => intval( $count ) . ' times',
			);
		}

		return $data;
	}

	/**
	 * Export user session data.
	 *
	 * @param int $user_id User ID.
	 * @return array Personal data entries.
	 */
	private static function export_user_sessions( int $user_id ): array {
		$data = array();

		// Get session data from transient
		$session_key = 'wpshadow_session_' . $user_id;
		$session = get_transient( $session_key );

		if ( ! $session || ! is_array( $session ) ) {
			return $data;
		}

		// Create summary of session data
		$session_summary = wp_json_encode( $session, JSON_PRETTY_PRINT );

		if ( $session_summary ) {
			$data[] = array(
				'name'  => __( 'Active Session Data', 'wpshadow' ),
				'value' => $session_summary,
			);
		}

		return $data;
	}

	/**
	 * Erase user personal data.
	 *
	 * Removes or anonymizes all personal data associated with a user from WPShadow,
	 * including activity logs, sessions, and feature access tracking.
	 *
	 * @param string $email_address Email address of the user to erase data for.
	 * @param int    $page         Page number for pagination (1-based).
	 * @return array {
	 *     Array with erase success status and item count.
	 *
	 *     @type bool $success Whether the erase succeeded.
	 *     @type bool $items_removed Whether any items were removed.
	 * }
	 */
	public static function erase_user_data( string $email_address, int $page = 1 ): array {
		$user = get_user_by( 'email', $email_address );

		if ( ! $user ) {
			return array(
				'success'       => false,
				'items_removed' => false,
			);
		}

		$items_removed = false;

		// Erase feature activity logs
		if ( self::erase_feature_logs( $user->ID ) ) {
			$items_removed = true;
		}

		// Erase feature access tracking
		if ( self::erase_feature_access( $user->ID ) ) {
			$items_removed = true;
		}

		// Erase user sessions
		if ( self::erase_user_sessions( $user->ID ) ) {
			$items_removed = true;
		}

		/**
		 * Action hook for add-ons to erase their user data.
		 *
		 * @param int $user_id User ID for which data is being erased.
		 */
		do_action( 'wpshadow_privacy_erase_user_data', $user->ID );

		return array(
			'success'        => true,
			'items_removed'  => $items_removed,
		);
	}

	/**
	 * Erase feature activity logs for user.
	 *
	 * Anonymizes log entries associated with the user by replacing
	 * user_id with 0 and username with 'deleted'.
	 *
	 * @param int $user_id User ID.
	 * @return bool Whether any items were removed.
	 */
	private static function erase_feature_logs( int $user_id ): bool {
		$all_logs = get_option( 'wpshadow_feature_logs', array() );

		if ( empty( $all_logs ) || ! is_array( $all_logs ) ) {
			return false;
		}

		$modified = false;

		foreach ( $all_logs as $feature_id => $logs ) {
			if ( ! is_array( $logs ) ) {
				continue;
			}

			foreach ( $all_logs[ $feature_id ] as $index => $log ) {
				if ( isset( $log['user_id'] ) && intval( $log['user_id'] ) === $user_id ) {
					// Anonymize the log entry
					$all_logs[ $feature_id ][ $index ]['user_id'] = 0;
					$all_logs[ $feature_id ][ $index ]['user'] = 'deleted';
					$modified = true;
				}
			}
		}

		if ( $modified ) {
			update_option( 'wpshadow_feature_logs', $all_logs );
			return true;
		}

		return false;
	}

	/**
	 * Erase feature access tracking for user.
	 *
	 * @param int $user_id User ID.
	 * @return bool Whether any items were removed.
	 */
	private static function erase_feature_access( int $user_id ): bool {
		$accessed = get_user_meta( $user_id, 'wpshadow_accessed_features', true );

		if ( ! empty( $accessed ) ) {
			delete_user_meta( $user_id, 'wpshadow_accessed_features' );
			return true;
		}

		return false;
	}

	/**
	 * Erase user session data.
	 *
	 * @param int $user_id User ID.
	 * @return bool Whether any items were removed.
	 */
	private static function erase_user_sessions( int $user_id ): bool {
		$session_key = 'wpshadow_session_' . $user_id;
		$session = get_transient( $session_key );

		if ( $session ) {
			delete_transient( $session_key );
			return true;
		}

		return false;
	}
}
