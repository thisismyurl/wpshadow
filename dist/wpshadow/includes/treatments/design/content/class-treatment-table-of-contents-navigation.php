<?php
/**
 * Table of Contents Navigation Treatment
 *
 * Issue #4915: Long Pages Missing Table of Contents
 * Pillar: 🎓 Learning Inclusive / 🌍 Accessibility First
 *
 * Checks if long-form content has navigation.
 * Users need to jump to sections without scrolling.
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
 * Treatment_Table_Of_Contents_Navigation Class
 *
 * @since 0.6093.1200
 */
class Treatment_Table_Of_Contents_Navigation extends Treatment_Base {

	protected static $slug = 'table-of-contents-navigation';
	protected static $title = 'Long Pages Missing Table of Contents';
	protected static $description = 'Checks if documentation pages have navigation aids';
	protected static $family = 'content';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Table_Of_Contents_Navigation' );
	}
}
