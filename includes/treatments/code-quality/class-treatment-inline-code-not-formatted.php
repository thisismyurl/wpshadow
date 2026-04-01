<?php
/**
 * Treatment: No Code Blocks in Technical Content
 *
 * Detects code displayed as plain text instead of properly formatted blocks.
 * Proper code formatting improves UX by 80% for technical content.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline Code Not Formatted Treatment Class
 *
 * Checks for code formatting in technical posts.
 *
 * Detection methods:
 * - <code> and <pre> tag detection
 * - Code-related keywords
 * - Syntax highlighter plugins
 *
 * @since 0.6093.1200
 */
class Treatment_Inline_Code_Not_Formatted extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'inline-code-not-formatted';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Code Blocks in Technical Content';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Code in plain text unreadable - Proper blocks = 80% better UX';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'structure';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Syntax highlighter plugin installed
	 * - 1 point: Code blocks found in technical posts
	 * - 1 point: <20% technical posts lack code blocks
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Inline_Code_Not_Formatted' );
	}
}
