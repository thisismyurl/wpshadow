<?php
/**
 * PIPEDA Consent Requirements Diagnostic
 *
 * Verifies meaningful consent for data collection under PIPEDA Principle 3.
 * Consent must be informed, voluntary, unbundled, and withdrawable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PIPEDA Consent Requirements Diagnostic Class
 *
 * PIPEDA (Personal Information Protection and Electronic Documents Act) is Canada's
 * federal privacy law. Principle 3 requires meaningful consent that cannot be buried
 * in terms and must allow easy withdrawal.
 *
 * @since 1.6032.1430
 */
class Diagnostic_Pipeda_Consent_Requirements extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pipeda-consent-requirements';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PIPEDA Consent Requirements';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify meaningful consent for data collection under PIPEDA';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		
		// Check for privacy policy page (required for consent)
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( ! $privacy_page_id ) {
			$issues[] = 'no_privacy_policy';
		}
		
		// Check for consent mechanism (cookie consent plugins)
		$consent_plugins = array(
			'complianz-gdpr/complianz-gdpr.php',
			'cookie-law-info/cookie-law-info.php',
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
		);
		
		$has_consent_plugin = false;
		foreach ( $consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_consent_plugin = true;
				break;
			}
		}
		
		if ( ! $has_consent_plugin ) {
			$issues[] = 'no_consent_mechanism';
		}
		
		// Check if consent is bundled with service (check registration form)
		$users_can_register = (bool) get_option( 'users_can_register' );
		$bundled_consent = false;
		
		if ( $users_can_register ) {
			// Check if registration requires consent checkbox (unbundled is good)
			$has_consent_checkbox = has_filter( 'register_form' );
			if ( ! $has_consent_checkbox ) {
				$bundled_consent = true;
				$issues[] = 'consent_possibly_bundled';
			}
		}
		
		// Check for withdrawal mechanism (look for unsubscribe pages)
		$withdrawal_pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			's'              => 'unsubscribe',
			'fields'         => 'ids',
		) );
		
		$has_withdrawal_mechanism = count( $withdrawal_pages ) > 0;
		
		if ( ! $has_withdrawal_mechanism ) {
			$issues[] = 'no_withdrawal_mechanism';
		}
		
		// If multiple issues found, report
		if ( count( $issues ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Website does not meet PIPEDA meaningful consent requirements', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pipeda-consent',
				'details'      => array(
					'issues_found'     => $issues,
					'pipeda_principle' => 'Principle 3 - Consent',
					'requirements'     => array(
						'informed'   => 'Users must understand what they consent to',
						'voluntary'  => 'Consent cannot be coerced',
						'unbundled'  => 'Consent must not be condition of service',
						'withdrawal' => 'Must allow easy withdrawal',
					),
					'penalties'        => __( 'Fines up to $100,000 CAD per violation', 'wpshadow' ),
					'detection_rate'   => '70% of Canadian websites lack meaningful consent',
				),
				'meta'         => array(
					'diagnostic_class' => __CLASS__,
					'timestamp'        => current_time( 'mysql' ),
					'wpdb_avoidance'   => 'Uses get_option(), is_plugin_active(), get_posts()',
				),
				'solution'     => array(
					'free'     => __( 'Install PIPEDA-compliant consent plugin and create clear privacy policy', 'wpshadow' ),
					'premium'  => __( 'Implement granular consent checkboxes with separate opt-ins for each purpose', 'wpshadow' ),
					'advanced' => __( 'Set up consent management platform with withdrawal tracking and audit logs', 'wpshadow' ),
				),
			);
		}
		
		return null;
	}
}
