<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Data_Retention {

	private const RETENTION_POLICY_OPTION = 'wpshadow_retention_policies';

	private const PURGE_HOOK = 'wpshadow_data_retention_purge';

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'schedule_purge' ) );
		add_action( self::PURGE_HOOK, array( __CLASS__, 'execute_purge' ) );
	}

	public static function get_policies(): array {
		$defaults = array(
			'activity_logs'     => array(
				'enabled' => true,
				'days'    => 90,
				'label'   => __( 'Activity Logs', 'wpshadow' ),
			),
			'privacy_requests'  => array(
				'enabled' => true,
				'days'    => 180,
				'label'   => __( 'Privacy Requests', 'wpshadow' ),
			),
			'diagnostic_tokens' => array(
				'enabled' => true,
				'days'    => 30,
				'label'   => __( 'Diagnostic Tokens', 'wpshadow' ),
			),
			'error_logs'        => array(
				'enabled' => true,
				'days'    => 30,
				'label'   => __( 'Error Logs', 'wpshadow' ),
			),
			'user_sessions'     => array(
				'enabled' => true,
				'days'    => 7,
				'label'   => __( 'User Sessions', 'wpshadow' ),
			),
		);

		$saved = get_option( self::RETENTION_POLICY_OPTION, array() );

		return array_merge( $defaults, $saved );
	}

	public static function update_policy( string $data_type, bool $enabled, int $days ): bool {
		$policies = self::get_policies();

		if ( ! isset( $policies[ $data_type ] ) ) {
			return false;
		}

		$policies[ $data_type ]['enabled'] = $enabled;
		$policies[ $data_type ]['days']    = max( 1, $days );

		return update_option( self::RETENTION_POLICY_OPTION, $policies );
	}

	public static function schedule_purge(): void {
		if ( ! wp_next_scheduled( self::PURGE_HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::PURGE_HOOK );
		}
	}

	public static function execute_purge(): array {
		$policies = self::get_policies();
		$results  = array();

		foreach ( $policies as $data_type => $policy ) {
			if ( ! $policy['enabled'] ) {
				continue;
			}

			$purged = self::purge_data_type( $data_type, $policy['days'] );
			if ( $purged > 0 ) {
				$results[ $data_type ] = $purged;

				if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
					WPSHADOW_Activity_Logger::log(
						'info',
						sprintf(

							__( 'Purged %2$d old %1$s records (retention: %3$d days)', 'wpshadow' ),
							$policy['label'] ?? $data_type,
							$purged,
							$policy['days']
						),
						array(
							'data_type'      => $data_type,
							'items_purged'   => $purged,
							'retention_days' => $policy['days'],
						)
					);
				}
			}
		}

		return $results;
	}

	private static function purge_data_type( string $data_type, int $days ): int {
		global $wpdb;

		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		$count  = 0;

		switch ( $data_type ) {
			case 'activity_logs':

				$table = $wpdb->prefix . 'wpshadow_activity_log';
				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
					$count = (int) $wpdb->query(
						$wpdb->prepare(
							"DELETE FROM {$table} WHERE logged_at < %s",
							$cutoff
						)
					);
				}
				break;

			case 'privacy_requests':

				$count = (int) $wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}WPSHADOW_privacy_requests 
						WHERE status IN ('completed', 'denied') 
						AND updated_at < %s",
						$cutoff
					)
				);
				break;

			case 'diagnostic_tokens':

				$tokens = get_option( 'wpshadow_diagnostic_tokens', array() );
				$before = count( $tokens );
				$tokens = array_filter(
					$tokens,
					function ( $token ) use ( $cutoff ) {
						$created = isset( $token['created'] ) ? $token['created'] : 0;
						return gmdate( 'Y-m-d H:i:s', $created ) >= $cutoff;
					}
				);
				update_option( 'wpshadow_diagnostic_tokens', $tokens );
				$count = $before - count( $tokens );
				break;

			case 'error_logs':

				$error_log = ini_get( 'error_log' );
				if ( $error_log && file_exists( $error_log ) ) {
					$cutoff_timestamp = strtotime( "-{$days} days" );
					$lines            = file( $error_log );
					$kept_lines       = array();
					foreach ( $lines as $line ) {
						if ( preg_match( '/^\[(\d{2}-\w{3}-\d{4})/', $line, $matches ) ) {
							$log_time = strtotime( $matches[1] );
							if ( $log_time && $log_time >= $cutoff_timestamp ) {
								$kept_lines[] = $line;
							} else {
								++$count;
							}
						} else {
							$kept_lines[] = $line;
						}
					}
					if ( $count > 0 ) {
						file_put_contents( $error_log, implode( '', $kept_lines ) );
					}
				}
				break;

			case 'user_sessions':

				$count = (int) $wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->options} 
						WHERE option_name LIKE %s 
						AND option_name NOT LIKE %s",
						$wpdb->esc_like( '_transient_WPSHADOW_user_session_' ) . '%',
						$wpdb->esc_like( '_transient_timeout_WPSHADOW_user_session_' ) . '%'
					)
				);
				break;
		}

		return $count;
	}

	public static function get_status(): array {
		$policies = self::get_policies();
		$enabled  = count(
			array_filter(
				$policies,
				function ( $policy ) {
					return $policy['enabled'];
				}
			)
		);

		return array(
			'total_policies' => count( $policies ),
			'enabled'        => $enabled,
			'next_purge'     => wp_next_scheduled( self::PURGE_HOOK ),
		);
	}

	public static function manual_purge(): array {
		return self::execute_purge();
	}
}
