<?php
/**
 * Text Truncation Attack Not Prevented Diagnostic
 *
 * Checks text truncation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Text_Truncation_Attack_Not_Prevented Class
 *
 * Performs diagnostic check for Text Truncation Attack Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Text_Truncation_Attack_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'text-truncation-attack-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Text Truncation Attack Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks text truncation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'prevent_text_truncation' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Text truncation attack not prevented. Validate field lengths consistently across all layers.',
						'severity'   =>   'medium',
						'threat_level'   =>   40,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/text-truncation-attack-not-prevented'
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
