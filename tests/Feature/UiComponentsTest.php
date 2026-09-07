<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class UiComponentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        view()->share('errors', new ViewErrorBag);
    }

    public function test_fields_preserve_attributes_values_and_validation(): void
    {
        $errors = (new ViewErrorBag)->put('default', new MessageBag(['email' => 'Enter a valid email.']));
        view()->share('errors', $errors);
        $html = Blade::render('<x-ui.field name="email" label="Email" required><x-ui.input name="email" id="email" type="email" value="a&amp;b" required aria-describedby="email-error" /></x-ui.field>');
        $this->assertStringContainsString('for="email"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('id="email-error"', $html);
        $this->assertStringContainsString('Enter a valid email.', $html);
    }

    public function test_filters_and_tables_keep_native_elements_and_js_hooks(): void
    {
        $html = Blade::render('<x-ui.filter action="/customers" id="filterForm"><x-ui.select name="status"><option value="active" selected>Active</option></x-ui.select><x-ui.button type="submit">Apply</x-ui.button></x-ui.filter><x-ui.table id="results"><tbody><tr><td>Customer</td></tr></tbody></x-ui.table>');
        $this->assertStringContainsString('method="GET"', $html);
        $this->assertStringContainsString('id="filterForm"', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('id="results"', $html);
        $this->assertStringNotContainsString('<x-ui.', $html);
    }

    public function test_flash_messages_are_escaped_and_use_semantic_roles(): void
    {
        session()->flash('success', '<script>alert(1)</script>');
        $html = Blade::render('<x-ui.flash-messages />');
        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }
}
