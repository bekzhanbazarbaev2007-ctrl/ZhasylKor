<div class="modal-backdrop" id="detail-modal" hidden>
    <section class="dialog-card" role="dialog" aria-modal="true" aria-labelledby="detail-title">
        <button class="dialog-close" type="button" data-close-modal aria-label="Close dialog">×</button>
        <div class="eyebrow">REPORT DETAILS</div>
        <h2 id="detail-title">Environmental report</h2>
        <p id="detail-description"></p>
        <div class="dialog-meta" id="detail-meta"></div>
        <div class="photo-gallery" id="report-photo-gallery"></div>
        <div class="dialog-actions">
            @if ($canManageReports)<button class="button button-primary" type="button" data-open-report-manage>Manage report</button>@endif
            <button class="button button-secondary" type="button" data-close-modal>Close</button>
        </div>
    </section>
</div>

<div class="modal-backdrop" id="task-modal" hidden>
    <section class="dialog-card task-dialog" role="dialog" aria-modal="true" aria-labelledby="task-dialog-title">
        <button class="dialog-close" type="button" data-close-modal aria-label="Close dialog">×</button>
        <div class="eyebrow" id="task-dialog-id">TASK DETAILS</div>
        <h2 id="task-dialog-title">Task details</h2>
        <p id="task-dialog-description"></p>
        <a class="task-map-link" id="task-map-link" href="{{ route('greenfund.screen', ['page' => 'map']) }}">Open location on map ↗</a>
        <div class="dialog-meta" id="task-dialog-meta"></div>
        @if ($canCompleteTasks)
            <form id="task-progress-form" method="POST">
                @csrf
                @method('PATCH')
                @if ($canManageTasks)
                    <label class="form-field"><span>Status</span><select name="status"><option value="assigned">Assigned</option><option value="in_progress">In progress</option></select></label>
                    <label class="form-field"><span>Assigned executor</span><select name="assigned_to"><option value="">Unassigned</option>@foreach ($employees as $employee)<option value="{{ $employee['id'] }}">{{ $employee['name'] }} · {{ $employee['role'] }}</option>@endforeach</select></label>
                    <div class="settings-fields"><label class="form-field"><span>Priority</span><select name="priority"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></label><label class="form-field"><span>Deadline</span><input name="due_date" type="datetime-local"></label></div>
                @else
                    <input type="hidden" name="status" value="in_progress">
                    <p class="form-hint">Mark this assigned task as in progress before submitting completion.</p>
                @endif
                <div class="dialog-actions"><button class="button button-secondary" type="submit">Update task</button></div>
            </form>
            <form id="task-completion-form" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="form-field"><span>Completion photo <b>*</b></span><input name="photo" type="file" accept="image/jpeg,image/png,image/webp" required></label>
                <label class="form-field"><span>Work notes</span><textarea name="notes" rows="3" maxlength="2000" placeholder="Describe the work completed..."></textarea></label>
                <div class="dialog-actions"><button class="button button-primary" type="submit">Submit for verification</button></div>
            </form>
        @endif
    </section>
</div>

<div class="modal-backdrop" id="asset-modal" hidden>
    <section class="dialog-card asset-dialog" role="dialog" aria-modal="true" aria-labelledby="asset-dialog-title">
        <button class="dialog-close" type="button" data-close-modal aria-label="Close dialog">×</button>
        <div class="eyebrow" id="asset-dialog-id">GREEN ASSET PROFILE</div>
        <h2 id="asset-dialog-title">Asset profile</h2>
        <div class="asset-photo-gallery" id="asset-photo-gallery"></div>
        <div class="dialog-meta" id="asset-dialog-meta"></div>
        <h3 class="asset-history-title">Condition history &amp; maintenance</h3>
        <div class="asset-history" id="asset-history"></div>
        @if ($canManageAssets)
            <form id="asset-update-form" method="POST">
                @csrf
                @method('PATCH')
                <label class="form-field"><span>Condition</span><select name="condition" required><option value="healthy">Healthy</option><option value="needs_watering">Needs watering</option><option value="diseased">Diseased</option><option value="drying">Drying</option><option value="pest_damage">Pest damage</option></select></label>
                <label class="form-field"><span>Location</span><input name="address" maxlength="255" required></label>
                <label class="form-field"><span>Species</span><input name="species" maxlength="255"></label>
                <label class="form-field"><span>Description</span><textarea name="description" rows="2" maxlength="2000"></textarea></label>
                <label class="form-field"><span>Condition update notes</span><textarea name="history_notes" rows="2" maxlength="2000"></textarea></label>
                <button class="button button-primary" type="submit">Save asset profile</button>
            </form>
            <form id="asset-photos-form" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="form-field"><span>Add asset photos</span><input name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required></label>
                <button class="button button-secondary" type="submit">Upload photos</button>
            </form>
        @endif
    </section>
</div>

@if ($canManageReports)
    <div class="modal-backdrop" id="report-manage-modal" hidden>
        <section class="dialog-card" role="dialog" aria-modal="true" aria-labelledby="report-manage-title">
            <button class="dialog-close" type="button" data-close-modal aria-label="Close dialog">×</button>
            <div class="eyebrow">OPERATIONS</div><h2 id="report-manage-title">Update environmental report</h2>
            <form id="report-manage-form" method="POST">
                @csrf @method('PATCH')
                <label class="form-field"><span>Status</span><select name="status" required><option value="new">New</option><option value="assigned">Assigned</option><option value="in_progress">In Progress</option><option value="completed">Completed</option><option value="rejected">Rejected</option></select></label>
                <label class="form-field"><span>Priority</span><select name="priority" required><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></label>
                <label class="form-field"><span>Assigned employee</span><select name="assigned_to"><option value="">Unassigned</option>@foreach ($employees as $employee)<option value="{{ $employee['id'] }}">{{ $employee['name'] }} · {{ $employee['role'] }}</option>@endforeach</select></label>
                <div class="dialog-actions"><button class="button button-secondary" type="button" data-close-modal>Cancel</button><button class="button button-primary" type="submit">Save report</button></div>
            </form>
        </section>
    </div>
@endif

@if ($canManageTasks)
    <div class="modal-backdrop" id="task-create-modal" hidden>
        <section class="dialog-card" role="dialog" aria-modal="true" aria-labelledby="task-create-title">
            <button class="dialog-close" type="button" data-close-modal aria-label="Close dialog">×</button>
            <div class="eyebrow">FIELD OPERATIONS</div><h2 id="task-create-title">Create environmental task</h2>
            <form method="POST" action="{{ route('greenfund.tasks.store') }}">
                @csrf
                <label class="form-field"><span>Task title</span><input name="title" maxlength="255" required></label>
                <label class="form-field"><span>Related report</span><select name="report_number"><option value="">No linked report</option>@foreach ($reports as $report)<option value="{{ $report['id'] }}">{{ $report['id'] }} · {{ $report['category'] }}</option>@endforeach</select></label>
                <label class="form-field"><span>Work category</span><select name="category" required><option>Waste removal</option><option>Tree inspection</option><option>Watering</option><option>Irrigation</option><option>Cleaning</option><option>Pruning</option><option>Other</option></select></label>
                <label class="form-field"><span>Location</span><input name="address" maxlength="255" required></label>
                <div class="settings-fields"><label class="form-field"><span>Priority</span><select name="priority" required><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></label><label class="form-field"><span>Deadline</span><input name="due_date" type="datetime-local" required></label></div>
                <label class="form-field"><span>Assigned executor</span><select name="assigned_to"><option value="">Unassigned</option>@foreach ($employees as $employee)@if ($employee['role'] !== 'Operator')<option value="{{ $employee['id'] }}">{{ $employee['name'] }} · {{ $employee['role'] }}</option>@endif @endforeach</select></label>
                <label class="form-field"><span>Work notes</span><textarea name="description" rows="2" maxlength="2000"></textarea></label>
                <div class="dialog-actions"><button class="button button-secondary" type="button" data-close-modal>Cancel</button><button class="button button-primary" type="submit">Create task</button></div>
            </form>
        </section>
    </div>
@endif

@if ($canManageAssets)
    <div class="modal-backdrop" id="asset-create-modal" hidden>
        <section class="dialog-card" role="dialog" aria-modal="true" aria-labelledby="asset-create-title">
            <button class="dialog-close" type="button" data-close-modal aria-label="Close dialog">×</button>
            <div class="eyebrow">GREEN INVENTORY</div><h2 id="asset-create-title">Register a green asset</h2>
            <form method="POST" action="{{ route('greenfund.assets.store') }}">
                @csrf
                <label class="form-field"><span>Asset name</span><input name="name" maxlength="255" required></label>
                <div class="settings-fields"><label class="form-field"><span>Asset type</span><select name="type" required><option value="tree">Tree</option><option value="park">Park</option><option value="square">Square</option><option value="green_area">Green area</option></select></label><label class="form-field"><span>Condition</span><select name="condition" required><option value="healthy">Healthy</option><option value="needs_watering">Needs watering</option><option value="diseased">Diseased</option><option value="drying">Drying</option><option value="pest_damage">Pest damage</option></select></label></div>
                <label class="form-field"><span>Species</span><input name="species" maxlength="255"></label>
                <label class="form-field"><span>Description</span><textarea name="description" rows="2" maxlength="2000"></textarea></label>
                <label class="form-field"><span>Location</span><input name="address" maxlength="255" required></label>
                <div class="settings-fields"><label class="form-field"><span>Latitude</span><input name="latitude" type="number" step="any" min="-90" max="90"></label><label class="form-field"><span>Longitude</span><input name="longitude" type="number" step="any" min="-180" max="180"></label></div>
                <div class="dialog-actions"><button class="button button-secondary" type="button" data-close-modal>Cancel</button><button class="button button-primary" type="submit">Register asset</button></div>
            </form>
        </section>
    </div>
@endif

@if ($canManageMaintenance)
    <div class="modal-backdrop" id="maintenance-create-modal" hidden>
        <section class="dialog-card" role="dialog" aria-modal="true" aria-labelledby="maintenance-create-title">
            <button class="dialog-close" type="button" data-close-modal aria-label="Close dialog">×</button>
            <div class="eyebrow">MAINTENANCE PLANNING</div><h2 id="maintenance-create-title">Schedule maintenance</h2>
            <form method="POST" action="{{ route('greenfund.maintenance.store') }}">
                @csrf
                <label class="form-field"><span>Work title</span><input name="title" maxlength="255" required></label>
                <div class="settings-fields"><label class="form-field"><span>Work type</span><select name="maintenance_type" required><option value="watering">Watering</option><option value="inspection">Tree inspection</option><option value="pruning">Pruning</option><option value="cleaning">Cleaning</option><option value="other">Other</option></select></label><label class="form-field"><span>Green asset</span><select name="green_asset_id"><option value="">No specific asset</option>@foreach ($maintenanceAssets as $asset)<option value="{{ $asset['id'] }}">{{ $asset['asset_number'] }} · {{ $asset['name'] }}</option>@endforeach</select></label></div>
                <label class="form-field"><span>Location</span><input name="location_label" maxlength="255" required></label>
                <div class="settings-fields"><label class="form-field"><span>Scheduled date and time</span><input name="scheduled_at" type="datetime-local" required></label><label class="form-field"><span>Responsible employee</span><select name="assigned_to"><option value="">Unassigned</option>@foreach ($employees as $employee)@if ($employee['role'] !== 'Operator')<option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>@endif @endforeach</select></label></div>
                <label class="form-field"><span>Related task</span><select name="task_id"><option value="">No linked task</option>@foreach ($tasks as $task)<option value="{{ $task['recordId'] }}">{{ $task['id'] }} · {{ $task['title'] }}</option>@endforeach</select></label>
                <label class="form-field"><span>Notes</span><textarea name="description" rows="2" maxlength="2000"></textarea></label>
                <div class="dialog-actions"><button class="button button-secondary" type="button" data-close-modal>Cancel</button><button class="button button-primary" type="submit">Schedule work</button></div>
            </form>
        </section>
    </div>
@endif
