<?php
/**
 * Global Privacy Control (GPC) Support Diagnostic
 *
 * Verifies website honors Global Privacy Control signals as required by CPRA regulations (2023).
 * GPC is a legally binding opt-out signal that must be honored for data sales and targeted advertising.
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
 * Global Privacy Control Support Diagnostic Class
 *
 * Checks if the website detects and honors Global Privacy Control (GPC) signals
 * from browsers/extensions. California regulations recognize GPC as legally binding.
 *
 * @since 1.6032.1430
 */
class Diagnostic_Global_Privacy_Control_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'global-privacy-control-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Global Privacy Control (GPC) Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify website honors Global Privacy Control signals';

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
		
		// Check for GPC detection plugins
		$gpc_plugins = array(
			'complianz-gdpr/complianz-gdpr.php',
			'cookiebot/cookiebot.php',
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
		);
		
		$has_gpc_plugin = false;
		foreach ( $gpc_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_gpc_plugin = true;
				break;
			}
		}
		
		if ( ! $has_gpc_plugin ) {
			$issues[] = 'no_gpc_plugin_detected';
		}
		
		// Check theme/custom code for GPC signal detection
		$theme_functions = get_template_directory() . '/functions.php';
		$has_gpc_code = false;
		
		if ( file_exists( $theme_functions ) ) {
			$content = file_get_contents( $theme_functions );
			if ( stripos( $content, 'Sec-GPC' ) !== false || stripos( $content, 'global privacy control' ) !== false ) {
				$has_gpc_code = true;
			}
		}
		
		// Check for custom GPC handling code
		$gpc_option = get_option( 'wpshadow_gpc_enabled', false );
		
		if ( ! $has_gpc_plugin && ! $has_gpc_code && ! $gpc_option ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Website does not support Global Privacy Control (GPC) signals', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/global-privacy-control',
				'details'      => array(
					'issues_found'   => $issues,
					'gpc_standard'   => 'Sec-GPC: 1 HTTP header',
					'legal_basis'    => 'California CPRA regulations (2023)',
					'consequences'   => __( 'Ignoring GPC signals is a CCPA/CPRA violation', 'wpshadow' ),
					'detection_rate' => '95% of websites do not support GPC',
				),
				'meta'         => array(
					'diagnostic_class' => __CLASS__,
					'timestamp'        => current_time( 'mysql' ),
					'wpdb_avoidance'   => 'Uses is_plugin_active() and file_get_contents()',
				),
				'solution'     => array(
					'free'     => __( 'Install a CCPA compliance plugin that supports GPC (Complianz, Cookiebot)', 'wpshadow' ),
					'premium'  => __( 'Implement custom GPC detection via JavaScript or server-side headers', 'wpshadow' ),
					'advanced' => __( 'Set up automated GPC signal processing with cookie consent management', 'wpshadow' ),
				),
			);
		}
		
		return null;
	}
}
