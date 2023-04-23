<?php

declare(strict_types = 1);

namespace Drupal\date_time_day\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase;
use Drupal\date_time_day\Plugin\Field\FieldType\DateTimeDayItem;

/**
 * Base class for the 'datetimeday_*' widgets.
 */
class DateTimeDayWidgetBase extends DateTimeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#element_validate'][] = [$this, 'validateStartEnd'];

    // Wrap all of the selected elements with a fieldset.
    $element['#theme_wrappers'][] = 'fieldset';

    $element['value']['#title'] = $this->t('Date');

    $element['start_time_value'] = [
      '#title' => $this->t('Start time'),
    ] + $element['value'];

    $element['end_time_value'] = [
      '#title' => $this->t('End time'),
    ] + $element['value'];
    /** @var \Drupal\date_time_day\Plugin\Field\FieldType\DateTimeDayItem $items[$delta] */
    if (!empty($items[$delta]->date)) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $value */
      $value = $items[$delta]->date;
      $element['value']['#default_value'] = $this->createDateTimeDayDefaultValue($value, $element['value']['#date_timezone']);
    }

    if (!empty($items[$delta]->start_time)) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $start_time */
      $start_time = $items[$delta]->start_time;
      $element['start_time_value']['#default_value'] = $this->createDateTimeDayDefaultValue($start_time, $element['start_time_value']['#date_timezone']);
    }

    if (!empty($items[$delta]->end_time)) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $end_time */
      $end_time = $items[$delta]->end_time;
      $element['end_time_value']['#default_value'] = $this->createDateTimeDayDefaultValue($end_time, $element['end_time_value']['#date_timezone']);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // The widget form element type has transformed the value to a
    // DrupalDateTime object at this point. We need to convert it back to the
    // storage timezone and format.
    foreach ($values as &$item) {
      if (!empty($item['value']) && $item['value'] instanceof DrupalDateTime) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $value_date */
        $value_date = $item['value'];
        $value_format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
        // Adjust the date for storage.
        $value_date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
        $item['value'] = $value_date->format($value_format);
      }
      if (!empty($item['start_time_value']) && $item['start_time_value'] instanceof DrupalDateTime) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $start_time_date */
        $start_time_date = $item['start_time_value'];
        $start_time_format = '';
        switch ($this->getFieldSetting('time_type')) {
          case DateTimeDayItem::DATEDAY_TIME_DEFAULT_TYPE_FORMAT:
            $start_time_format = DateTimeDayItem::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT;
            break;

          case DateTimeDayItem::DATEDAY_TIME_TYPE_SECONDS_FORMAT:
            $start_time_format = DateTimeDayItem::DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT;
            break;

          default:
            $start_time_format = DateTimeDayItem::DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT;
            break;
        }
        // Adjust the date for storage.
        $start_time_date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
        $item['start_time_value'] = $start_time_date->format($start_time_format);
      }

      if (!empty($item['end_time_value']) && $item['end_time_value'] instanceof DrupalDateTime) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $end_time_date */
        $end_time_date = $item['end_time_value'];
        $end_time_format = '';
        switch ($this->getFieldSetting('time_type')) {
          case DateTimeDayItem::DATEDAY_TIME_DEFAULT_TYPE_FORMAT:
            $end_time_format = DateTimeDayItem::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT;
            break;

          case DateTimeDayItem::DATEDAY_TIME_TYPE_SECONDS_FORMAT:
            $end_time_format = DateTimeDayItem::DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT;
            break;

          default:
            $end_time_format = DateTimeDayItem::DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT;
            break;
        }
        // Adjust the date for storage.
        $end_time_date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
        $item['end_time_value'] = $end_time_date->format($end_time_format);
      }
    }
    return $values;
  }

  /**
   * Creates a date object for use as a default value.
   *
   * This will take a default value, apply the proper timezone for display in
   * a widget, and set the default time for date-only fields.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   The UTC default date.
   * @param string $timezone
   *   The timezone to apply.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   A date object for use as a default value in a field widget.
   */
  protected function createDateTimeDayDefaultValue(DrupalDateTime $date, string $timezone): DrupalDateTime {
    $date->setTimezone(new \DateTimeZone($timezone));
    return $date;
  }

  /**
   * Validation callback to ensure that the start_time <= the end_time.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public function validateStartEnd(array &$element, FormStateInterface $form_state, array &$complete_form): void {
    $type = $this->getFieldSetting('time_type');
    $start_date = $element['start_time_value']['#value']['time'] ?? $element['start_time_value']['#value'];
    $storage_format = $type === DateTimeDayItem::DATEDAY_TIME_DEFAULT_TYPE_FORMAT ? DateTimeDayItem::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT : DateTimeDayItem::DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT;
    if ($type === DateTimeDayItem::DATEDAY_TIME_TYPE_SECONDS_FORMAT && strlen($start_date) === 5) {
      $start_date = "$start_date:00";
    }
    $end_date = $element['end_time_value']['#value']['time'] ?? $element['end_time_value']['#value'];
    if ($type === DateTimeDayItem::DATEDAY_TIME_TYPE_SECONDS_FORMAT && strlen($end_date) === 5) {
      $end_date = "$end_date:00";
    }

    if (!empty($start_date) && !empty($end_date)) {
      $start_date = DrupalDateTime::createFromFormat($storage_format, $start_date);
      $end_date = DrupalDateTime::createFromFormat($storage_format, $end_date);

      if ($start_date->getTimestamp() !== $end_date->getTimestamp()) {
        $interval = $start_date->diff($end_date);
        if ($interval->invert === 1) {
          $form_state->setError($element, (string) $this->t('The @title end date cannot be before the start date', ['@title' => $element['#title']]));
        }
      }
    }
  }

}
