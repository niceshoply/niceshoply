<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\Promotion;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 后台促销活动 Controller 测试。
 */
class PromotionControllerTest extends ConsoleTestCase
{
    private function payload(): array
    {
        return [
            'name'            => '满200减30-'.uniqid(),
            'scope'           => 'cart',
            'condition_type'  => 'min_amount',
            'condition_value' => 200,
            'action_type'     => 'fixed',
            'action_value'    => 30,
            'priority'        => 10,
            'active'          => 1,
            'label'           => '全场满 200 减 30',
        ];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('promotions.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('promotions.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['promotions_index']);
        $this->get($this->consoleUrl('promotions.index'))->assertStatus(200);
    }

    public function test_store_creates_promotion(): void
    {
        $this->loginAdmin(['promotions_store']);

        $payload = $this->payload();
        $this->post($this->consoleUrl('promotions.store'), $payload)
            ->assertRedirect($this->consoleUrl('promotions.index'));

        $this->assertDatabaseHas('nice_promotions', ['name' => $payload['name'], 'action_type' => 'fixed']);
    }

    public function test_update_modifies_promotion(): void
    {
        $this->loginAdmin(['promotions_update']);

        $promotion = Promotion::query()->create([
            'name'        => 'orig-'.uniqid(), 'scope' => 'cart', 'condition_type' => 'none',
            'action_type' => 'fixed', 'actions' => ['value' => 5], 'active' => true,
        ]);

        $payload         = $this->payload();
        $payload['name'] = '更新后活动';

        $this->put($this->consoleUrl('promotions.update', $promotion->id), $payload)
            ->assertRedirect($this->consoleUrl('promotions.index'));

        $this->assertDatabaseHas('nice_promotions', ['id' => $promotion->id, 'name' => '更新后活动']);
    }

    public function test_destroy_removes_promotion(): void
    {
        $this->loginAdmin(['promotions_destroy']);

        $promotion = Promotion::query()->create([
            'name'        => 'del-'.uniqid(), 'scope' => 'cart', 'condition_type' => 'none',
            'action_type' => 'fixed', 'actions' => ['value' => 5], 'active' => true,
        ]);

        $this->delete($this->consoleUrl('promotions.destroy', $promotion->id))->assertRedirect();
        $this->assertDatabaseMissing('nice_promotions', ['id' => $promotion->id]);
    }
}
