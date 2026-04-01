<?php
/**
 * DLP Rules Configured Diagnostic
 *
 * Checks if data loss prevention rules are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DLP Rules Configured Diagnostic Class
 *
 * Verifies that Data Loss Prevention (DLP) rules are properly
 * configured to prevent unauthorized data exfiltration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_DLP_Rules_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dlp-rules-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DLP Rules Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Data loss prevention rules are active';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the DLP rules check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if DLP not configured, null otherwise.
	 */
	public static function check() {
		$stats = array();
		$issues = array();

		// Check for DLP plugin.
		$dlp_enabled = false;
		$dlp_plugin = null;

		$dlp_plugins = array(
			'defender/defender.php' => 'Defender',
			'wordfence/wordfence.php' => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One Security',
		);

		foreach ( $dlp_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$dlp_plugin = $plugin_name;
				$dlp_enabled = true;
				$stats['dlp_plugin'] = $plugin_name;
				break;
			}
		}

		if ( ! $dlp_enabled ) {
			$issues[] = __( 'No DLP plugin detected', 'wpshadow' );
			$stats['dlp_enabled'] = false;
		} else {
			$stats['dlp_enabled'] = true;
		}

		// Check for DLP rules.
		$dlp_rules = get_option( 'wpshadow_dlp_rules', array() );
		$stats['dlp_rules_count'] = is_array( $dlp_rules ) ? count( $dlp_rules ) : 0;

		if ( empty( $dlp_rules ) && $dlp_enabled ) {
			$issues[] = __( 'DLP plugin detected but no rules configured', 'wpshadow' );
			$stats['rules_configured'] = false;
		} else {
			$stats['rules_configured'] = true;
		}

		// Check specific DLP rule categories.
		$rule_categories = array(
			'credit_card' => __( 'Credit card patterns', 'wpshadow' ),
			'ssn'         => __( 'Social Security Numbers', 'wpshadow' ),
			'api_keys'    => __( 'API keys and credentials', 'wpshadow' ),
			'pii'         => __( 'Personally identifiable information', 'wpshadow' ),
		);

		$rules_by_category = array();
		$missing_categories = array();

		foreach ( $rule_categories as $category => $label ) {
			$has_rule = isset( $dlp_rules[ $category ] ) && $dlp_rules[ $category ];
			$rules_by_category[ $category ] = $has_rule;

			if ( ! $has_rule ) {
				$missing_categories[] = $label;
			}
		}

		$stats['rules_by_category'] = $rules_by_category;

		if ( ! empty( $missing_categories ) ) {
			$issues[] = sprintf(
				/* translators: %s: categories */
				__( 'Missing DLP rules for: %s', 'wpshadow' ),
				implode( ', ', $missing_categories )
			);
		}

		// Check for DLP alerting.
		$dlp_alerts_enabled = get_option( 'wpshadow_dlp_alerts_enabled' );
		$stats['alerts_enabled'] = boolval( $dlp_alerts_enabled );

		if ( $dlp_enabled && ! $dlp_alerts_enabled ) {
			$issues[] = __( 'DLP alerting is not enabled - cannot respond to violations', 'wpshadow' );
		}

		// Check for DLP logging.
		$dlp_logging = get_option( 'wpshadow_dlp_logging_enabled' );
		$stats['logging_enabled'] = boolval( $dlp_logging );

		if ( $dlp_enabled && ! $dlp_logging ) {
			$issues[] = __( 'DLP logging is disabled - cannot audit violations', 'wpshadow' );
		}

		// Check DLP violation count.
		$recent_violations = get_option( 'wpshadow_dlp_violations_count', 0 );
		$stats['recent_violations'] = intval( $recent_violations );

		if ( $recent_violations > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of violations */
				__( '%d DLP violations detected - review policies', 'wpshadow' ),
				$recent_violations
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'DLP configuration issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dlp-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null; // DLP properly configured.
	}
}
