<?php
/**
 * Form Builder File Upload Integration Treatment
 *
 * Provides treatment mapping for form builder file upload diagnostics.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Builder File Upload Integration Treatment Class
 *
 * @since 1.6093.1200
 */
class Treatment_Form_Builder_File_Upload_Integration extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'form-builder-file-upload-integration';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Form Builder File Upload Integration';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests file uploads in form builders and validates media library integration';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'integrations';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Form_Builder_File_Upload_Integration' );
	}
}
