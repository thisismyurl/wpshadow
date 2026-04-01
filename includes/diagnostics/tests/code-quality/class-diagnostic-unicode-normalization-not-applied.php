<?php
/**
 * Unicode Normalization Not Applied Diagnostic
 *
 * Checks unicode normalization.
 *
 * @package    WPShadow
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
 * Diagnostic_Unicode_Normalization_Not_Applied Class
 *
 * Performs diagnostic check for Unicode Normalization Not Applied.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Unicode_Normalization_Not_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unicode-normalization-not-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unicode Normalization Not Applied';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks unicode normalization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'normalize_unicode_input' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unicode normalization is not applied yet. Normalizing input can reduce spoofing risks and keep text handling consistent.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/unicode-normalization-not-applied?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
