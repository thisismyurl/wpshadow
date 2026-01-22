<?php declare(strict_types=1);
/**
 * Author Archive Strategy Diagnostic
 *
 * Philosophy: Single-author sites should noindex author archives
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_Author_Archive_Strategy {
    public static function check() {
        $user_count = count_users();
        if ($user_count['total_users'] === 1) {
            return [
                'id' => 'seo-author-archive-strategy',
                'title' => 'Single-Author Site Author Archives',
                'description' => 'Single-author sites should noindex author archives to avoid duplicate content with blog homepage.',
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/author-archives/',
                'training_link' => 'https://wpshadow.com/training/archive-templates-seo/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
        }
        return null;
    }
}
