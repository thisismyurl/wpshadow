<?php
declare(strict_types=1);
/**
 * Comment Spam Ratio Diagnostic
 *
 * Philosophy: High spam ratio wastes crawl budget
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Comment_Spam_Ratio extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $approved = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->comments} WHERE comment_approved = '1'");
        $spam = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
        if ($spam > 100 && $spam > ($approved * 2)) {
            return [
                'id' => 'seo-comment-spam-ratio',
                'title' => 'High Comment Spam Ratio',
                'description' => sprintf('%d spam comments vs %d approved. Clean up spam to reduce database bloat.', $spam, $approved),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/comment-spam/',
                'training_link' => 'https://wpshadow.com/training/comment-management/',
                'auto_fixable' => false,
                'threat_level' => 25,
            ];
        }
        return null;
    }
}
