<?php

declare(strict_types = 1);

namespace Drupal\date_time_day;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedData;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * A computed property for date of date day field items.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - date source: The date property containing the to be computed date.
 */
class DateDayComputed extends TypedData {

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
      throw new \InvalidArgumentException("The definition's 'date source' key has to specify the name of the date property to be computed.");
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

    $storage_format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
    try {
      $date = DrupalDateTime::createFromFormat($storage_format, $value, DateTimeItemInterface::STORAGE_TIMEZONE);
      if ($date instanceof DrupalDateTime && !$date->hasErrors()) {
        $this->date = $date;
        // The format did not include an explicit time portion, then the
        // time will be set from the current time instead. For consistency, we
        // set the time to 12:00:00 UTC for date-only fields. This is used so
        // that the local date portion is the same, across nearly all time
        // zones.
        // @see http://php.net/manual/en/datetime.createfromformat.php
        $this->date->setDefaultDateTime();
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
