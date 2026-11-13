<?php

use App\Enums\UserRole;
use App\Models\EnvironmentalReport;
use App\Models\EnvironmentalTask;
use App\Models\GreenAsset;
use App\Models\GreenAssetConditionHistory;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Database\Seeders\GreenFundDemoSeeder;
use Illuminate\Support\Facades\Hash;

test('demo seeding creates requested local records and all role accounts idempotently', function () {
    app(GreenFundDemoSeeder::class)->run();
    app(GreenFundDemoSeeder::class)->run();

    expect(EnvironmentalReport::query()->count())->toBe(20)
        ->and(EnvironmentalTask::query()->count())->toBe(10)
        ->and(GreenAsset::query()->count())->toBe(30)
        ->and(MaintenanceSchedule::query()->count())->toBe(10)
        ->and(GreenAssetConditionHistory::query()->count())->toBe(30)
        ->and(User::query()->whereIn('role', array_map(
            static fn (UserRole $role): string => $role->value,
            UserRole::cases(),
        ))->count())->toBe(4);

    $citizen = User::query()->where('email', 'citizen@greenfund.local')->firstOrFail();

    expect($citizen->role)->toBe(UserRole::Citizen)
        ->and(Hash::check('GreenFundLocal2026!', $citizen->password))->toBeTrue();
});
