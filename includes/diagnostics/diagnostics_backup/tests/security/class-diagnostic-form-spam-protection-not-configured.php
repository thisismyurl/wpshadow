<?php
/**
 * Form Spam Protection Not Configured Diagnostic
 *
 * Checks if spam protection is configured for forms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Spam Protection Not Configured Diagnostic Class
 *
 * Detects missing form spam protection.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Form_Spam_Protection_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-spam-protection-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Spam Protection Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form spam protection is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for form plugins
		$form_plugins = array(
			'wpforms-lite/wpforms.php',
			'contact-form-7/wp-contact-form-7.php',
			'formidable/formidable.php',
			'gravity-forms/gravityforms.php',
		);

		$form_active = false;
		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$form_active = true;
				break;
			}
		}

		if ( ! $form_active ) {
			return null; // No forms detected
		}

		// Check for spam protection plugins
		$spam_plugins = array(
			'akismet/akismet.php',
			'antispam-bee/antispam-bee.php',
			'wordfence/wordfence.php',
		);

		$spam_active = false;
		foreach ( $spam_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$spam_active = true;
				break;
			}
		}

		if ( ! $spam_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Forms are active but no spam protection plugin is configured. Unprotected forms can be targeted by spammers.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/form-spam-protection-not-configured',
			);
		}

		return null;
	}
}
