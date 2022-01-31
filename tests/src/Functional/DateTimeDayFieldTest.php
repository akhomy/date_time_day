<?php

declare (strict_types = 1);

namespace Drupal\Tests\date_time_day\Functional;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\Tests\datetime\Functional\DateTestBase;
use Drupal\date_time_day\Plugin\Field\FieldType\DateTimeDayItem;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests date_time_day field functionality.
 *
 * @group date_time_day
 */
class DateTimeDayFieldTest extends DateTestBase {

  use StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['date_time_day'];

  /**
   * An array of timezone extremes to test.
   *
   * @var string[]
   */
  protected static $timezones = [
    // UTC.
    'UTC',
  ];

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
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected function getTestFieldType() {
    return 'datetimeday';
  }

  /**
   * Test the default field type and widget.
   */
  public function testDateTimeDayTypeDefaultWithWidgetField(): void {
    $field_name = $this->fieldStorage->getName();
    // Loop through defined timezones to test that date-only fields work at the
    // extremes.
    foreach (static::$timezones as $timezone) {

      $this->setSiteTimezone($timezone);
      $this->assertEquals($timezone, $this->config('system.date')->get('timezone.default'), 'Time zone set to ' . $timezone);
      // Ensure field is set to a date-only field.
      $this->fieldStorage->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATE);
      $this->fieldStorage->setSetting('time_type', DateTimeDayItem::DATEDAY_TIME_DEFAULT_TYPE_FORMAT);
      $this->fieldStorage->save();
      // Set correct form widget type.
      EntityFormDisplay::load('entity_test.entity_test.default')
        ->setComponent($field_name, ['type' => 'datetimeday_default'])
        ->save();
      // Display creation form.
      $this->drupalGet('entity_test/add');

      $this->assertSession()->fieldValueEquals("{$field_name}[0][value][date]", '');
      $this->assertSession()->fieldValueEquals("{$field_name}[0][start_time_value]", '');
      $this->assertSession()->fieldValueEquals("{$field_name}[0][end_time_value]", '');
      // @phpstan-ignore-next-line
      $this->assertSession()->elementExists('xpath', '//*[@id="edit-' . $field_name . '-wrapper"]//label[contains(@class, "js-form-required")]');
      // @phpstan-ignore-next-line
      $this->assertSession()->elementExists('xpath', '//fieldset[@id="edit-' . $field_name . '-0"]/legend');
      // @phpstan-ignore-next-line
      $this->assertSession()->elementExists('xpath', '//fieldset[@aria-describedby="edit-' . $field_name . '-0--description"]');
      // @phpstan-ignore-next-line
      $this->assertSession()->elementExists('xpath', '//div[@id="edit-' . $field_name . '-0--description"]');
      // Build up dates in the UTC timezone.
      $date_value = '2012-12-30 00:00:00';
      $date = new DrupalDateTime($date_value, 'UTC');
      $start_time_value = '10:00';
      $end_time_value = '19:00';
      // Submit a valid date and ensure it is accepted.
      $date_format = DateFormat::load('html_date')->getPattern();

      $edit = [
        "{$field_name}[0][value][date]" => $date->format($date_format),
        "{$field_name}[0][start_time_value]" => $start_time_value,
        "{$field_name}[0][end_time_value]" => $end_time_value,
      ];
      $this->submitForm($edit, 'Save');
      $match = [];
      preg_match('|entity_test/manage/(\d+)|', $this->getUrl(), $match);
      $id = isset($match[1]) ? $match[1] : NULL;
      $this->assertSession()->pageTextContains("entity_test $id has been created.");
      $this->assertSession()->responseContains('2012-12-30');
      $this->assertSession()->responseContains($start_time_value);
      $this->assertSession()->responseContains($end_time_value);
      // Verify the date doesn't change when entity is edited through the form.
      $entity = EntityTest::load($id);
      $this->assertEquals('2012-12-30', $entity->{$field_name}->value);
      $this->assertEquals($start_time_value, $entity->{$field_name}->start_time_value);
      $this->assertEquals($end_time_value, $entity->{$field_name}->end_time_value);
      $this->drupalGet('entity_test/manage/' . $id . '/edit');
      $this->submitForm([], 'Save');
      $this->drupalGet('entity_test/manage/' . $id . '/edit');
      $this->submitForm([], 'Save');
      $this->drupalGet('entity_test/manage/' . $id . '/edit');
      $this->submitForm([], 'Save');
      $entity = EntityTest::load($id);
      $this->assertEquals('2012-12-30', $entity->{$field_name}->value);
      $this->assertEquals($start_time_value, $entity->{$field_name}->start_time_value);
      $this->assertEquals($end_time_value, $entity->{$field_name}->end_time_value);
    }
  }

  /**
   * Test with seconds field type and widget.
   */
  public function testDateTimeDayTypeSecondsWithWidgetField(): void {
    $field_name = $this->fieldStorage->getName();
    $field_label = $this->field->label();
    // Loop through defined timezones to test that date-only fields work at the
    // extremes.
    foreach (static::$timezones as $timezone) {

      $this->setSiteTimezone('UTC');
      $this->assertEquals($timezone, $this->config('system.date')->get('timezone.default'), 'Time zone set to ' . $timezone);
      // Ensure field is set to a date-only field.
      $this->fieldStorage->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATE);
      $this->fieldStorage->setSetting('time_type', DateTimeDayItem::DATEDAY_TIME_TYPE_SECONDS_FORMAT);
      // Set correct form widget type.
      EntityFormDisplay::load('entity_test.entity_test.default')
        ->setComponent($field_name, ['type' => 'datetimeday_h_i_s_time'])
        ->save();
      $this->fieldStorage->save();
      // Display creation form.
      $this->drupalGet('entity_test/add');
      $this->assertSession()->fieldValueEquals("{$field_name}[0][value][date]", '');
      $this->assertSession()->fieldValueEquals("{$field_name}[0][start_time_value][time]", '');
      $this->assertSession()->fieldValueEquals("{$field_name}[0][end_time_value][time]", '');
      // @phpstan-ignore-next-line
      $this->assertSession()->elementExists('xpath', '//*[@id="edit-' . $field_name . '-wrapper"]//label[contains(@class, "js-form-required")]');
      // @phpstan-ignore-next-line
      $this->assertSession()->elementExists('xpath', '//fieldset[@id="edit-' . $field_name . '-0"]/legend', $field_label);
      // @phpstan-ignore-next-line
      $this->assertSession()->elementExists('xpath', '//fieldset[@aria-describedby="edit-' . $field_name . '-0--description"]');
      // @phpstan-ignore-next-line
      $this->assertSession()->elementExists('xpath', '//div[@id="edit-' . $field_name . '-0--description"]');
      // Build up dates in the UTC timezone.
      $date_value = '2012-12-30 00:00:00';
      $date = new DrupalDateTime($date_value, 'UTC');
      $start_time_value = '18:10:10';
      $end_time_value = '19:19:19';
      // Submit a valid date and ensure it is accepted.
      $date_format = DateFormat::load('html_date')->getPattern();

      $edit = [
        "{$field_name}[0][value][date]" => $date->format($date_format),
        "{$field_name}[0][start_time_value][time]" => $start_time_value,
        "{$field_name}[0][end_time_value][time]" => $end_time_value,
      ];
      $this->submitForm($edit, 'Save');
      $match = [];
      preg_match('|entity_test/manage/(\d+)|', $this->getUrl(), $match);
      $id = isset($match[1]) ? $match[1] : NULL;
      $this->assertSession()->pageTextContains("entity_test $id has been created.");
      $this->assertSession()->responseContains('2012-12-30');
      $this->assertSession()->responseContains($start_time_value);
      $this->assertSession()->responseContains($end_time_value);
      // Verify the date doesn't change when entity is edited through the form.
      $entity = EntityTest::load($id);
      $this->assertEquals('2012-12-30', $entity->{$field_name}->value);
      $this->assertEquals($start_time_value, $entity->{$field_name}->start_time_value);
      $this->assertEquals($end_time_value, $entity->{$field_name}->end_time_value);
      $this->drupalGet('entity_test/manage/' . $id . '/edit');
      $this->submitForm([], 'Save');
      $this->drupalGet('entity_test/manage/' . $id . '/edit');
      $this->submitForm([], 'Save');
      $this->drupalGet('entity_test/manage/' . $id . '/edit');
      $this->submitForm([], 'Save');
      $entity = EntityTest::load($id);
      $this->assertEquals('2012-12-30', $entity->{$field_name}->value);
      $this->assertEquals($start_time_value, $entity->{$field_name}->start_time_value);
      $this->assertEquals($end_time_value, $entity->{$field_name}->end_time_value);
    }
  }

  /**
   * Tests that field storage setting form is disabled if field has data.
   */
  public function testDateStorageSettings(): void {
    // Create a test content type.
    $this->drupalCreateContentType(['type' => 'date_content']);

    // Create a field storage with settings to validate.
    $field_name = mb_strtolower($this->randomMachineName());
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'datetimeday',
      'settings' => [
        'datetime_type' => DateTimeItem::DATETIME_TYPE_DATE,
        'time_type' => DateTimeDayItem::DATEDAY_TIME_DEFAULT_TYPE_FORMAT,
      ],
    ]);
    $field_storage->save();
    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'field_name' => $field_name,
      'bundle' => 'date_content',
    ]);
    $field->save();

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');

    $display_repository->getFormDisplay('node', 'date_content', 'default')
      ->setComponent($field_name, [
        'type' => 'datetimeday_default',
      ])
      ->save();
    $edit = [
      'title[0][value]' => $this->randomString(),
      'body[0][value]' => $this->randomString(),
      $field_name . '[0][value][date]' => '2016-04-01',
      $field_name . '[0][start_time_value]' => '10:00',
      $field_name . '[0][end_time_value]' => '19:00',
    ];
    $this->drupalGet('node/add/date_content');
    $this->submitForm($edit, 'Save');
    $this->drupalGet('admin/structure/types/manage/date_content/fields/node.date_content.' . $field_name . '/storage');
    $result = $this->xpath("//*[@id='edit-settings-datetime-type' and contains(@disabled, 'disabled')]");
    $this->assertEquals(count($result), 1, "Changing datetime setting is disabled.");
    $this->assertSession()->pageTextContains('There is data for this field in the database. The field settings can no longer be changed.');
  }

}
