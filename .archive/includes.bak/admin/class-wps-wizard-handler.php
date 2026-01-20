<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Wizard_Handler {

	public static function init(): void {
		add_action( 'wp_ajax_wpshadow_wizard_save_feature', array( __CLASS__, 'ajax_save_feature' ) );
		add_action( 'wp_ajax_wpshadow_wizard_complete', array( __CLASS__, 'ajax_complete_wizard' ) );
		add_action( 'wp_ajax_wpshadow_wizard_dismiss', array( __CLASS__, 'ajax_dismiss_wizard' ) );
		add_action( 'wp_ajax_wpshadow_wizard_reset', array( __CLASS__, 'ajax_reset_wizard' ) );
	}

	public static function ajax_save_feature(): void {
		check_ajax_referer( 'wpshadow_wizard_save', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';
		$enabled    = isset( $_POST['enabled'] ) && '1' === $_POST['enabled'];

		if ( empty( $feature_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid feature ID', 'wpshadow' ) ) );
		}

		$enabled_features = get_option( 'wpshadow_enabled_features', array() );
		if ( ! is_array( $enabled_features ) ) {
			$enabled_features = array();
		}

		if ( $enabled ) {
			if ( ! in_array( $feature_id, $enabled_features, true ) ) {
				$enabled_features[] = $feature_id;
			}
		} else {
			$enabled_features = array_diff( $enabled_features, array( $feature_id ) );
		}

		update_option( 'wpshadow_enabled_features', array_values( $enabled_features ) );

		wp_send_json_success( array( 'message' => __( 'Feature saved', 'wpshadow' ) ) );
	}

	public static function ajax_complete_wizard(): void {
		check_ajax_referer( 'wpshadow_wizard_complete', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'wpshadow_setup_wizard_completed', true );
		delete_user_meta( $user_id, 'wpshadow_setup_wizard_dismissed' );

		wp_send_json_success( array( 'message' => __( 'Setup wizard completed', 'wpshadow' ) ) );
	}

	public static function ajax_dismiss_wizard(): void {
		check_ajax_referer( 'wpshadow_wizard_dismiss', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'wpshadow_setup_wizard_dismissed', true );
		delete_user_meta( $user_id, 'wpshadow_setup_wizard_completed' );

		wp_send_json_success( array( 'message' => __( 'Setup wizard dismissed', 'wpshadow' ) ) );
	}

	public static function ajax_reset_wizard(): void {
		check_ajax_referer( 'wpshadow_wizard_reset', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$user_id = get_current_user_id();
		delete_user_meta( $user_id, 'wpshadow_setup_wizard_completed' );
		delete_user_meta( $user_id, 'wpshadow_setup_wizard_dismissed' );

		wp_send_json_success( array( 'message' => __( 'Setup wizard reset', 'wpshadow' ) ) );
	}
}
