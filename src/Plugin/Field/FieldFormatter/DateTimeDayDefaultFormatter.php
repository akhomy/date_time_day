<?php

namespace Drupal\date_time_day\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeDefaultFormatter;
use Drupal\date_time_day\DateTimeDayTrait;

/**
 * Plugin implementation of the 'Default' formatter for 'datetimeday' fields.
 *
 * This formatter renders the data time day using <time> elements, with
 * configurable date formats (from the list of configured formats) and
 * separators.
 *
 * @FieldFormatter(
 *   id = "datetimeday_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "datetimeday"
 *   }
 * )
 */
class DateTimeDayDefaultFormatter extends DateTimeDefaultFormatter {

  use DateTimeDayTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'date_separator' => '-',
      'time_separator' => '-',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['date_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date separator'),
      '#description' => $this->t('The string to separate the day and start, end times'),
      '#default_value' => $this->getSetting('date_separator'),
    ];

    $form['time_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time separator'),
      '#description' => $this->t('The string to separate start, end times'),
      '#default_value' => $this->getSetting('time_separator'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($date_separator = $this->getSetting('date_separator')) {
      $summary[] = $this->t('Date Separator: %date_separator', ['%date_separator' => $date_separator]);
    }

    if ($time_separator = $this->getSetting('time_separator')) {
      $summary[] = $this->t('Time Separator: %time_separator', ['%time_separator' => $time_separator]);
    }

    return $summary;
  }

}
