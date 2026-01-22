<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Comment_Spam_Surge extends Diagnostic_Base .php {
    public static function check(): ?array {
        return ['id' => 'monitor-comment-spam', 'title' => __('Comment Spam Surge Detection', 'wpshadow'), 'description' => __('Detects spike in spam comments. Indicates compromised comment form or weak spam filter. Protects content credibility.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/comment-moderation/', 'training_link' => 'https://wpshadow.com/training/spam-prevention/', 'auto_fixable' => false, 'threat_level' => 5];
    }
}
