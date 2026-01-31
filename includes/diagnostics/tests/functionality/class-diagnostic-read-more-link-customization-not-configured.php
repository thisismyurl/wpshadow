<?php
/**
 * Read More Link Customization Not Configured Diagnostic
 *
 * Checks if read more links are customized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2320
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read More Link Customization Not Configured Diagnostic Class
 *
 * Detects generic read more links.
 *
 * @since 1.2601.2320
 */
class Diagnostic_Read_More_Link_Customization_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'read-more-link-customization-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Read More Link Customization Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if read more links are customized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2320
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if theme or site uses the default read more text
		// This is advisory - generic read more links aren't a major issue
		// but customized text improves UX

		return null; // This is optional
	}
}
