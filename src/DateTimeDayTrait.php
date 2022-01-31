<?php

declare(strict_types = 1);

namespace Drupal\date_time_day;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Provides friendly methods for date_time_day.
 */
trait DateTimeDayTrait {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $day_separator = $this->getSetting('day_separator');
    $time_separator = $this->getSetting('time_separator');

    foreach ($items as $delta => $item) {
      $elements[$delta]['date'] = $this->buildDateWithIsoAttribute($item->date);
      if (!empty($item->start_time)) {
        $elements[$delta]['day_separator'] = ['#plain_text' => $day_separator];
        $elements[$delta]['start_time'] = $this->buildTimeWithAttribute($item->start_time);
        if (!empty($item->end_time)) {
          $elements[$delta]['time_separator'] = ['#plain_text' => $time_separator];
          $elements[$delta]['end_time'] = $this->buildTimeWithAttribute($item->end_time);
        }
      }
    }

    return $elements;
  }

}
