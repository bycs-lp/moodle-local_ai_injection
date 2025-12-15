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
     * Set up test environment.
     */
    protected function setUp(): void {
        global $PAGE;
        parent::setUp();
        $this->resetAfterTest(true);
        // Ensure a valid context by attaching to a real course page without referencing context classes.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
    }

    /**
     * Test get_subplugin_name method.
     */
    public function test_get_subplugin_name(): void {
        $injection = new injection();

        // Use reflection to access protected method.
        $reflection = new \ReflectionMethod($injection, 'get_subplugin_name');
        $reflection->setAccessible(true);

        $this->assertEquals('aiinjection_alttext', $reflection->invoke($injection));
    }

    /**
     * Test get_amd_module method.
     */
    public function test_get_amd_module(): void {
        $injection = new injection();

        // Use reflection to access protected method.
        $reflection = new \ReflectionMethod($injection, 'get_amd_module');
        $reflection->setAccessible(true);

        $this->assertEquals('aiinjection_alttext/alttext_injection', $reflection->invoke($injection));
    }

    /**
     * Test get_js_config method.
     */
    public function test_get_js_config(): void {
        global $CFG;

        // Test with debugging enabled.
        $CFG->debug = DEBUG_DEVELOPER;

        $injection = new injection();

        // Use reflection to access protected method.
        $reflection = new \ReflectionMethod($injection, 'get_js_config');
        $reflection->setAccessible(true);

        $config = $reflection->invoke($injection);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('debug', $config);
        $this->assertTrue($config['debug']);

        // Test with debugging disabled.
        $CFG->debug = DEBUG_NONE;

        $config = $reflection->invoke($injection);
        $this->assertFalse($config['debug']);
    }

    /**
     * Test should_inject method in debugging mode.
     */
    public function test_should_inject_debug_mode_with_capability(): void {
        global $CFG;
        // Admin has all capabilities by default.
        $this->setAdminUser();
        // Enable debugging (bypasses tenant check, but capability still required).
        $CFG->debug = DEBUG_DEVELOPER;

        $injection = new injection();
        $reflection = new \ReflectionMethod($injection, 'should_inject');
        $reflection->setAccessible(true);
        $this->assertTrue($reflection->invoke($injection));
    }

    /**
     * Ensure should_inject returns false without capability, even in debug mode.
     */
    public function test_should_inject_without_capability_is_false(): void {
        global $CFG;
        $CFG->debug = DEBUG_DEVELOPER; // Would normally allow, but capability is required first.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $injection = new injection();
        $reflection = new \ReflectionMethod($injection, 'should_inject');
        $reflection->setAccessible(true);
        $this->assertFalse($reflection->invoke($injection));
    }

    /**
     * Test inheritance from base_injection.
     */
    public function test_inheritance(): void {
        $injection = new injection();
        $this->assertInstanceOf(\local_ai_injection\local\base_injection::class, $injection);
    }

    /**
     * Test all required abstract methods are implemented.
     */
    public function test_required_methods_implemented(): void {
        $injection = new injection();

        // Test that all methods from base_injection are available.
        $this->assertTrue(method_exists($injection, 'inject_javascript'));
        $this->assertTrue(method_exists($injection, 'get_subplugin_name'));
        $this->assertTrue(method_exists($injection, 'get_amd_module'));
        $this->assertTrue(method_exists($injection, 'get_js_config'));
        $this->assertTrue(method_exists($injection, 'should_inject'));
    }

    /**
     * Test inject_javascript method integration (basic smoke test).
     */
    public function test_inject_javascript_integration(): void {
        global $PAGE, $CFG;

        // Enable the subplugin.
        set_config('enabled', 1, 'aiinjection_alttext');
        // Ensure user has capability and bypass tenant via debugging.
        $this->setAdminUser();
        $CFG->debug = DEBUG_DEVELOPER;

        $injection = new injection();

        // This should not throw an exception.
        $injection->inject_javascript();

        $this->assertTrue(true); // Test passes if no exception is thrown.
    }

    /**
     * Test configuration integration.
     */
    public function test_configuration_integration(): void {
        // Test enabled status.
        set_config('enabled', 1, 'aiinjection_alttext');

        $injection = new injection();

        // Use reflection to test is_enabled method.
        $reflection = new \ReflectionMethod($injection, 'is_enabled');
        $reflection->setAccessible(true);

        $this->assertTrue($reflection->invoke($injection));

        // Test disabled status.
        set_config('enabled', 0, 'aiinjection_alttext');
        $this->assertFalse($reflection->invoke($injection));
    }
}
