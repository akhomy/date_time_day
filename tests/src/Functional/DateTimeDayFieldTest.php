<?php

namespace Drupal\Tests\date_time_day\Functional;

use Drupal\Tests\datetime\Functional\DateTestBase;

/**
 * Tests date_time_day field functionality.
 *
 * @group date_time_day
 */
class DateTimeDayFieldTest extends DateTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['date_time_day'];

  /**
   * The default display settings to use for the formatters.
   *
   * @var array
   */
  protected $defaultSettings = [
    'timezone_override' => '',
    'day_separator' => ',',
    'time_separator' => '-',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getTestFieldType() {
    return 'datetimeday';
  }

  /**
   * Test the default field type.
   */
  public function testDateTimeDayTypeDefaultField() {

  }

  /**
   * Test with seconds field type.
   */
  public function testDateTimeDayTypeSecondsField() {

  }

  /**
   * Test the default field widget.
   */
  public function testDateTimeDayDefaultWidgetField() {

  }

  /**
   * Test with seconds field widget.
   */
  public function testDateTimeDaySecondsWidgetField() {

  }

  /**
   * Test default value functionality.
   */
  public function testDefaultValue() {

  }

  /**
   * Test that invalid values are caught and marked as invalid.
   */
  public function testInvalidField() {

  }

  /**
   * Tests that 'Date' field storage setting form is disabled if field has data.
   */
  public function testDateStorageSettings() {

  }

}
