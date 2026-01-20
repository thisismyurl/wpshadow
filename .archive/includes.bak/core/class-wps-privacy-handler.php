<?php

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Privacy_Handler {

	public static function init(): void {

		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporters' ) );

		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_erasers' ) );
	}

	public static function register_exporters( array $exporters ): array {
		$exporters['wpshadow-core'] = array(
			'exporter_friendly_name' => __( 'WPShadow Plugin Data', 'wpshadow' ),
			'callback'               => array( __CLASS__, 'export_user_data' ),
		);

		return $exporters;
	}

	public static function register_erasers( array $erasers ): array {
		$erasers['wpshadow-core'] = array(
			'eraser_friendly_name' => __( 'WPShadow Plugin Data', 'wpshadow' ),
			'callback'             => array( __CLASS__, 'erase_user_data' ),
		);

		return $erasers;
	}

	public static function export_user_data( string $email_address, int $page = 1 ): array {
		$user = get_user_by( 'email', $email_address );

		if ( ! $user ) {
			return array(
				'success' => false,
				'data'    => array(),
			);
		}

		$items = array();

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

		$items = apply_filters( 'wpshadow_privacy_export_user_data', $items, $user->ID );

		return array(
			'success' => true,
			'data'    => $items,
		);
	}

	private static function export_feature_logs( int $user_id ): array {
		$data = array();
		$all_logs = get_option( 'wpshadow_feature_logs', array() );

		if ( empty( $all_logs ) || ! is_array( $all_logs ) ) {
			return $data;
		}

		foreach ( $all_logs as $feature_id => $logs ) {
			if ( ! is_array( $logs ) ) {
				continue;
			}

			foreach ( $logs as $log ) {

				if ( isset( $log['user_id'] ) && intval( $log['user_id'] ) === $user_id ) {
					$timestamp = isset( $log['timestamp'] ) ? intval( $log['timestamp'] ) : 0;
					$action = isset( $log['action'] ) ? sanitize_text_field( $log['action'] ) : '';
					$message = isset( $log['details'] ) ? sanitize_text_field( $log['details'] ) : '';

					$data[] = array(
						'name'  => sprintf(

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

	private static function export_feature_access( int $user_id ): array {
		$data = array();

		$accessed = get_user_meta( $user_id, 'wpshadow_accessed_features', true );

		if ( empty( $accessed ) || ! is_array( $accessed ) ) {
			return $data;
		}

		foreach ( $accessed as $feature_id => $count ) {
			$data[] = array(
				'name'  => sprintf(

					__( 'Feature Access Count: %s', 'wpshadow' ),
					esc_html( $feature_id )
				),
				'value' => intval( $count ) . ' times',
			);
		}

		return $data;
	}

	private static function export_user_sessions( int $user_id ): array {
		$data = array();

		$session_key = 'wpshadow_session_' . $user_id;
		$session = get_transient( $session_key );

		if ( ! $session || ! is_array( $session ) ) {
			return $data;
		}

		$session_summary = wp_json_encode( $session, JSON_PRETTY_PRINT );

		if ( $session_summary ) {
			$data[] = array(
				'name'  => __( 'Active Session Data', 'wpshadow' ),
				'value' => $session_summary,
			);
		}

		return $data;
	}

	public static function erase_user_data( string $email_address, int $page = 1 ): array {
		$user = get_user_by( 'email', $email_address );

		if ( ! $user ) {
			return array(
				'success'       => false,
				'items_removed' => false,
			);
		}

		$items_removed = false;

		if ( self::erase_feature_logs( $user->ID ) ) {
			$items_removed = true;
		}

		if ( self::erase_feature_access( $user->ID ) ) {
			$items_removed = true;
		}

		if ( self::erase_user_sessions( $user->ID ) ) {
			$items_removed = true;
		}

		do_action( 'wpshadow_privacy_erase_user_data', $user->ID );

		return array(
			'success'        => true,
			'items_removed'  => $items_removed,
		);
	}

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

	private static function erase_feature_access( int $user_id ): bool {
		$accessed = get_user_meta( $user_id, 'wpshadow_accessed_features', true );

		if ( ! empty( $accessed ) ) {
			delete_user_meta( $user_id, 'wpshadow_accessed_features' );
			return true;
		}

		return false;
	}

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
