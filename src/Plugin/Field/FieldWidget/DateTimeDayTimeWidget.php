<?php

namespace Drupal\date_time_day\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\date_time_day\Plugin\Field\FieldType\DateTimeDayItem;

/**
 * Plugin implementation of the 'datetimeday_h_i_partial_time' widget.
 *
 * @FieldWidget(
 *   id = "datetimeday_h_i_partial_time",
 *   label = @Translation("Date Day Time"),
 *   field_types = {
 *     "datetimeday"
 *   }
 * )
 */
class DateTimeDayTimeWidget extends DateTimeDayWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Identify the type of date and time elements to use.
    switch ($this->getFieldSetting('datetime_type')) {
      case DateTimeDayItem::DATEDAY_TIME_DEFAULT_TYPE_FORMAT:
        // Date field properties.
        $value_date_format = DateTimeDayItem::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT;
        $value_date_type = 'date';
        $value_time_format = '';
        $value_time_type = 'none';
        break;

      default:
        // Date field properties.
        $value_date_format = DateTimeDayItem::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT;
        $value_date_type = 'date';
        $value_time_format = '';
        $value_time_type = 'none';
        break;
    }

    $element['value'] += [
      '#date_date_format' => $value_date_format,
      '#date_date_element' => $value_date_type,
      '#date_date_callbacks' => [],
      '#date_time_format' => $value_time_format,
      '#date_time_element' => $value_time_type,
      '#date_time_callbacks' => [],
    ];
    $element['start_time_value'] = [
      '#type' => 'textfield',
      '#size' => 12,
      '#attributes' => ['pattern' => '([01]?[0-9]|2[0-3]):[0-5][0-9]', 'title' => 'hh:mm'],
    ];
    if ($items[$delta]->start_time) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
      $start_time = $items[$delta]->start_time;
      $element['start_time_value']['#default_value'] = $start_time->format(DateTimeDayItem::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT);
    }

    $element['end_time_value'] = [
      '#type' => 'textfield',
      '#size' => 12,
      '#attributes' => ['pattern' => '([01]?[0-9]|2[0-3]):[0-5][0-9]', 'title' => 'hh:mm'],
    ];
    if ($items[$delta]->end_time) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
      $end_time = $items[$delta]->end_time;
      $element['end_time_value']['#default_value'] = $end_time->format(DateTimeDayItem::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT);
    }
    return $element;
  }

}
