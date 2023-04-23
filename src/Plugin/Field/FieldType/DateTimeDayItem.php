<?php

declare(strict_types = 1);

namespace Drupal\date_time_day\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\date_time_day\DateDayComputed;
use Drupal\date_time_day\DateTimeDayComputed;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;

/**
 * Plugin implementation of the 'datetimeday' field type.
 *
 * @FieldType(
 *   id = "datetimeday",
 *   label = @Translation("Date time day"),
 *   description = @Translation("Create and store date time day field."),
 *   default_widget = "datetimeday_default",
 *   default_formatter = "datetimeday_default",
 *   list_class = "\Drupal\date_time_day\Plugin\Field\FieldType\DateTimeDayFieldItemList"
 * )
 */
class DateTimeDayItem extends DateTimeItem {

  /**
   * Values for the 'time_type' setting: store only a time, time & seconds.
   */
  const DATEDAY_TIME_DEFAULT_TYPE_FORMAT = 'time';
  const DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT = 'H:i';
  const DATEDAY_TIME_TYPE_SECONDS_FORMAT = 'time_seconds';
  const DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT = 'H:i:s';

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'datetime_type' => DateTimeItem::DATETIME_TYPE_DATE,
      'time_type' => static::DATEDAY_TIME_DEFAULT_TYPE_FORMAT,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    /** @var \Drupal\Core\TypedData\DataDefinition $date */
    $date = $properties['date'];
    $date->setClass(DateDayComputed::class);

    $properties['start_time_value'] = DataDefinition::create('string')
      ->setLabel((string) t('Start time value'))
      ->setRequired(TRUE);

    $properties['start_time'] = DataDefinition::create('any')
      ->setLabel((string) t('Computed start time'))
      ->setDescription((string) t('The computed start DateTime object.'))
      ->setComputed(TRUE)
      ->setClass(DateTimeDayComputed::class)
      ->setSetting('date source', 'start_time_value');

    $properties['end_time_value'] = DataDefinition::create('string')
      ->setLabel((string) t('End time value'))
      ->setRequired(TRUE);

    $properties['end_time'] = DataDefinition::create('any')
      ->setLabel((string) t('Computed end time'))
      ->setDescription((string) t('The computed end DateTime object.'))
      ->setComputed(TRUE)
      ->setClass(DateTimeDayComputed::class)
      ->setSetting('date source', 'end_time_value');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['start_time_value'] = [
      'description' => 'The start time value.',
    ] + $schema['columns']['value'];

    $schema['columns']['end_time_value'] = [
      'description' => 'The end time value.',
    ] + $schema['columns']['value'];

    $schema['indexes']['start_time_value'] = ['start_time_value'];
    $schema['indexes']['end_time_value'] = ['end_time_value'];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = parent::storageSettingsForm($form, $form_state, $has_data);

    $element['time_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Day type'),
      '#description' => $this->t('Choose the type of time to create for the start and end time.'),
      '#default_value' => $this->getSetting('time_type'),
      '#options' => [
        static::DATEDAY_TIME_DEFAULT_TYPE_FORMAT => $this->t('Start, end time of day'),
        static::DATEDAY_TIME_TYPE_SECONDS_FORMAT => $this->t('Start, end time of day with seconds'),
      ],
      '#disabled' => $has_data,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values = parent::generateSampleValue($field_definition);

    $timestamp = (int) microtime() - (int) mt_rand(0, 86400 * 365);
    $start = $timestamp - 3600;
    $end = $start + 3600;
    $type = $field_definition->getSetting('time_type');
    if ($type === static::DATEDAY_TIME_DEFAULT_TYPE_FORMAT) {
      $values['start_time_value'] = gmdate(static::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT, $start);
      $values['end_time_value'] = gmdate(static::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT, $end);
    }
    if ($type === static::DATEDAY_TIME_TYPE_SECONDS_FORMAT) {
      $values['start_time_value'] = gmdate(static::DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT, $start);
      $values['end_time_value'] = gmdate(static::DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT, $end);
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    $start_value = $this->get('start_time_value')->getValue();
    $end_value = $this->get('end_time_value')->getValue();
    return ($value === NULL || $value === '') && ($start_value === NULL || $start_value === '') && ($end_value === NULL || $end_value === '');
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function onChange($property_name, $notify = TRUE) {
    // Enforce that the computed date is recalculated.
    if ($property_name == 'start_time_value') {
      // @phpstan-ignore-next-line
      $this->start_time = NULL;
    }
    elseif ($property_name == 'end_time_value') {
      // @phpstan-ignore-next-line
      $this->end_time = NULL;
    }
    parent::onChange($property_name, $notify);
  }

}
