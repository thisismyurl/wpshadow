<?php
/**
 * Open Graph Tags Treatment
 *
 * Issue #4974: No Open Graph Tags (Social Sharing)
 * Pillar: #1: Helpful Neighbor / 🎓 Learning Inclusive
 *
 * Checks if Open Graph tags are configured.
 * OG tags control how content appears when shared socially.
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
 * Treatment_Open_Graph_Tags Class
 *
 * @since 0.6093.1200
 */
class Treatment_Open_Graph_Tags extends Treatment_Base {

	protected static $slug = 'open-graph-tags';
	protected static $title = 'No Open Graph Tags (Social Sharing)';
	protected static $description = 'Checks if Open Graph meta tags are configured';
	protected static $family = 'content';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Open_Graph_Tags' );
	}
}
