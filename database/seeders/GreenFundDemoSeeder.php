<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\EnvironmentalReport;
use App\Models\EnvironmentalTask;
use App\Models\GreenAsset;
use App\Models\GreenAssetConditionHistory;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GreenFundDemoSeeder extends Seeder
{
    /**
     * Seed clearly synthetic environmental records for local development.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $users = collect([
                UserRole::Citizen->value => ['Citizen Demo', 'citizen@greenfund.local'],
                UserRole::Operator->value => ['Operator Demo', 'operator@greenfund.local'],
                UserRole::Executor->value => ['Executor Demo', 'executor@greenfund.local'],
                UserRole::LandscapingSpecialist->value => ['Landscaping Demo', 'landscaping@greenfund.local'],
            ])->mapWithKeys(function (array $account, string $role): array {
                [$name, $email] = $account;

                $user = User::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => 'GreenFundLocal2026!',
                        'role' => $role,
                        'email_verified_at' => now(),
                    ],
                );

                return [$role => $user];
            });

            $citizen = $users[UserRole::Citizen->value];
            $operator = $users[UserRole::Operator->value];
            $executor = $users[UserRole::Executor->value];
            $landscaper = $users[UserRole::LandscapingSpecialist->value];

            $locations = [
                ['Korkyt Ata Street, near Syr Darya', 44.8508, 65.5093],
                ['Central Park, east entrance', 44.8487, 65.4954],
                ['Tazhibayev Street, Kyzylorda', 44.8604, 65.5182],
                ['Arai microdistrict, block 12', 44.8338, 65.5008],
                ['Abay Avenue, bus stop 18', 44.8563, 65.4931],
                ['Nazarbayev Avenue, central strip', 44.8431, 65.5237],
                ['Syr Darya river promenade', 44.8502, 65.5162],
                ['Independence Square, city centre', 44.8473, 65.5094],
                ['Kyzylorda railway district', 44.8614, 65.4879],
                ['Arai Park, southern entrance', 44.8295, 65.5067],
            ];
            $reportSamples = [
                ['illegal_dumping', 'Construction debris beside the canal road'],
                ['damaged_tree', 'Poplar branches damaged by strong wind'],
                ['air_pollution', 'Smoke reported near a residential block'],
                ['green_space', 'New saplings need watering during the hot spell'],
                ['water_pollution', 'Unusual discoloration observed at the river bank'],
                ['illegal_dumping', 'Household waste left beside overflowing containers'],
                ['green_space', 'Broken irrigation pipe flooding a park path'],
                ['other', 'Public flower bed damaged near the footpath'],
                ['damaged_tree', 'Tree trunk shows signs of disease'],
                ['air_pollution', 'Dust from an unmanaged construction site'],
            ];
            $statuses = ['new', 'assigned', 'in_progress', 'completed', 'rejected'];
            $reports = [];

            foreach (range(0, 19) as $index) {
                [$category, $description] = $reportSamples[$index % count($reportSamples)];
                [$address, $latitude, $longitude] = $locations[$index % count($locations)];
                $status = $statuses[$index % count($statuses)];
                $reportNumber = 'ENV-'.(2501 + $index);

                $reports[] = EnvironmentalReport::query()->updateOrCreate(
                    ['report_number' => $reportNumber],
                    [
                        'user_id' => $citizen->id,
                        'submitted_at' => now()->subDays($index),
                        'category' => $category,
                        'description' => $description,
                        'additional_information' => $index % 3 === 0 ? 'Sample record for local demonstration.' : null,
                        'address' => $address,
                        'latitude' => $latitude + (($index % 3) * 0.0007),
                        'longitude' => $longitude + (($index % 4) * 0.0006),
                        'priority' => ['low', 'medium', 'high', 'urgent'][$index % 4],
                        'status' => $status,
                        'assigned_to' => $status === 'new' || $status === 'rejected' ? null : $executor->id,
                        'created_at' => now()->subDays($index),
                        'updated_at' => now()->subDays(max(0, $index - 1)),
                    ],
                );
            }

            $taskTitles = [
                'Clear construction debris',
                'Inspect wind-damaged poplars',
                'Check the reported smoke source',
                'Water newly planted saplings',
                'Inspect riverbank water quality',
                'Collect waste at bus stop 18',
                'Repair the park irrigation line',
                'Restore the public flower bed',
                'Inspect the diseased tree',
                'Control construction-site dust',
            ];

            foreach (range(0, 9) as $index) {
                $report = $reports[$index];
                $status = ['in_progress', 'assigned', 'completed', 'new', 'in_progress'][$index % 5];
                $dueDate = $index % 4 === 0 ? now()->subDay() : now()->addDays($index + 1);
                $completedAt = $status === 'completed' ? now()->subDays(1) : null;

                EnvironmentalTask::query()->updateOrCreate(
                    ['task_number' => 'TSK-'.(9201 + $index)],
                    [
                        'report_id' => $report->id,
                        'title' => $taskTitles[$index],
                        'description' => 'Synthetic field task linked to a local demonstration report.',
                        'category' => ['Waste removal', 'Tree inspection', 'Air quality', 'Watering', 'Inspection'][$index % 5],
                        'address' => $report->address,
                        'latitude' => $report->latitude,
                        'longitude' => $report->longitude,
                        'assigned_to' => $executor->id,
                        'created_by' => $operator->id,
                        'priority' => ['low', 'medium', 'high', 'urgent'][$index % 4],
                        'status' => $status,
                        'due_date' => $dueDate,
                        'completed_at' => $completedAt,
                        'created_at' => now()->subDays($index),
                        'updated_at' => $completedAt ?? now()->subHours($index),
                    ],
                );
            }

            $assetNames = [
                ['Siberian elm', 'Ulmus pumila', 'tree'],
                ['Eastern poplar', 'Populus nigra', 'tree'],
                ['Ash tree', 'Fraxinus excelsior', 'tree'],
                ['White willow', 'Salix alba', 'tree'],
                ['Turangyl Park', null, 'park'],
                ['Independence Square', null, 'square'],
                ['Arai Green Belt', null, 'green_area'],
            ];
            $conditions = ['healthy', 'needs_watering', 'diseased', 'drying', 'pest_damage'];
            $assets = [];

            foreach (range(0, 29) as $index) {
                [$name, $species, $type] = $assetNames[$index % count($assetNames)];
                [$address, $latitude, $longitude] = $locations[$index % count($locations)];
                $assetNumber = ($type === 'tree' ? 'TREE-' : 'AREA-').str_pad((string) (1001 + $index), 5, '0', STR_PAD_LEFT);

                $assets[] = GreenAsset::query()->updateOrCreate(
                    ['asset_number' => $assetNumber],
                    [
                        'name' => $name,
                        'type' => $type,
                        'species' => $species,
                        'description' => 'Synthetic green asset record for local demonstration.',
                        'condition' => $conditions[$index % count($conditions)],
                        'address' => $address,
                        'latitude' => $latitude + (($index % 4) * 0.0005),
                        'longitude' => $longitude + (($index % 5) * 0.0004),
                        'registration_date' => now()->subDays(30 + $index)->toDateString(),
                    ],
                );

                GreenAssetConditionHistory::query()->firstOrCreate(
                    [
                        'green_asset_id' => $assets[$index]->id,
                        'condition' => $assets[$index]->condition,
                        'notes' => 'Initial condition at registration.',
                    ],
                    ['updated_by' => $landscaper->id],
                );
            }

            foreach (range(0, 9) as $index) {
                $asset = $assets[$index];
                $task = EnvironmentalTask::query()->where('task_number', 'TSK-'.(9201 + $index))->firstOrFail();
                $completed = $index < 2;

                MaintenanceSchedule::query()->updateOrCreate(
                    ['code' => 'MNT-'.str_pad((string) (1001 + $index), 4, '0', STR_PAD_LEFT)],
                    [
                        'title' => [
                            'Watering route · Sector A',
                            'Inspect damaged poplars',
                            'Clean river promenade',
                            'Prune ash trees along Abay Avenue',
                            'Check irrigation lines',
                        ][$index % 5],
                        'maintenance_type' => ['watering', 'inspection', 'cleaning', 'pruning', 'inspection'][$index % 5],
                        'description' => 'Synthetic maintenance record for local demonstration.',
                        'location_label' => $asset->address,
                        'green_asset_id' => $asset->id,
                        'task_id' => $task->id,
                        'assigned_to' => $landscaper->id,
                        'scheduled_at' => now()->addDays($index),
                        'completed_at' => $completed ? now()->subDay() : null,
                        'status' => $completed ? 'completed' : ($index === 2 ? 'in_progress' : 'scheduled'),
                    ],
                );
            }
        });
    }
}
