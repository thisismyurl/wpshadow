<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: External Comment Systems (THIRD-003)
 * 
 * Detects Disqus, Facebook Comments, etc.
 * Philosophy: Educate (#5) about comment system alternatives.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_External_Comment_Systems extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for external comment systems
        $has_disqus = function_exists('dsq_init');
        $has_facebook = get_option('fb_app_id');
        $has_custom = apply_filters('wpshadow_external_comments_detected', false);
        
        if ($has_disqus || $has_facebook || $has_custom) {
            return array(
                'id' => 'external-comment-systems',
                'title' => __('External Comment System Detected', 'wpshadow'),
                'description' => __('Using an external comment system adds extra requests and may impact performance. Monitor third-party service uptime.', 'wpshadow'),
                'severity' => 'info',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/external-comment-systems/',
                'training_link' => 'https://wpshadow.com/training/comment-performance/',
                'auto_fixable' => false,
                'threat_level' => 30,
            );
        }
        return null;
	}

}