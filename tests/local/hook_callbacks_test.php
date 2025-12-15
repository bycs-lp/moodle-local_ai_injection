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

namespace local_ai_injection\local;

use advanced_testcase;
use core\hook\output\before_footer_html_generation;

/**
 * Unit tests for hook_callbacks class.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_ai_injection\local\hook_callbacks
 * @group      local_ai_injection
 */
final class hook_callbacks_test extends advanced_testcase {
    /**
     * Mock subplugin for testing.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Set up test fixtures.
     */
    private function create_test_subplugin_class(): void {
        // Include test fixtures.
        require_once(__DIR__ . '/../fixtures/test_injection.php');
    }

    /**
     * Set up failing test fixtures.
     */
    private function create_failing_subplugin_class(): void {
        // Include failing test fixtures.
        require_once(__DIR__ . '/../fixtures/failing_injection.php');
    }

    /**
     * Test before_footer_html_generation with no subplugins.
     */
    public function test_before_footer_html_generation_no_subplugins(): void {
        global $PAGE;
        $this->resetAfterTest(true);

        // Create a mock renderer of the correct type.
        $renderer = $this->getMockBuilder(\core\output\renderer_base::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create a real hook instance with mock renderer.
        $hook = new before_footer_html_generation($renderer);

        // Test the hook callback directly.
        // This should complete without error even if no subplugins are found.
        hook_callbacks::before_footer_html_generation($hook);

        $this->assertTrue(true); // Test passes if no exceptions thrown.
    }

    /**
     * Test before_footer_html_generation with working subplugin.
     */
    public function test_before_footer_html_generation_with_subplugin(): void {
        global $CFG, $PAGE;
        $this->resetAfterTest(true);

        // Create test subplugin class.
        $this->create_test_subplugin_class();

        // Create a mock renderer of the correct type.
        $renderer = $this->getMockBuilder(\core\output\renderer_base::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create real hook with mock renderer.
        $hook = new before_footer_html_generation($renderer);

        // Enable debugging to ensure exception handling works.
        $CFG->debug = DEBUG_DEVELOPER;

        // This test verifies the method exists and can be called.
        $this->assertTrue(method_exists(hook_callbacks::class, 'before_footer_html_generation'));

        // Call the actual method - it should handle missing subplugins gracefully.
        hook_callbacks::before_footer_html_generation($hook);

        // If we get here, the method didn't throw an exception.
        $this->assertTrue(true);
    }

    /**
     * Test before_footer_html_generation with exception handling.
     */
    public function test_before_footer_html_generation_exception_handling(): void {
        global $CFG, $PAGE;
        $this->resetAfterTest(true);

        // Create failing subplugin class.
        $this->create_failing_subplugin_class();

        // Enable debugging to test exception handling.
        $CFG->debug = DEBUG_DEVELOPER;
        $CFG->debugdisplay = 1;

        // Create a mock renderer of the correct type.
        $renderer = $this->getMockBuilder(\core\output\renderer_base::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create real hook with mock renderer.
        $hook = new before_footer_html_generation($renderer);

        // Test that the method handles exceptions gracefully.
        hook_callbacks::before_footer_html_generation($hook);

        // The method should not throw exceptions, even with failing subplugins.
        $this->assertTrue(true);
    }

    /**
     * Test that hook callback method exists and is callable.
     */
    public function test_hook_callback_method_exists(): void {
        $this->assertTrue(method_exists(hook_callbacks::class, 'before_footer_html_generation'));
        $this->assertTrue(is_callable([hook_callbacks::class, 'before_footer_html_generation']));

        // Check method is static.
        $reflection = new \ReflectionMethod(hook_callbacks::class, 'before_footer_html_generation');
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test hook callback parameter type.
     */
    public function test_hook_callback_parameter_type(): void {
        $reflection = new \ReflectionMethod(hook_callbacks::class, 'before_footer_html_generation');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('hook', $parameters[0]->getName());

        $type = $parameters[0]->getType();
        $this->assertNotNull($type);
        $this->assertEquals(before_footer_html_generation::class, $type->getName());
    }
}
