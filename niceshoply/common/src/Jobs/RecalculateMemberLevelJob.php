<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NiceShoply\Common\Services\Member\MemberLevelService;
use Throwable;

/**
 * 队列异步重算客户会员等级。
 */
class RecalculateMemberLevelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $customerId) {}

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        MemberLevelService::getInstance()->recalculateForCustomer($this->customerId);
    }
}
