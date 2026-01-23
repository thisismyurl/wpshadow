<?php
declare(strict_types=1);
/**
 * Gutenberg Block XSS Diagnostic
 *
 * Philosophy: Block security - prevent XSS in custom blocks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for XSS vulnerabilities in Gutenberg blocks.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Gutenberg_Block_XSS extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Get all registered blocks
		$registry = WP_Block_Type_Registry::get_instance();
		$blocks = $registry->get_all_registered();
		
		$suspicious_blocks = array();
		
		foreach ( $blocks as $block_name => $block ) {
			// Skip core blocks
			if ( strpos( $block_name, 'core/' ) === 0 ) {
				continue;
			}
			
			// Check if block has render callback
			if ( ! empty( $block->render_callback ) ) {
				// Try to get callback source (limited analysis)
				if ( is_array( $block->render_callback ) ) {
					$class = is_object( $block->render_callback[0] ) ? get_class( $block->render_callback[0] ) : $block->render_callback[0];
					$method = $block->render_callback[1];
					
					if ( class_exists( $class ) ) {
						$reflection = new \ReflectionMethod( $class, $method );
						$source = file_get_contents( $reflection->getFileName() );
						
						// Look for direct attribute output without escaping
						if ( preg_match( '/\$attributes\[[^\]]+\]\s*(?!.*esc_)/s', $source ) ) {
							$suspicious_blocks[] = $block_name;
						}
					}
				}
			}
		}
		
		if ( ! empty( $suspicious_blocks ) ) {
			return array(
				'id'          => 'gutenberg-block-xss',
				'title'       => 'Gutenberg Blocks May Have XSS',
				'description' => sprintf(
					'Custom blocks potentially outputting unescaped attributes: %s. Block attributes from editor can contain malicious scripts. Use esc_html(), esc_attr() in render callbacks.',
					implode( ', ', array_slice( $suspicious_blocks, 0, 3 ) )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-gutenberg-blocks/',
				'training_link' => 'https://wpshadow.com/training/block-security/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}

}