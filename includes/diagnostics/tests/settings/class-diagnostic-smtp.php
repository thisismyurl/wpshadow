<?php
/**
 * SMTP Configured Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Smtp_Configured Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Smtp_extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'smtp';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'SMTP';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for SMTP';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check SMTP plugin configuration state.
	 *
	 * TODO Fix Plan:
	 * - Enable SMTP transport.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( WP_Settings::uses_smtp_plugin() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No SMTP or transactional email plugin was detected. WordPress uses PHP mail() by default, which is frequently blocked by hosting providers and flagged as spam. Install an SMTP plugin and configure a reliable mail provider to ensure emails (password resets, order confirmations, notifications) are delivered.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/smtp-configured',
			'details'      => array(
				'detected_plugin' => null,
				'note'            => __( 'Install WP Mail SMTP, FluentSMTP, Post SMTP Mailer, or a similar plugin to configure SMTP delivery.', 'wpshadow' ),
			),
		);
	}
}
