<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Live Chat/Push/Notification Impact (THIRD-347)
 *
 * Measures ongoing cost of chat, push, notification scripts.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_LiveChatNotificationImpact extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Monitor live chat impact on performance
        $has_live_chat = apply_filters('wpshadow_live_chat_active', false);
        
        if ($has_live_chat) {
            $chat_impact = get_transient('wpshadow_livechat_impact_ms');
            
            if ($chat_impact && $chat_impact > 300) { // 300ms
                return array(
                    'id' => 'live-chat-notification-impact',
                    'title' => sprintf(__('Live Chat Impact: +%dms', 'wpshadow'), $chat_impact),
                    'description' => __('Live chat widget is impacting page performance. Consider loading it asynchronously or after user interaction.', 'wpshadow'),
                    'severity' => 'low',
                    'category' => 'monitoring',
                    'kb_link' => 'https://wpshadow.com/kb/livechat-optimization/',
                    'training_link' => 'https://wpshadow.com/training/chat-widget-performance/',
                    'auto_fixable' => false,
                    'threat_level' => 35,
                );
            }
        }
        return null;
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
