<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Design_ extends Diagnostic_Base $(echo "$id" | sed 's/-/_/g' | awk '{print toupper($0)}') {
    public static function check(): ?array {
        return [
            'id' => 'design-$(echo "$id" | tr '_' '-')',
            'title' => __('$(echo "$title")', 'wpshadow'),
            'description' => __('$(echo "$desc")', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-$(echo "$id" | tr '_' '-')',
            'training_link' => 'https://wpshadow.com/training/design-$(echo "$id" | tr '_' '-')',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
