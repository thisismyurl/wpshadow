<?php
/**
 * Search Form Accessible Name Diagnostic
 *
 * WordPress core's default search form includes a visible <label> for the
 * search input. Themes that override searchform.php sometimes drop the
 * label entirely, leaving the input anonymous to screen readers. This
 * diagnostic detects that pattern and flags it.
 *
 * @package WPShadow
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
 * Diagnostic_Search_Form_Accessible_Name Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Search_Form_Accessible_Name extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'search-form-accessible-name';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Search Form Accessible Name';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the theme\'s search form template provides an accessible name for the search input via a label, aria-label, or aria-labelledby attribute.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * If the active theme (or parent theme) provides a custom searchform.php,
	 * this method reads it and looks for a <label> element, an aria-label, or
	 * an aria-labelledby attribute that names the search input. If none are
	 * found the diagnostic fires. Sites using the WordPress default form pass
	 * automatically because it includes a screen-reader-visible label.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// Locate the searchform template, child theme first.
		$template_file = '';
		$candidates    = array(
			get_stylesheet_directory() . '/searchform.php',
			get_template_directory()   . '/searchform.php',
		);

		foreach ( $candidates as $candidate ) {
			if ( file_exists( $candidate ) ) {
				$template_file = $candidate;
				break;
			}
		}

		// No custom template - WordPress default form is used, which is accessible.
		if ( '' === $template_file ) {
			return null;
		}

		$content = file_get_contents( $template_file );
		if ( false === $content ) {
			return null;
		}

		// Pass if any of the standard accessible-name patterns are present.
		$has_accessible_name =
			preg_match( '/<label[^>]*for\s*=\s*[\'"]/i', $content )        // explicit <label for>
			|| preg_match( '/<label[^>]*>/i', $content )                   // implicit <label>
			|| preg_match( '/aria-label\s*=/i', $content )                 // aria-label attribute
			|| preg_match( '/aria-labelledby\s*=/i', $content )            // aria-labelledby attribute
			|| preg_match( '/role\s*=\s*[\'"]search[\'"]/i', $content );   // role=search covers the landmark

		if ( $has_accessible_name ) {
			return null;
		}

		$relative = str_replace(
			array( get_stylesheet_directory() . DIRECTORY_SEPARATOR, get_template_directory() . DIRECTORY_SEPARATOR ),
			'',
			$template_file
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The active theme\'s custom searchform.php does not appear to include a label, aria-label, or aria-labelledby attribute for the search input. Screen-reader users will not know the purpose of the field.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => '',
			'details'      => array(
				'template_file' => $relative,
				'fix'           => __( 'In your searchform.php, add a <label for="s">Search</label> before the text input, or add aria-label="Search" directly on the <input> element. Using role="search" on the wrapping <form> also helps users locate the region.', 'wpshadow' ),
			),
		);
	}
}
