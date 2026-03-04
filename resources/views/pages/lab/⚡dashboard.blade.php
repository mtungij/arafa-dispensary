<?php

use Livewire\Component;
use App\Models\Visit;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public function getPatientsProperty()
    {
        return Visit::with('patient')
            ->where('status', 'waiting_lab')
            ->where('current_department', 'lab')
            ->latest()
            ->get();
    }
};
?>

<div class="p-6 bg-gray-100 min-h-screen">

    <h2 class="text-2xl font-bold mb-6">Lab Queue</h2>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3">Visit #</th>
                    <th class="px-4 py-3">Patient Name</th>
                    <th class="px-4 py-3">Gender</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->patients as $visit)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">#{{ $visit->id }}</td>
                        <td class="px-4 py-3">
                            {{ $visit->patient->first_name }}
                            {{ $visit->patient->last_name }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $visit->patient->gender }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $visit->patient->phone }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">
                                {{ $visit->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href=""
                               class="bg-blue-600 text-white px-4 py-1 rounded">
                                Open
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            No patients waiting for lab
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>