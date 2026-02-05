<?php
/**
 * Form Validation Not Comprehensive Treatment
 *
 * Checks form validation.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Form_Validation_Not_Comprehensive Class
 *
 * Performs treatment check for Form Validation Not Comprehensive.
 *
 * @since 1.6033.2033
 */
class Treatment_Form_Validation_Not_Comprehensive extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-validation-not-comprehensive';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Validation Not Comprehensive';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks form validation';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('wp_head',
						'validate_all_forms' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Form validation not comprehensive. Validate all input on both client and server side using HTML5 validation and backend checks.',
						'severity'   =>   'medium',
						'threat_level'   =>   40,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/form-validation-not-comprehensive'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
