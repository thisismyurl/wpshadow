<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_SEO_Crawl_Budget_Score extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'seo-crawl-budget-score', 'title' => __('Overall Crawl Budget Efficiency Score', 'wpshadow'), 'description' => __('Calculates holistic crawl efficiency: site speed, crawl depth, redirects, parameters, pagination. High score = Google crawls efficiently. Low score = wasted budget.', 'wpshadow'), 'severity' => 'high', 'category' => 'seo', 'kb_link' => 'https://wpshadow.com/kb/crawl-budget/', 'training_link' => 'https://wpshadow.com/training/crawl-optimization/', 'auto_fixable' => false, 'threat_level' => 9];
    }
}
