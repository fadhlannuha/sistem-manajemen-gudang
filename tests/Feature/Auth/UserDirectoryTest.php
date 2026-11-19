<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Modules\Auth\Contracts\UserDirectoryContract;
use App\Modules\Auth\DTO\UserSummary;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\UserDirectoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_is_bound_to_the_auth_service(): void
    {
        $this->assertInstanceOf(
            UserDirectoryService::class,
            $this->app->make(UserDirectoryContract::class),
        );
    }

    public function test_contract_returns_dtos_including_roles(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Uji',
            'email' => 'admin.uji@example.test',
        ]);
        $staff = User::factory()->staff()->create();

        $contract = $this->app->make(UserDirectoryContract::class);

        $summary = $contract->find($admin->id);

        $this->assertInstanceOf(UserSummary::class, $summary);
        $this->assertSame($admin->id, $summary->id);
        $this->assertSame('Admin Uji', $summary->name);
        $this->assertSame('admin.uji@example.test', $summary->email);
        $this->assertSame(['admin'], $summary->roles);

        $this->assertNull($contract->find(999_999));
        $this->assertSame(2, $contract->count());

        $many = $contract->findMany([$admin->id, $staff->id]);

        $this->assertCount(2, $many);
        $this->assertArrayHasKey($admin->id, $many);
        $this->assertArrayHasKey($staff->id, $many);
        $this->assertSame(['staff_gudang'], $many[$staff->id]->roles);
        $this->assertSame([], $contract->findMany([]));
    }
}
