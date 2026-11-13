<?php

use App\Models\EnvironmentalReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    #[Validate('required|in:illegal_dumping,damaged_tree,air_pollution,water_pollution,green_space,other')]
    public string $category = '';

    #[Validate('required|string|max:400')]
    public string $description = '';

    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:10240')]
    public ?TemporaryUploadedFile $photo = null;

    #[Validate('required|numeric|between:-90,90')]
    public string $latitude = '44.8528';

    #[Validate('required|numeric|between:-180,180')]
    public string $longitude = '65.5092';

    #[Validate('required|string|max:255')]
    public string $locationLabel = 'Kyzylorda city center';

    #[Validate('nullable|string|max:2000')]
    public string $additionalInformation = '';

    public ?string $submittedCode = null;

    public int $reportCount = 0;

    public function mount(): void
    {
        $this->reportCount = EnvironmentalReport::query()->count();
    }

    public function submit(): void
    {
        $validated = $this->validate();
        $uploadDisk = (string) config('filesystems.public_upload_disk', 'public');
        $photoPath = $this->photo?->store('reports', $uploadDisk);

        if ($this->photo !== null && $photoPath === false) {
            throw new \RuntimeException('The report photo could not be stored.');
        }

        do {
            $reportNumber = 'ENV-'.Str::upper(Str::random(8));
        } while (EnvironmentalReport::query()->where('report_number', $reportNumber)->exists());

        $report = DB::transaction(function () use ($validated, $photoPath, $reportNumber): EnvironmentalReport {
            $report = EnvironmentalReport::create([
                'report_number' => $reportNumber,
                'user_id' => auth()->id(),
                'submitted_at' => now(),
                'category' => $validated['category'],
                'description' => $validated['description'],
                'additional_information' => $validated['additionalInformation'],
                'address' => $validated['locationLabel'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'priority' => 'medium',
                'status' => 'new',
            ]);

            if ($photoPath !== null) {
                $report->photos()->create([
                    'file_path' => $photoPath,
                    'photo_type' => 'before',
                    'uploaded_by' => auth()->id(),
                ]);
            }

            return $report;
        });

        $this->submittedCode = $report->report_number;
        $this->reportCount++;
        $this->reset('category', 'description', 'photo', 'additionalInformation');
    }
};
?>

<div>
    <div class="page-heading">
        <div>
            <div class="eyebrow">COMMUNITY <span class="eyebrow-divider">/</span> MAKE A DIFFERENCE</div>
            <h1>Report an environmental issue</h1>
            <p>Your report helps city teams keep Kyzylorda clean, safe, and green.</p>
        </div>
        <a class="button button-secondary" href="{{ route('greenfund.screen', ['page' => 'reports']) }}">Track a report</a>
    </div>

    <div class="submission-layout">
        <section class="panel submission-panel">
            <div class="form-step"><span>1</span><div><h2>Tell us what happened</h2><p>Share a few details so our team can help.</p></div></div>

            @if ($submittedCode)
                <div class="success-panel" role="status">
                    <span class="success-check">✓</span>
                    <div>
                        <strong>Report submitted successfully</strong>
                        <p>Your reference number is <b>{{ $submittedCode }}</b>. The Kyzylorda city team will review it shortly.</p>
                        <a href="{{ route('greenfund.screen', ['page' => 'reports']) }}">View all reports <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            @endif

            <form id="citizen-report-form" wire:submit="submit" enctype="multipart/form-data">
                <label class="form-field">
                    <span>Issue category <b>*</b></span>
                    <select name="category" wire:model="category" required>
                        <option value="" selected disabled>Select a category</option>
                        <option value="illegal_dumping">Illegal dumping</option><option value="air_pollution">Air pollution</option><option value="damaged_tree">Damaged tree</option>
                        <option value="green_space">Green space</option><option value="water_pollution">Water pollution</option>
                        <option value="other">Other environmental issue</option>
                    </select>
                    @error('category') <small class="upload-error">{{ $message }}</small> @enderror
                    <small>Choose the option that best describes the issue.</small>
                </label>

                <label class="form-field">
                    <span>Short description <b>*</b></span>
                    <textarea name="description" wire:model="description" rows="4" maxlength="400" placeholder="Describe what you noticed and when it started..." required></textarea>
                    @error('description') <small class="upload-error">{{ $message }}</small> @enderror
                    <small>Include helpful details such as size, nearby landmarks, or ongoing risks.</small>
                </label>

                <div class="form-field">
                    <span>Photo evidence <span class="optional-label">Optional</span></span>
                    <label class="upload-zone" for="report-photo">
                        <input id="report-photo" name="photo" wire:model="photo" type="file" accept="image/jpeg,image/png,image/webp" hidden>
                        <span class="upload-icon"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 13V4m0 0L6.5 7.5M10 4l3.5 3.5"/><path d="M4 12.5v3A1.5 1.5 0 0 0 5.5 17h9a1.5 1.5 0 0 0 1.5-1.5v-3"/></svg></span>
                        <span class="upload-copy"><strong>Click to upload <span>or drag and drop</span></strong><small>JPG, PNG or HEIC · max 10 MB</small></span>
                        <span class="upload-preview" id="photo-preview" @if (! $photo) hidden @endif>
                            @if ($photo)<img src="{{ $photo->temporaryUrl() }}" alt="Selected report photo preview">@endif
                        </span>
                    </label>
                    @error('photo') <small class="upload-error">{{ $message }}</small> @enderror
                </div>

                <div class="form-field">
                    <span>Location <b>*</b></span>
                    <p class="form-hint">Pin the issue on the map or use your current location.</p>
                    <div class="submission-map-wrap"><div id="submission-map" class="submission-map" aria-label="Choose the report location on the map"></div><button type="button" class="button button-secondary locate-button" id="use-my-location"><svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="5"/><path d="M10 2v3M10 15v3M2 10h3M15 10h3"/></svg> Use my location</button></div>
                    <input type="hidden" name="latitude" id="report-latitude" wire:model="latitude" required>
                    <input type="hidden" name="longitude" id="report-longitude" wire:model="longitude" required>
                    <input type="hidden" name="location_label" id="report-location-label" wire:model="locationLabel">
                    <div class="location-selected"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M16 8c0 4.5-6 9-6 9S4 12.5 4 8a6 6 0 1 1 12 0Z"/><circle cx="10" cy="8" r="2"/></svg><span id="selected-location">Kyzylorda city center</span><button id="clear-location" type="button" aria-label="Reset location">Reset</button></div>
                    @error('latitude') <small class="upload-error">{{ $message }}</small> @enderror
                    @error('longitude') <small class="upload-error">{{ $message }}</small> @enderror
                </div>

                <label class="form-field"><span>Additional information <span class="optional-label">Optional</span></span><textarea name="additionalInformation" wire:model="additionalInformation" rows="2" placeholder="Anything else the city team should know?"></textarea>@error('additionalInformation') <small class="upload-error">{{ $message }}</small> @enderror</label>
                <div class="form-consent"><span class="consent-icon">i</span><p>Your contact details will only be used to provide updates on this report. For emergencies, please contact <a href="tel:112">112</a>.</p></div>
                <button class="button button-primary submit-button" type="submit" wire:loading.attr="disabled" wire:target="submit,photo">Submit report <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M4 10h12m-5-5 5 5-5 5"/></svg></button>
            </form>
        </section>

        <aside class="submission-aside">
            <div class="community-card"><span class="community-icon"><svg viewBox="0 0 24 24"><path d="M12 20V9m0 7c-4.5 0-7.5-2.7-7.5-7.1C9 9 12 11.6 12 16Zm0-3.4c0-4.6 3-7.3 7.5-7.3 0 4.6-3 7.3-7.5 7.3Z"/></svg></span><h2>A greener city starts with you</h2><p>Every report is reviewed by the Kyzylorda city operations team. You can follow updates and see what happens next.</p><div class="community-stat"><strong>{{ $reportCount }}</strong><span>community reports recorded</span></div></div>
            <div class="how-it-works panel"><h3>What happens next?</h3><div><span>1</span><p><strong>We review your report</strong><small>Usually within one working day</small></p></div><div><span>2</span><p><strong>A city team is assigned</strong><small>You’ll see progress updates here</small></p></div><div><span>3</span><p><strong>Issue resolved</strong><small>We’ll share a completion photo</small></p></div></div>
        </aside>
    </div>
</div>