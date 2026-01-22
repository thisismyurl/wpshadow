<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Duplicate_Content_Emergence extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-duplicate-content', 'title' => __('Duplicate Content Emergence Detection', 'wpshadow'), 'description' => __('Detects when new duplicate/thin content is published. Indicates content farm activity or hacker spam injection.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/duplicate-detection/', 'training_link' => 'https://wpshadow.com/training/unique-content/', 'auto_fixable' => false, 'threat_level' => 6];
    }
}
