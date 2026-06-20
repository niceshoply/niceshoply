<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\ShippingTemplate;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 后台运费模板 Controller 测试。
 */
class ShippingTemplateControllerTest extends ConsoleTestCase
{
    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['shipping_templates_index']);
        $this->get($this->consoleUrl('shipping_templates.index'))->assertStatus(200);
    }

    public function test_store_creates_template_with_json_rules(): void
    {
        $this->loginAdmin(['shipping_templates_store']);

        $name = 'Tpl-'.uniqid();
        $this->post($this->consoleUrl('shipping_templates.store'), [
            'name'           => $name,
            'zone_id'        => '',
            'calc_type'      => 'by_weight',
            'rules'          => '{"base":5,"rate":2,"unit":1}',
            'free_threshold' => 100,
            'priority'       => 1,
            'active'         => 1,
        ])->assertRedirect($this->consoleUrl('shipping_templates.index'));

        $template = ShippingTemplate::query()->where('name', $name)->first();
        $this->assertNotNull($template);
        $this->assertEquals('by_weight', $template->calc_type);
        $this->assertEquals(5, $template->rules['base']);
    }

    public function test_destroy_removes_template(): void
    {
        $this->loginAdmin(['shipping_templates_destroy']);

        $template = ShippingTemplate::query()->create([
            'name' => 'Del-'.uniqid(), 'calc_type' => 'flat', 'rules' => [], 'active' => true,
        ]);

        $this->delete($this->consoleUrl('shipping_templates.destroy', $template->id))->assertRedirect();
        $this->assertDatabaseMissing('nice_shipping_templates', ['id' => $template->id]);
    }
}
