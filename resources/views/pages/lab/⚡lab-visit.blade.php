<?php

use App\Models\PatientMovement;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mpdf\Mpdf;

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
            'investigationRequests.investigation',
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

            if (! $request) {
                return;
            }

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

            if (! $request) {
                return;
            }

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

                if (! $alreadyMoved) {
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
            'investigationRequests.investigation',
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
            fn () => print ($mpdf->Output('', 'S')),
            "Lab_Report_Visit_{$visit->id}.pdf"
        );
    }
}
?>

<div class="p-6 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 min-h-screen">

    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">
            Lab Processing
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Complete investigation results for Visit #{{ $visit->id }}
        </p>
    </div>

    <!-- Patient Card -->
    <div class="mb-8">
        <x-patient-card :patient="$visit->patient" :visit="$visit" />
    </div>

    <!-- Download Section -->
    <div class="mb-8 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
            Investigation Results
        </h2>
        <button
            wire:click="downloadReport"
            class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-6 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 font-semibold flex items-center gap-2">
            <span>📥</span>
            Download Lab Report
        </button>
    </div>

    <div class="space-y-6">
        @forelse($visit->investigationRequests as $request)
            @php
                $isSubmitted = !empty($results[$request->id]) || (isset($files[$request->id]) && count($files[$request->id]) > 0);
            @endphp
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow border border-gray-100 dark:border-slate-700 relative">

                {{-- Status Badge (Submitted / Not Submitted) --}}
                <span class="absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1
                    {{ $isSubmitted ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' }}">
                    <span class="inline-block w-2 h-2 rounded-full {{ $isSubmitted ? 'bg-green-600' : 'bg-blue-600' }}"></span>
                    {{ $isSubmitted ? 'Submitted' : 'Pending' }}
                </span>

                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-4">{{ $request->investigation->name }}</h3>

                {{-- Result Textarea --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Investigation Result</label>
                    <textarea
                        wire:model.live.debounce.500ms="results.{{ $request->id }}"
                        wire:loading.attr="disabled"
                        wire:target="results.{{ $request->id }}"
                        class="w-full border border-gray-300 dark:border-slate-600 rounded-lg p-4 bg-white dark:bg-slate-700 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        rows="5"
                        placeholder="Enter investigation results..."
                    ></textarea>

                    {{-- Live saving indicator --}}
                    <div class="mt-2 flex items-center gap-2">
                        <svg wire:loading wire:target="results.{{ $request->id }}, files.{{ $request->id }}" class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span wire:loading wire:target="results.{{ $request->id }}, files.{{ $request->id }}" class="text-xs text-blue-600 dark:text-blue-400 font-medium">Saving changes...</span>
                        <svg wire:loading.remove wire:target="results.{{ $request->id }}, files.{{ $request->id }}" class="h-4 w-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>

                {{-- File Upload --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Attach Reports/Images</label>
                    <div class="relative">
                        <input
                            type="file"
                            wire:model="files.{{ $request->id }}[]"
                            multiple
                            @if($this->isLocked($request)) disabled @endif
                            class="w-full border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-lg p-4 bg-gray-50 dark:bg-slate-700/50 cursor-pointer hover:border-blue-400 transition"
                        />
                        @error('files.'.$request->id) <span class="text-red-600 dark:text-red-400 text-sm mt-2">{{ $message }}</span> @enderror

                        {{-- File upload loading indicator --}}
                        <div wire:loading wire:target="files.{{ $request->id }}" class="absolute inset-0 bg-orange-50 dark:bg-orange-900/20 rounded-lg flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-orange-600 dark:text-orange-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span class="text-sm font-medium text-orange-600 dark:text-orange-400">Uploading files...</span>
                        </div>
                    </div>

                    {{-- Preview new uploads --}}
                    @if(isset($files[$request->id]) && count($files[$request->id]))
                        <div class="mt-4">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-3">New Files</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($files[$request->id] as $file)
                                    @if(in_array($file->extension(), ['jpg','jpeg','png']))
                                        <div class="relative group">
                                            <img src="{{ $file->temporaryUrl() }}" class="h-24 w-full object-cover border border-gray-200 dark:border-slate-600 rounded-lg shadow-sm group-hover:shadow-md transition">
                                            <span class="absolute top-1 right-1 bg-blue-500 text-white text-xs px-2 py-0.5 rounded">{{ strtoupper($file->extension()) }}</span>
                                        </div>
                                    @elseif($file->extension() === 'pdf')
                                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800 rounded-lg p-3 flex items-center justify-center text-center">
                                            <a href="{{ $file->temporaryUrl() }}" target="_blank" class="text-red-700 dark:text-red-300 text-sm font-semibold hover:underline">📄 PDF</a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Preview existing saved files --}}
                    @if($request->file_path)
                        @php
                            $savedFiles = json_decode($request->file_path, true);
                        @endphp
                        <div class="mt-4">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-3">Saved Files</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($savedFiles as $savedFile)
                                    @if(Str::endsWith($savedFile, ['jpg','jpeg','png']))
                                        <div class="relative group">
                                            <img src="{{ Storage::url($savedFile) }}" class="h-24 w-full object-cover border border-gray-200 dark:border-slate-600 rounded-lg shadow-sm group-hover:shadow-md transition">
                                            <span class="absolute top-1 right-1 bg-green-500 text-white text-xs px-2 py-0.5 rounded">Saved</span>
                                        </div>
                                    @elseif(Str::endsWith($savedFile, 'pdf'))
                                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800 rounded-lg p-3 flex items-center justify-center text-center">
                                            <a href="{{ Storage::url($savedFile) }}" target="_blank" class="text-red-700 dark:text-red-300 text-sm font-semibold hover:underline">📄 View PDF</a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-800 p-12 rounded-xl border border-gray-200 dark:border-slate-700 text-center">
                <div class="text-4xl mb-3">📋</div>
                <p class="text-gray-500 dark:text-gray-400 font-medium">No investigations found for this visit</p>
            </div>
        @endforelse
    </div>
</div>