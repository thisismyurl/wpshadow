<?php
/**
 * Canadian Data Breach Notification Diagnostic
 *
 * Ensures compliance with mandatory breach reporting to OPC (Office of the Privacy Commissioner)
 * and affected individuals under PIPEDA breach notification requirements (mandatory since 2018).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Breach Notification Canada Diagnostic Class
 *
 * PIPEDA requires mandatory breach notification to OPC and individuals if risk
 * of significant harm exists. Records must be kept for 24 months.
 *
 * @since 1.6032.1530
 */
class Diagnostic_Data_Breach_Notification_Canada extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-breach-notification-canada';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Breach Notification (Canada)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure compliance with mandatory breach reporting to OPC and individuals';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1530
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		
		// Check for breach notification plugins/systems
		$breach_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
			'ithemes-security-pro/ithemes-security-pro.php',
		);
		
		$has_breach_detection = false;
		foreach ( $breach_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_breach_detection = true;
				break;
			}
		}
		
		if ( ! $has_breach_detection ) {
			$issues[] = 'no_breach_detection_system';
		}
		
		// Check for breach response plan documentation
		$breach_pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'private' ),
			'posts_per_page' => 20,
			's'              => 'breach',
			'fields'         => 'ids',
		) );
		
		$has_breach_plan = false;
		foreach ( $breach_pages as $page_id ) {
			$content = strtolower( get_post_field( 'post_content', $page_id ) );
			if ( stripos( $content, 'breach response' ) !== false ||
				 stripos( $content, 'incident response' ) !== false ||
				 stripos( $content, 'breach notification' ) !== false ) {
				$has_breach_plan = true;
				break;
			}
		}
		
		if ( ! $has_breach_plan ) {
			$issues[] = 'no_breach_response_plan';
		}
		
		// Check for OPC contact info in privacy policy
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		$has_opc_info = false;
		
		if ( $privacy_page_id ) {
			$privacy_page = get_post( $privacy_page_id );
			if ( $privacy_page ) {
				$content = strtolower( $privacy_page->post_content );
				if ( stripos( $content, 'privacy commissioner' ) !== false ||
					 stripos( $content, 'opc' ) !== false ||
					 stripos( $content, 'priv.gc.ca' ) !== false ) {
					$has_opc_info = true;
				}
			}
		}
		
		if ( ! $has_opc_info ) {
			$issues[] = 'no_opc_contact_info';
		}
		
		// Check for breach record keeping (look for custom table or option)
		$breach_records_option = get_option( 'wpshadow_breach_records', array() );
		$has_breach_records = ! empty( $breach_records_option );
		
		if ( ! $has_breach_records ) {
			$issues[] = 'no_breach_record_keeping';
		}
		
		if ( count( $issues ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Website lacks PIPEDA-compliant breach notification procedures', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pipeda-breach-notification',
				'details'      => array(
					'issues_found'     => $issues,
					'pipeda_law'       => 'Mandatory breach notification since November 1, 2018',
					'reporting_deadline' => 'As soon as feasible to OPC and affected individuals',
					'record_keeping'   => '24 months minimum',
					'penalties'        => __( 'Fines up to $100,000 CAD for failure to report', 'wpshadow' ),
					'risk_threshold'   => 'Risk of significant harm to individuals',
					'detection_rate'   => '70% of Canadian organizations lack breach procedures',
				),
				'meta'         => array(
					'diagnostic_class' => __CLASS__,
					'timestamp'        => current_time( 'mysql' ),
					'wpdb_avoidance'   => 'Uses get_posts(), get_post_field(), get_option()',
				),
				'solution'     => array(
					'free'     => __( 'Install security plugin with breach detection and create breach response plan', 'wpshadow' ),
					'premium'  => __( 'Implement automated breach notification system with OPC reporting templates', 'wpshadow' ),
					'advanced' => __( 'Set up breach detection, risk assessment workflow, and 24-month record retention system', 'wpshadow' ),
				),
			);
		}
		
		return null;
	}
}
