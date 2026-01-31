<?php
/**
 * Contact Form Security Not Implemented Diagnostic
 *
 * Checks if contact forms are secure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2345
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Form Security Not Implemented Diagnostic Class
 *
 * Detects insecure contact forms.
 *
 * @since 1.2601.2345
 */
class Diagnostic_Contact_Form_Security_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'contact-form-security-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Contact Form Security Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if contact forms are secure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for contact form plugins
		$contact_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'jetpack/jetpack.php',
			'wpforms-lite/wpforms.php',
		);

		$contact_active = false;
		foreach ( $contact_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$contact_active = true;
				break;
			}
		}

		if ( ! $contact_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Contact forms are not using security plugins. Use reputable contact form plugins with spam protection and CAPTCHA.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/contact-form-security-not-implemented',
			);
		}

		return null;
	}
}
