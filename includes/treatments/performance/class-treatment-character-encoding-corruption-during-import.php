<?php
/**
 * Character Encoding Corruption During Import Treatment
 *
 * Detects when special characters become corrupted during import.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Character Encoding Corruption During Import Treatment Class
 *
 * Detects when special characters, unicode, or emoji become corrupted during import.
 *
 * @since 1.6033.0000
 */
class Treatment_Character_Encoding_Corruption_During_Import extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'character-encoding-corruption-during-import';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Character Encoding Corruption During Import';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects character encoding issues during import';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Character_Encoding_Corruption_During_Import' );
	}
}
