<?php declare(strict_types=1);
/**
 * Pingback Spam Diagnostic
 *
 * Philosophy: Spam pingbacks affect site quality
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Pingback_Spam {
    public static function check() {
        global $wpdb;
        $pingbacks = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->comments} WHERE comment_type = 'pingback' AND comment_approved = 'spam'");
        if ($pingbacks > 100) {
            return [
                'id' => 'seo-pingback-spam',
                'title' => 'Pingback Spam Detected',
                'description' => sprintf('%d spam pingbacks detected. Clean up and consider disabling pingbacks if not useful.', $pingbacks),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/pingback-spam/',
                'training_link' => 'https://wpshadow.com/training/comment-management/',
                'auto_fixable' => false,
                'threat_level' => 15,
            ];
        }
        return null;
    }
}
