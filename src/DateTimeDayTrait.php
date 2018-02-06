<?php

namespace Drupal\date_time_day;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\date_time_day\Plugin\Field\FieldType\DateTimeDayItem;

/**
 * Provides friendly methods for date_time_day.
 */
trait DateTimeDayTrait {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $date_separator = $this->getSetting('date_separator');
    $time_separator = $this->getSetting('time_separator');

    foreach ($items as $delta => $item) {
      if (!empty($item->start_time) && !empty($item->end_time)) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
        $date = $item->date;
        /** @var \Drupal\Core\Datetime\DrupalDateTime $start_time */
        $start_time = $item->start_time;
        /** @var \Drupal\Core\Datetime\DrupalDateTime $end_time */
        $end_time = $item->end_time;
        $datetime_type = $this->getFieldSetting('datetime_type');
        $storage_format = $datetime_type === DateTimeDayItem::DATEDAY_TIME_DEFAULT_TYPE_FORMAT ? DateTimeDayItem::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT : DateTimeDayItem::DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT;
        $elements[$delta] = [
          'date' => ['#plain_text' => $date->format(DATETIME_DATE_STORAGE_FORMAT)],
          'date_separator' => ['#plain_text' => ' ' . $date_separator . ' '],
          'start_time' => ['#plain_text' => $start_time->format($storage_format)],
          'time_separator' => ['#plain_text' => ' ' . $time_separator . ' '],
          'end_time' => ['#plain_text' => $end_time->format($storage_format)],
        ];
      }
    }

    return $elements;
  }

}
