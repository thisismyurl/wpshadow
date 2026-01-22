<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Settings\Report_Scheduler;

/**
 * AJAX Handler: Update Report Schedule
 * 
 * Updates scheduled report configuration and cron jobs.
 * 
 * @since 1.2601
 * @package WPShadow
 */
class Update_Report_Schedule_Handler extends AJAX_Handler_Base {
	
	/**
	 * Register AJAX action
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_update_report_schedule', [ __CLASS__, 'handle' ] );
	}
	
	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		// Verify security
		self::verify_request( 'wpshadow_schedule_report_nonce', 'manage_options' );
		
		// Get schedule configuration
		$report_type = self::get_post_param( 'report_type', 'text', '', true );
		$enabled = (bool) self::get_post_param( 'enabled', 'text', 'false', false );
		$frequency = self::get_post_param( 'frequency', 'text', 'daily', false );
		$recipients = self::get_post_param( 'recipients', 'raw', '', false );
		$template = self::get_post_param( 'template', 'text', '', false );
		$include_recommendations = (bool) self::get_post_param( 'include_recommendations', 'text', 'false', false );
		
		// Parse recipients (comma-separated or JSON array)
		if ( is_string( $recipients ) ) {
			if ( '[' === $recipients[0] ?? '' ) {
				$recipients = json_decode( $recipients, true );
			} else {
				$recipients = array_map( 'trim', explode( ',', $recipients ) );
			}
		}
		
		// Build config array
		$config = [
			'enabled' => $enabled,
			'frequency' => $frequency,
			'recipients' => $recipients,
			'template' => $template,
			'include_recommendations' => $include_recommendations,
		];
		
		// Update schedule
		if ( class_exists( Report_Scheduler::class ) ) {
			if ( Report_Scheduler::validate_schedule_config( $config ) ) {
				Report_Scheduler::update_schedule( $report_type, $config );
				
				self::send_success( [
					'message' => __( 'Report schedule updated successfully', 'wpshadow' ),
					'report_type' => $report_type
				] );
			} else {
				self::send_error( __( 'Invalid schedule configuration', 'wpshadow' ) );
			}
		} else {
			self::send_error( __( 'Report scheduler not available', 'wpshadow' ) );
		}
	}
}
