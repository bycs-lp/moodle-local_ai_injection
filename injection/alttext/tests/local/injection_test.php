<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace aiinjection_alttext\local;

use advanced_testcase;
use local_ai_injection\local\ai_manager_wrapper;
use local_ai_injection\plugininfo\aiinjection;
use local_ai_manager\ai_manager_utils;

/**
 * Unit tests for alttext injection class.
 *
 * @package    aiinjection_alttext
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \aiinjection_alttext\local\injection
 * @group      aiinjection_alttext
 */
final class injection_test extends advanced_testcase {
    /**
     * Create a mock AI manager wrapper with the given availability state.
     *
     * @param string $generalavailability One of ai_manager_utils::AVAILABILITY_* constants
     * @param string|null $purposeavailability One of ai_manager_utils::AVAILABILITY_* constants, or null
     * @return ai_manager_wrapper The mocked wrapper
     */
    private function create_mock_ai_wrapper(
        string $generalavailability = ai_manager_utils::AVAILABILITY_AVAILABLE,
        ?string $purposeavailability = null
    ): ai_manager_wrapper {
        $mock = $this->createMock(ai_manager_wrapper::class);

        $aiconfig = [
            'availability' => [
                'available' => $generalavailability,
                'errormessage' => '',
            ],
            'purposes' => [],
        ];

        if ($purposeavailability !== null) {
            $aiconfig['purposes'][] = [
                'purpose' => 'itt',
                'available' => $purposeavailability,
                'errormessage' => '',
            ];
        }

        $mock->method('get_ai_config')->willReturn($aiconfig);

        return $mock;
    }

    /**
     * Test injection class properties and inheritance.
     */
    public function test_injection_class_properties(): void {
        $this->resetAfterTest(true);

        // Mock the AI manager wrapper via DI.
        \core\di::set(ai_manager_wrapper::class, $this->create_mock_ai_wrapper());

        $injection = new injection();

        // Test inheritance.
        $this->assertInstanceOf(\local_ai_injection\local\base_injection::class, $injection);

        // Test public methods return expected values.
        $this->assertEquals('aiinjection_alttext', $injection->get_subplugin_name());
        $this->assertEquals('aiinjection_alttext/alttext_injection', $injection->get_js_module_name());
        $this->assertIsArray($injection->get_js_config());

        // Test enabled state.
        set_config('enabled', 1, 'aiinjection_alttext');
        $this->assertTrue($injection->is_enabled());

        set_config('enabled', 0, 'aiinjection_alttext');
        $this->assertFalse($injection->is_enabled());
    }

    /**
     * Test PROMPT_TEMPLATE constant exists and contains expected structure.
     */
    public function test_prompt_template_constant(): void {
        // PROMPT_TEMPLATE should be a public constant.
        $this->assertTrue(
            defined(injection::class . '::PROMPT_TEMPLATE'),
            'PROMPT_TEMPLATE constant should be defined'
        );

        $template = injection::PROMPT_TEMPLATE;

        // Template should be a non-empty string.
        $this->assertIsString($template);
        $this->assertNotEmpty($template);

        // Template should contain the {LANGUAGE} placeholder.
        $this->assertStringContainsString('{LANGUAGE}', $template);

        // Template should contain key phrases from the prompt.
        $this->assertStringContainsString('You are an alt text generator', $template);
        $this->assertStringContainsString('Return ONLY the alt text', $template);
    }

    /**
     * Test get_js_config returns prompt with language inserted.
     */
    public function test_get_js_config_returns_prompt_with_language(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Setup course context.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);

        // Mock the AI manager wrapper via DI.
        \core\di::set(ai_manager_wrapper::class, $this->create_mock_ai_wrapper());

        $this->setAdminUser();

        $injection = new injection();
        $config = $injection->get_js_config();

        // Config should contain prompt.
        $this->assertArrayHasKey('prompt', $config);
        $this->assertIsString($config['prompt']);
        $this->assertNotEmpty($config['prompt']);

        // Prompt should NOT contain the placeholder (it should be replaced).
        $this->assertStringNotContainsString('{LANGUAGE}', $config['prompt']);

        // Prompt should contain key phrases from the template.
        $this->assertStringContainsString('You are an alt text generator', $config['prompt']);

        // Prompt should contain a language name (e.g., "English").
        // The exact language depends on the test environment, but it should be there.
        $this->assertMatchesRegularExpression('/Language: [A-Z][a-z]+/', $config['prompt']);
    }

    /**
     * Test should_inject returns false when user lacks capability.
     */
    public function test_should_inject_returns_false_without_capability(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Setup course context.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);

        // Mock the AI manager wrapper via DI with AVAILABLE state.
        \core\di::set(ai_manager_wrapper::class, $this->create_mock_ai_wrapper(
            ai_manager_utils::AVAILABILITY_AVAILABLE
        ));

        $injection = new injection();

        // Regular user without capability - should not inject.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertFalse($injection->should_inject());
    }

    /**
     * Test should_inject returns true when user has capability and AI is available.
     */
    public function test_should_inject_returns_true_with_capability_and_available(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Setup course context.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);

        // Mock the AI manager wrapper via DI with AVAILABLE state.
        \core\di::set(ai_manager_wrapper::class, $this->create_mock_ai_wrapper(
            ai_manager_utils::AVAILABILITY_AVAILABLE
        ));

        $injection = new injection();

        // Admin has capability - should inject when AI is available.
        $this->setAdminUser();
        $this->assertTrue($injection->should_inject());
    }

    /**
     * Test should_inject returns true when AI is disabled (frontend handles disabled state).
     */
    public function test_should_inject_returns_true_when_ai_disabled(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Setup course context.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);

        // Mock the AI manager wrapper via DI with DISABLED state.
        \core\di::set(ai_manager_wrapper::class, $this->create_mock_ai_wrapper(
            ai_manager_utils::AVAILABILITY_DISABLED
        ));

        $injection = new injection();

        // Admin has capability - should inject even when disabled.
        // Frontend will show disabled button with reason.
        $this->setAdminUser();
        $this->assertTrue($injection->should_inject());
    }

    /**
     * Test should_inject returns false when AI is hidden.
     */
    public function test_should_inject_returns_false_when_ai_hidden(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Setup course context.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);

        // Mock the AI manager wrapper via DI with HIDDEN state.
        \core\di::set(ai_manager_wrapper::class, $this->create_mock_ai_wrapper(
            ai_manager_utils::AVAILABILITY_HIDDEN
        ));

        $injection = new injection();

        // Admin has capability but AI is hidden - should not inject.
        $this->setAdminUser();
        $this->assertFalse($injection->should_inject());
    }

    /**
     * Test should_inject returns false when purpose is hidden.
     */
    public function test_should_inject_returns_false_when_purpose_hidden(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Setup course context.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);

        // Mock the AI manager wrapper via DI with AVAILABLE general but HIDDEN purpose.
        \core\di::set(ai_manager_wrapper::class, $this->create_mock_ai_wrapper(
            ai_manager_utils::AVAILABILITY_AVAILABLE,
            ai_manager_utils::AVAILABILITY_HIDDEN
        ));

        $injection = new injection();

        // Admin has capability but purpose is hidden - should not inject.
        $this->setAdminUser();
        $this->assertFalse($injection->should_inject());
    }

    /**
     * Test plugin enable/disable via plugininfo.
     */
    public function test_plugin_enable_disable(): void {
        $this->resetAfterTest(true);

        // Mock the AI manager wrapper via DI.
        \core\di::set(ai_manager_wrapper::class, $this->create_mock_ai_wrapper());

        $injection = new injection();

        // Initially the plugin should be disabled (no config set).
        set_config('enabled', 0, 'aiinjection_alttext');
        $this->assertFalse($injection->is_enabled());

        // Enable via static enable_plugin method.
        $changed = aiinjection::enable_plugin('alttext', 1);
        $this->assertTrue($changed);
        $this->assertTrue($injection->is_enabled());

        // Enabling again should return false (no change).
        $changed = aiinjection::enable_plugin('alttext', 1);
        $this->assertFalse($changed);

        // Disable via static enable_plugin method.
        $changed = aiinjection::enable_plugin('alttext', 0);
        $this->assertTrue($changed);
        $this->assertFalse($injection->is_enabled());

        // Disabling again should return false (no change).
        $changed = aiinjection::enable_plugin('alttext', 0);
        $this->assertFalse($changed);
    }

    /**
     * Test get_enabled_plugins returns correct list.
     */
    public function test_get_enabled_plugins(): void {
        $this->resetAfterTest(true);

        // Initially alttext should not be in enabled list (if disabled).
        aiinjection::enable_plugin('alttext', 0);
        $enabled = aiinjection::get_enabled_plugins();
        $this->assertArrayNotHasKey('alttext', $enabled);

        // Enable the plugin.
        aiinjection::enable_plugin('alttext', 1);
        $enabled = aiinjection::get_enabled_plugins();
        $this->assertArrayHasKey('alttext', $enabled);
        $this->assertEquals('alttext', $enabled['alttext']);
    }

    /**
     * Test get_js_config returns correct ai_manager config structure.
     */
    public function test_get_js_config_structure(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Setup course context.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        $this->setAdminUser();

        // Mock the AI manager wrapper via DI.
        \core\di::set(ai_manager_wrapper::class, $this->create_mock_ai_wrapper(
            ai_manager_utils::AVAILABILITY_AVAILABLE,
            ai_manager_utils::AVAILABILITY_AVAILABLE
        ));

        $injection = new injection();
        $config = $injection->get_js_config();

        // Verify structure with aiconfig and contextid keys.
        $this->assertIsArray($config);
        $this->assertArrayHasKey('aiconfig', $config);
        $this->assertArrayHasKey('contextid', $config);

        // Verify contextid is a positive integer (course context).
        $this->assertIsInt($config['contextid']);
        $this->assertGreaterThan(0, $config['contextid']);

        // Verify aiconfig structure.
        $aiconfig = $config['aiconfig'];
        $this->assertArrayHasKey('availability', $aiconfig);
        $this->assertArrayHasKey('purposes', $aiconfig);

        // Verify availability structure.
        $availability = $aiconfig['availability'];
        $this->assertArrayHasKey('available', $availability);
        $this->assertArrayHasKey('errormessage', $availability);
        $this->assertEquals(ai_manager_utils::AVAILABILITY_AVAILABLE, $availability['available']);

        // Verify purposes is an array with one purpose.
        $this->assertIsArray($aiconfig['purposes']);
        $this->assertCount(1, $aiconfig['purposes']);
        $this->assertEquals('itt', $aiconfig['purposes'][0]['purpose']);
    }
}
