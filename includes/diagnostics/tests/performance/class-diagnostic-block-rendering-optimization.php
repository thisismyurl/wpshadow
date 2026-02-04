<?php
/**
 * Block Rendering Optimization Diagnostic
 *
 * Issue #4982: Unused Block Templates Loaded
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if all block templates are actually used.
 * Unused blocks increase page size and load time.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Block_Rendering_Optimization Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Block_Rendering_Optimization extends Diagnostic_Base {

	protected static $slug = 'block-rendering-optimization';
	protected static $title = 'Unused Block Templates Loaded';
	protected static $description = 'Checks if all block templates are actually used';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Audit which blocks are actually used on pages', 'wpshadow' );
		$issues[] = __( 'Deactivate unused block plugins', 'wpshadow' );
		$issues[] = __( 'Use lazy-loading for block CSS/JS', 'wpshadow' );
		$issues[] = __( 'Only enqueue block scripts on pages that use them', 'wpshadow' );
		$issues[] = __( 'Use block-specific CSS (not global)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Every block registered loads CSS and JavaScript. Unused blocks bloat page size. Deactivate plugins for unused block types.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/block-optimization',
				'details'      => array(
					'recommendations'         => $issues,
					'impact'                  => 'Each unused block adds 10-50KB overhead',
					'block_audit'             => 'Check which blocks appear in posts/pages',
					'deactivation'            => 'Use plugin to disable block types',
				),
			);
		}

		return null;
	}
}
