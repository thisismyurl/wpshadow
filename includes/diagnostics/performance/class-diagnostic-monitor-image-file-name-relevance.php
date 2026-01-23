<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Image extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-image_file_name_relevance', 'title' => __('Image File Name Relevance', 'wpshadow'), 'description' => __('Verifies images named descriptively. Generic names (image1.jpg) = missed keyword signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }

}