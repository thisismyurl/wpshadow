<?php
/**
 * GDPR Data Export Not Implemented Diagnostic
 *
 * Checks GDPR export.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_GDPR_Data_Export_Not_Implemented Class
 *
 * Performs diagnostic check for Gdpr Data Export Not Implemented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_GDPR_Data_Export_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-data-export-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Data Export Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks GDPR export';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// WordPress 4.9.6+ has native GDPR tools.
		global $wp_version;
		$native_support = version_compare( $wp_version, '4.9.6', '>=' );

		// Check if WordPress privacy tools are accessible.
		$privacy_policy_page = get_option( 'wp_page_for_privacy_policy' );
		$has_privacy_page = ! empty( $privacy_policy_page );

		// Check for GDPR plugins with export functionality.
		$gdpr_plugins = array(
			'gdpr-data-request-form/gdpr-data-request-form.php' => 'GDPR Data Request Form',
			'wp-gdpr-compliance/wp-gdpr-compliance.php'         => 'WP GDPR Compliance',
			'gdpr-framework/gdpr-framework.php'                 => 'The GDPR Framework',
			'complianz-gdpr/complianz-gpdr.php'                 => 'Complianz',
		);

		$gdpr_plugin_detected = false;
		$gdpr_plugin_name     = '';

		foreach ( $gdpr_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$gdpr_plugin_detected = true;
				$gdpr_plugin_name     = $name;
				break;
			}
		}

		// Check if site collects user data (comments, registrations).
		$users_can_register = get_option( 'users_can_register' );
		$comments_enabled = get_option( 'default_comment_status' ) === 'open';
		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$collects_data = $users_can_register || $comments_enabled || $has_woocommerce;

		// Check site locale (EU sites require GDPR compliance).
		$site_locale = get_locale();
		$eu_locale = in_array( 
			substr( $site_locale, 0, 2 ), 
			array( 'de', 'fr', 'es', 'it', 'nl', 'pl', 'pt', 'sv', 'da', 'fi', 'no', 'cs', 'el', 'hu', 'ro', 'sk', 'bg', 'hr', 'lt', 'lv', 'et', 'sl', 'mt', 'cy', 'ga' ),
			true
		);

		// If no native support and no plugin and site collects data.
		if ( ! $native_support && ! $gdpr_plugin_detected && $collects_data ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'GDPR data export not implemented. Your site collects user data but users cannot export their data. GDPR (Article 20) requires data portability. Upgrade to WordPress 4.9.6+ for native GDPR tools, or install a GDPR compliance plugin.', 'wpshadow' ),
				'severity'    => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gdpr-compliance',
				'details'     => array(
					'wp_version'           => $wp_version,
					'native_support'       => false,
					'collects_data'        => true,
					'users_can_register'   => $users_can_register,
					'comments_enabled'     => $comments_enabled,
					'has_woocommerce'      => $has_woocommerce,
					'eu_locale'            => $eu_locale,
					'recommendation'       => __( 'URGENT: Upgrade WordPress to 4.9.6+ (current: 6.4+) for native GDPR tools. WordPress includes: Export Personal Data, Erase Personal Data, Privacy Policy page generator.', 'wpshadow' ),
					'gdpr_requirements'    => array(
						'article_15' => 'Right to access personal data',
						'article_17' => 'Right to erasure ("right to be forgotten")',
						'article_20' => 'Right to data portability',
						'article_21' => 'Right to object to processing',
					),
					'penalties'            => array(
						'gdpr_tier_1' => '€10 million or 2% annual revenue',
						'gdpr_tier_2' => '€20 million or 4% annual revenue',
					),
				),
			);
		}

		// If native support but no privacy page configured.
		if ( $native_support && ! $has_privacy_page && $collects_data ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Privacy Policy Page Not Configured', 'wpshadow' ),
				'description' => __( 'WordPress GDPR tools available but Privacy Policy page not set. Go to Settings → Privacy to create and assign a privacy policy page. This page is required for GDPR compliance.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gdpr-compliance',
				'details'     => array(
					'native_support'   => true,
					'has_privacy_page' => false,
					'recommendation'   => __( 'Go to Settings → Privacy → Create New Page. WordPress will generate a template privacy policy for you to customize.', 'wpshadow' ),
				),
			);
		}

		// No issues - GDPR tools available and configured.
		return null;
	}
}
