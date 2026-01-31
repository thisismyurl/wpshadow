<?php
/**
 * Form Submission Security Not Enhanced Diagnostic
 *
 * Checks if form submission is secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2351
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Submission Security Not Enhanced Diagnostic Class
 *
 * Detects insecure form submissions.
 *
 * @since 1.2601.2351
 */
class Diagnostic_Form_Submission_Security_Not_Enhanced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-submission-security-not-enhanced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Submission Security Not Enhanced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form submission is secured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2351
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for form security plugins
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'ninja-forms/ninja-forms.php',
			'wpforms-lite/wpforms.php',
		);

		$form_active = false;
		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$form_active = true;
				break;
			}
		}

		if ( ! $form_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Form submission security is not enhanced. Use reputable form plugins with validation and spam protection.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/form-submission-security-not-enhanced',
			);
		}

		return null;
	}
}
