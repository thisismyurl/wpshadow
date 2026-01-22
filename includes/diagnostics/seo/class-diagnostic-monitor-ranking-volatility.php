<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Ranking_Volatility extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-ranking-volatility', 'title' => __('Ranking Volatility Detection', 'wpshadow'), 'description' => __('Tracks ranking position changes. Large swings = algorithm update impact or rank drop from issues. Monitor for recovery.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/ranking-monitoring/', 'training_link' => 'https://wpshadow.com/training/algorithm-updates/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
