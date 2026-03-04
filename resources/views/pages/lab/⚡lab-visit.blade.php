<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Visit;
use Livewire\Attributes\Layout;

use Illuminate\Support\Facades\DB;

new #[Layout ('components.layouts.app-sidebar')] class extends Component
{
 
    use WithFileUploads;

    public $visit;
    public $results = []; // [request_id => result text]
    public $files = [];   // [request_id => uploaded files]

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

    // Live update status only visually — no new ENUM needed
    public function updatedResults($value, $requestId)
    {
        $this->visit->investigationRequests()->where('id', $requestId)->update([
            'result' => $this->results[$requestId],
        ]);

        $this->visit->refresh();
    }

    public function updatedFiles($value, $requestId)
    {
        $request = $this->visit->investigationRequests()->where('id', $requestId)->first();
        if (!$request) return;

        $savedFiles = $request->file_path ? json_decode($request->file_path, true) : [];

        foreach ($this->files[$requestId] as $file) {
            $savedFiles[] = $file->store('lab_results', 'public');
        }

        $request->update([
            'file_path' => json_encode($savedFiles),
        ]);

        $this->visit->refresh();
    }
};
?>

<div class="p-6 bg-gray-100 min-h-screen">

    <h2 class="text-2xl font-bold mb-4">
        Lab Processing - Visit #{{ $visit->id }}
    </h2>

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
        $isSubmitted = !empty($request->result) || ($request->file_path && count(json_decode($request->file_path, true)) > 0);
    @endphp
    <span class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-semibold
        {{ $isSubmitted ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
        {{ $isSubmitted ? 'Submitted' : 'Not Submitted' }}
    </span>

    <h3 class="font-semibold mb-2">{{ $request->investigation->name }}</h3>

    {{-- Result Textarea --}}
    <textarea
        wire:model.debounce.500ms="results.{{ $request->id }}"
        class="w-full border rounded p-3"
        rows="4"
        placeholder="Enter result here..."
    ></textarea>

    {{-- File Upload --}}
    <div class="mt-2">
        <input type="file" wire:model="files.{{ $request->id }}[]" multiple>
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