<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Visit;
use App\Models\PatientMovement;
use Livewire\Attributes\Layout;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    use WithFileUploads;

    public $visit;
    public $results = [];
    public $files = [];

    public function mount($id)
    {
        $this->visit = Visit::with([
            'patient',
            'investigationRequests.investigation'
        ])
        ->where('company_id', auth()->user()->company_id)
        ->findOrFail($id);

        foreach ($this->visit->investigationRequests as $request) {
            $this->results[$request->id] = $request->result ?? '';
            $this->files[$request->id] = [];
        }
    }

    public function isLocked($request)
{
    // Lock if visit has moved to doctor
    return $this->visit->status === 'waiting_doctor' || $request->status === 'completed';
}

    /*
    |--------------------------------------------------------------------------
    | LIVE SAVE RESULTS
    |--------------------------------------------------------------------------
    */
    public function updatedResults($value, $key)
    {
        $requestId = $key;

        DB::transaction(function () use ($requestId, $value) {

            $request = $this->visit->investigationRequests
                ->firstWhere('id', $requestId);

            if (!$request) return;

            // Save to DB
            $request->update([
                'result' => $value,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Sync in-memory collection (CRITICAL)
            $request->result = $value;
            $request->status = 'completed';
            $request->completed_at = now();
        });

        $this->checkAllCompleted();
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE SAVE FILES
    |--------------------------------------------------------------------------
    */
    public function updatedFiles($value, $key)
    {
        $requestId = $key;

        DB::transaction(function () use ($requestId, $value) {

            $request = $this->visit->investigationRequests
                ->firstWhere('id', $requestId);

            if (!$request) return;

            $savedFiles = $request->file_path
                ? json_decode($request->file_path, true)
                : [];

            foreach ($value as $file) {
                $savedFiles[] = $file->store('lab_results', 'public');
            }

            $request->update([
                'file_path' => json_encode($savedFiles),
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Sync memory
            $request->file_path = json_encode($savedFiles);
            $request->status = 'completed';
            $request->completed_at = now();
        });

        // Clear file input (IMPORTANT)
        $this->files[$requestId] = [];

        $this->checkAllCompleted();
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK IF ALL INVESTIGATIONS COMPLETED
    |--------------------------------------------------------------------------
    */
    protected function checkAllCompleted()
    {
        // Use LOADED COLLECTION (NOT DB QUERY)
        $remaining = $this->visit->investigationRequests
            ->where('status', '!=', 'completed')
            ->count();

        if ($remaining === 0 && $this->visit->status !== 'waiting_doctor') {

            DB::transaction(function () {

                // Move visit back to doctor
                $this->visit->update([
                    'status' => 'waiting_doctor',
                    'current_department' => 'doctor',
                ]);

                // Prevent duplicate movements
                $alreadyMoved = PatientMovement::where('visit_id', $this->visit->id)
                    ->where('from_department', 'lab')
                    ->where('to_department', 'doctor')
                    ->exists();

                if (!$alreadyMoved) {
                    PatientMovement::create([
                        'visit_id' => $this->visit->id,
                        'from_department' => 'lab',
                        'to_department' => 'doctor',
                        'moved_at' => now(),
                    ]);
                }
            });
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD LAB REPORT
    |--------------------------------------------------------------------------
    */
   public function downloadReport()
{
    $visit = $this->visit->load([
        'patient',
        'investigationRequests.investigation'
    ]);

    // Map the results from component state
    foreach ($visit->investigationRequests as $request) {
        if (isset($this->results[$request->id])) {
            $request->result = $this->results[$request->id];
        }
    }

    $html = view('exports.lab-report', compact('visit'))->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'tempDir' => storage_path('app/mpdf'),
    ]);

    $mpdf->WriteHTML($html);

    return response()->streamDownload(
        fn () => print($mpdf->Output('', 'S')),
        "Lab_Report_Visit_{$visit->id}.pdf"
    );
}
}
?>

<div class="p-6 bg-gray-100 min-h-screen">

    <h2 class="text-2xl font-bold mb-4">
        Lab Processing - Visit #{{ $visit->id }}
    </h2>
<button 
    wire:click="downloadReport"
    class="bg-green-600 text-white px-4 py-2 rounded mb-4">
    Download Lab Report
</button>
    <div class="bg-white p-4 rounded shadow mb-6">
        <p><strong>Patient:</strong> {{ $visit->patient->first_name }} {{ $visit->patient->last_name }}</p>
        <p><strong>Gender:</strong> {{ $visit->patient->gender }}</p>
        <p><strong>Phone:</strong> {{ $visit->patient->phone }}</p>
    </div>

    <div class="space-y-6">
@forelse($visit->investigationRequests as $request)
<div class="bg-white p-4 rounded shadow mb-4 relative">

    {{-- Status Badge (Submitted / Not Submitted) --}}
  
 @php
$isSubmitted = !empty($results[$request->id]) || (isset($files[$request->id]) && count($files[$request->id]) > 0);
@endphp
   
  <span class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-semibold
    {{ $isSubmitted ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
    {{ $isSubmitted ? 'Submitted' : 'Not Submitted' }}
</span>

    <h3 class="font-semibold mb-2">{{ $request->investigation->name }}</h3>

    {{-- Result Textarea --}}
<textarea
    wire:model.live.debounce.500ms="results.{{ $request->id }}"
    wire:loading.attr="disabled"
    wire:target="results.{{ $request->id }}"
    class="w-full border rounded p-3"
    rows="4"
></textarea>

{{-- Live saving indicator --}}
<div class="mt-1 flex items-center space-x-2">
    <svg wire:loading wire:target="results.{{ $request->id }}, files.{{ $request->id }}" class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
    <span wire:loading wire:target="results.{{ $request->id }}, files.{{ $request->id }}" class="text-sm text-blue-600">
        Saving...
    </span>
</div>

    {{-- File Upload --}}
    <div class="mt-2">
        <input type="file" wire:model="files.{{ $request->id }}[]" multiple  @if($this->isLocked($request)) disabled @endif>>
        @error('files.'.$request->id) <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        {{-- Preview new uploads --}}
        @if(isset($files[$request->id]) && count($files[$request->id]))
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($files[$request->id] as $file)
                    @if(in_array($file->extension(), ['jpg','jpeg','png']))
                        <img src="{{ $file->temporaryUrl() }}" class="h-24 w-auto border rounded">
                    @elseif($file->extension() === 'pdf')
                        <a href="{{ $file->temporaryUrl() }}" target="_blank" class="text-blue-600 underline">Preview PDF</a>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Preview existing saved files --}}
        @if($request->file_path)
            @php
                $savedFiles = json_decode($request->file_path, true);
            @endphp
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($savedFiles as $savedFile)
                    @if(Str::endsWith($savedFile, ['jpg','jpeg','png']))
                        <img src="{{ Storage::url($savedFile) }}" class="h-24 w-auto border rounded">
                    @elseif(Str::endsWith($savedFile, 'pdf'))
                        <a href="{{ Storage::url($savedFile) }}" target="_blank" class="text-blue-600 underline">View PDF</a>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
@empty
<div class="bg-white p-4 rounded shadow text-gray-500">
    No investigations found.
</div>
@endforelse
</div>