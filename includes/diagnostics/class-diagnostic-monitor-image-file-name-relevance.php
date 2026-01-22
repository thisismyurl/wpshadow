<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Image-file-name-relevance {
  public static function check() {
    return ['id' => 'monitor-image_file_name_relevance', 'title' => __('Image File Name Relevance', 'wpshadow'), 'description' => __('Verifies images named descriptively. Generic names (image1.jpg) = missed keyword signal.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
