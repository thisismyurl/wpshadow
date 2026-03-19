<?php
/**
 * Tool View Base Loader
 *
 * Backwards compatibility loader for Tool_View_Base, which now lives in
 * includes/ui/templates/class-tool-view-base.php.
 *
 * @package    WPShadow
 * @subpackage Views
 * @since 1.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WPSHADOW_PATH . 'includes/ui/templates/class-tool-view-base.php';
