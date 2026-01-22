<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Ranking_Volatility {
    public static function check() {
        return ['id' => 'monitor-ranking-volatility', 'title' => __('Ranking Volatility Detection', 'wpshadow'), 'description' => __('Tracks ranking position changes. Large swings = algorithm update impact or rank drop from issues. Monitor for recovery.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/ranking-monitoring/', 'training_link' => 'https://wpshadow.com/training/algorithm-updates/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
