<?php
/**
 * Form Field Persistence Not Implemented Diagnostic
 *
 * Checks if form field persistence is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Field Persistence Not Implemented Diagnostic Class
 *
 * Detects missing form field persistence.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Form_Field_Persistence_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-field-persistence-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Field Persistence Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form field persistence is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if form field persistence is active
		if ( ! has_filter( 'wp_footer', 'enable_form_persistence' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Form field persistence is not implemented. Save form data in localStorage to recover incomplete entries if users navigate away.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/form-field-persistence-not-implemented',
			);
		}

		return null;
	}
}
