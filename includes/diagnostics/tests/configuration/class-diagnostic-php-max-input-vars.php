<?php
/**
 * Diagnostic: PHP max_input_vars
 *
 * Checks if PHP max_input_vars is adequate for WordPress forms.
 * Too low can cause $_POST/$_GET variables to be silently truncated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Max_Input_Vars
 *
 * Tests PHP max_input_vars configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Max_Input_Vars extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-max-input-vars';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP max_input_vars';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP max_input_vars is adequate';

	/**
	 * Check PHP max_input_vars setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get max_input_vars setting.
		$max_input_vars = ini_get( 'max_input_vars' );

		// Convert to integer.
		$max_input_vars = (int) $max_input_vars;

		// Default is 1000, but WordPress often needs more due to custom fields, widgets, etc.
		$wordpress_minimum = 3000;

		// Warn if below WordPress minimum.
		if ( $max_input_vars < $wordpress_minimum && $max_input_vars > 0 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Current value, 2: Recommended minimum */
					__( 'PHP max_input_vars is set to %1$d, which is below the recommended minimum of %2$d for WordPress. This may cause form data to be silently truncated, affecting custom fields, widgets, and plugin settings.', 'wpshadow' ),
					$max_input_vars,
					$wordpress_minimum
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_max_input_vars',
				'meta'        => array(
					'max_input_vars'       => $max_input_vars,
					'wordpress_recommended' => $wordpress_minimum,
				),
			);
		}

		// Warn if set to default 1000 (likely not intentional increase).
		if ( 1000 === $max_input_vars ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Recommended minimum for WordPress */
					__( 'PHP max_input_vars is at the default value of 1000. WordPress recommends at least %d input variables for proper functionality. Consider increasing this value.', 'wpshadow' ),
					$wordpress_minimum
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_max_input_vars',
				'meta'        => array(
					'max_input_vars'       => $max_input_vars,
					'is_default'           => true,
					'wordpress_recommended' => $wordpress_minimum,
				),
			);
		}

		// Warn if set very high (potential DoS risk).
		$maximum_safe = 100000;

		if ( $max_input_vars > $maximum_safe ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Current value */
					__( 'PHP max_input_vars is set to %d, which is extremely high. This may allow large POST requests that could tie up server resources. Consider a more reasonable value like 5000-10000.', 'wpshadow' ),
					$max_input_vars
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_max_input_vars',
				'meta'        => array(
					'max_input_vars' => $max_input_vars,
				),
			);
		}

		// PHP max_input_vars is properly configured.
		return null;
	}
}
