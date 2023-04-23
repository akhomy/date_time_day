<?php

declare(strict_types = 1);

namespace Drupal\date_time_day;

use Drupal\date_time_day\Plugin\Field\FieldType\DateTimeDayItem;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedData;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * A computed property for times of date time day field items.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - date source: The date property containing the to be computed date.
 */
class DateTimeDayComputed extends TypedData {

  /**
   * Cached computed date.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime|null
   */
  protected $date = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinitionInterface $definition, $name = NULL, ?TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);
    if (!$definition->getSetting('date source')) {
      throw new \InvalidArgumentException("The definition's 'date source' key has to specify the name of the date time property to be computed.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(string $langcode = NULL) {
    if ($this->date !== NULL) {
      return $this->date;
    }

    /** @var \Drupal\Core\Field\FieldItemInterface $item */
    $item = $this->getParent();
    $value = $item->{($this->definition->getSetting('date source'))} ?? '';
    $value = is_array($value) ? array_shift($value) : $value;

    // A date cannot be created from a NULL value.
    if (empty($value) || !is_string($value)) {
      return NULL;
    }
    $datetime_type = $item->getFieldDefinition()->getSetting('time_type');
    $storage_format = $datetime_type === DateTimeDayItem::DATEDAY_TIME_DEFAULT_TYPE_FORMAT ? DateTimeDayItem::DATE_TIME_DAY_H_I_FORMAT_STORAGE_FORMAT : DateTimeDayItem::DATE_TIME_DAY_H_I_S_FORMAT_STORAGE_FORMAT;
    // Fix time with seconds in incorrect widget.
    if ($datetime_type === DateTimeDayItem::DATEDAY_TIME_TYPE_SECONDS_FORMAT && strlen($value) === 5) {
      $value = "$value:00";
    }
    try {
      $date = DrupalDateTime::createFromFormat($storage_format, $value, DateTimeItemInterface::STORAGE_TIMEZONE);
      if ($date instanceof DrupalDateTime && !$date->hasErrors()) {
        $this->date = $date;
      }
    }
    catch (\Exception $e) {
      // @todo Handle this.
    }
    return $this->date;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function setValue($value, $notify = TRUE) {
    $this->date = $value;
    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

}
