<?php

namespace Drupal\Tests\date_time_day\Functional;

use Drupal\Tests\datetime\Functional\DateTestBase;
use Drupal\date_time_day\Plugin\Field\FieldType\DateTimeDayItem;

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
   * Test the default field type and widget.
   */
  public function testDateTimeDayTypeDefaultWidgetField() {
    $field_name = $this->fieldStorage->getName();
    $field_label = $this->field->label();
    // Ensure field is set to a date-only field.
    $this->fieldStorage->setSetting('datetime_type', DateTimeDayItem::DATEDAY_TIME_DEFAULT_TYPE_FORMAT);
    $this->fieldStorage->save();
    // Display creation form.
    $this->drupalGet('entity_test/add');
    $this->assertFieldByName("{$field_name}[0][value][date]", '', 'Date element found.');
    $this->assertFieldByName("{$field_name}[0][start_time_value]", '', 'Start time element found.');
    $this->assertFieldByName("{$field_name}[0][end_time_value]", '', 'End time element found.');
    $this->assertFieldByXPath('//*[@id="edit-' . $field_name . '-wrapper"]//label[contains(@class, "js-form-required")]', TRUE, 'Required markup found');
    $this->assertFieldByXPath('//fieldset[@id="edit-' . $field_name . '-0"]/legend', $field_label, 'Fieldset and label found');
    $this->assertFieldByXPath('//fieldset[@aria-describedby="edit-' . $field_name . '-0--description"]', NULL, 'ARIA described-by found');
    $this->assertFieldByXPath('//div[@id="edit-' . $field_name . '-0--description"]', NULL, 'ARIA description found');
    // Build up dates in the UTC timezone.
    // Submit a valid date and ensure it is accepted.
    // Verify the date doesn't change when entity is edited through the form.
    // Verify that the default formatter works.
    // Test that allowed markup in custom format is preserved and XSS is
    // removed.
  }

  /**
   * Test with seconds field type and widget.
   */
  public function testDateTimeDayTypeSecondsWidgetField() {
    $field_name = $this->fieldStorage->getName();
    $field_label = $this->field->label();
    // Ensure field is set to a date-only field.
    $this->fieldStorage->setSetting('datetime_type', DateTimeDayItem::DATEDAY_TIME_TYPE_SECONDS_FORMAT);
    $this->fieldStorage->save();
    // Display creation form.
    $this->drupalGet('entity_test/add');
    $this->assertFieldByName("{$field_name}[0][value][date]", '', 'Date element found.');
    $this->assertFieldByName("{$field_name}[0][start_time_value]", '', 'Start time element found.');
    $this->assertFieldByName("{$field_name}[0][end_time_value]", '', 'End time element found.');
    $this->assertFieldByXPath('//*[@id="edit-' . $field_name . '-wrapper"]//label[contains(@class, "js-form-required")]', TRUE, 'Required markup found');
    $this->assertFieldByXPath('//fieldset[@id="edit-' . $field_name . '-0"]/legend', $field_label, 'Fieldset and label found');
    $this->assertFieldByXPath('//fieldset[@aria-describedby="edit-' . $field_name . '-0--description"]', NULL, 'ARIA described-by found');
    $this->assertFieldByXPath('//div[@id="edit-' . $field_name . '-0--description"]', NULL, 'ARIA description found');
    // Build up dates in the UTC timezone.
    // Submit a valid date and ensure it is accepted.
    // Verify the date doesn't change when entity is edited through the form.
    // Verify that the default formatter works.
    // Test that allowed markup in custom format is preserved and XSS is
    // removed.
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
