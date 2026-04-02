<?php
/**
 * SMTP Configured Diagnostic
 *
 * Checks whether an SMTP or transactional email plugin is active to ensure
 * outgoing emails are reliably delivered rather than relying on PHP mail().
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Smtp Class
 *
 * Uses WP_Settings::uses_smtp_plugin() to detect recognised SMTP/transactional
 * email plugins and returns a medium-severity finding when none are active.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Smtp extends Diagnostic_Base {

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
	protected static $description = 'Checks whether an SMTP or transactional email plugin is active to ensure outgoing emails are reliably delivered rather than relying on PHP mail().';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Calls WP_Settings::uses_smtp_plugin() to check whether a recognised SMTP
	 * or transactional email plugin is active. Returns null when one is found.
	 * Returns a medium-severity finding when PHP mail() is the only mail
	 * transport available.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when no SMTP plugin is active, null when healthy.
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
			'kb_link'      => 'https://wpshadow.com/kb/smtp-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'detected_plugin' => null,
				'note'            => __( 'Install WP Mail SMTP, FluentSMTP, Post SMTP Mailer, or a similar plugin to configure SMTP delivery.', 'wpshadow' ),
			),
		);
	}
}
