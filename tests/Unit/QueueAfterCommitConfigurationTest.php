<?php

namespace Tests\Unit;

use App\Events\ModelChanged;
use App\Events\PurchaseCreated;
use App\Events\PurchaseReturnCreated;
use App\Events\SaleCreated;
use App\Events\SaleReturnCreated;
use App\Listeners\InvalidateSystemCache;
use App\Listeners\SendSaleNotification;
use Tests\TestCase;

class QueueAfterCommitConfigurationTest extends TestCase
{
    public function test_redis_queue_connection_waits_for_commit(): void
    {
        $this->assertTrue(config('queue.connections.redis.after_commit'));
    }

    public function test_broadcast_events_and_queue_listeners_are_after_commit_safe(): void
    {
        foreach ([
            ModelChanged::class,
            SaleCreated::class,
            PurchaseCreated::class,
            SaleReturnCreated::class,
            PurchaseReturnCreated::class,
            InvalidateSystemCache::class,
            SendSaleNotification::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);

            $this->assertTrue($reflection->hasProperty('afterCommit'));
            $this->assertTrue($reflection->getProperty('afterCommit')->getDefaultValue());
        }
    }
}
