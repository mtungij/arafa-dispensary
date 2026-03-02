<?php

use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public $lastQueueCount = 0;
    public $doctorQueueCount = 0;

    public function mount()
    {
        $this->doctorQueueCount = $this->getQueueCount();
        $this->lastQueueCount   = $this->doctorQueueCount;
    }

    // This runs every poll
    public function refreshQueue()
    {
        $newCount = $this->getQueueCount();

        if ($newCount > $this->lastQueueCount) {
            $this->dispatch('new-patient-arrived');
        }

        $this->doctorQueueCount = $newCount;
        $this->lastQueueCount   = $newCount;
    }

    private function getQueueCount()
    {
        return Visit::where('company_id', Auth::user()->company_id)
            ->where('current_department', 'doctor')
            ->where('status', 'waiting_doctor')
            ->count();
    }

    public function getDoctorQueueProperty()
    {
        return Visit::with('patient')
            ->where('company_id', Auth::user()->company_id)
            ->where('current_department', 'doctor')
            ->whereIn('status', ['waiting_doctor','consultation'])
            ->orderBy('created_at')
            ->get();
    }

    public function startConsultation($visitId)
{
    $visit = Visit::findOrFail($visitId);

    $visit->update([
        'status' => 'consultation'
    ]);

    return redirect()->route('doctor.consultation', $visitId);
}
};
?>


<div wire:poll.5s="refreshQueue" class="p-6 space-y-6">

    <!-- ===================== -->
    <!-- STATS CARDS -->
    <!-- ===================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Cash Card -->
        <div class="bg-white shadow-lg rounded-2xl p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm text-gray-500 uppercase tracking-wide">
                        Cash Registration Today
                    </h3>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $this->doctorQueueCount }}
                    </p>
                </div>
                <div class="bg-green-100 text-green-600 p-3 rounded-xl">
                    💵
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-500">
                Patients currently waiting for doctor
            </div>
        </div>

     

    </div>


    <!-- ===================== -->
    <!-- SOUND CONTROLS -->
    <!-- ===================== -->
 
<div id="soundWrapper" class="flex items-center gap-4">
    <button 
        id="enableSoundBtn"
        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl shadow-md animate-pulse transition">
        🔔 Enable Notification Sound
    </button>

    <button 
        id="testSoundBtn"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl shadow-md transition">
        ▶ Test Sound
    </button>
</div>

    


    <!-- ===================== -->
    <!-- QUEUE TABLE -->
    <!-- ===================== -->
    <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">

        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700">
                Doctor Queue
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Patient</th>
                        <th class="px-6 py-3">Waiting Time</th>
                        <th class="px-6 py-3">Visit Type</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($this->doctorQueue as $index => $visit)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">
                                    {{ $visit->patient->first_name }}
                                    {{ $visit->patient->last_name }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span 
                                    class="waiting-time font-mono text-blue-600"
                                    data-time="{{ $visit->created_at->timestamp }}">
                                    --
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-xs">
                                    {{ ucfirst($visit->visit_type) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs">
                                    {{ ucfirst(str_replace('_',' ', $visit->status)) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <button
                                    wire:click="startConsultation({{ $visit->id }})"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-sm transition">
                                    Start
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                No patients waiting
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

<script>
document.addEventListener('livewire:init', () => {

    let audioCtx = null;
    let audioUnlocked = false;
    let lastPatientIds = [];

    const enableBtn = document.getElementById('enableSoundBtn');
    const testBtn   = document.getElementById('testSoundBtn');
    const wrapper   = document.getElementById('soundWrapper');

    function initAudio() {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
    }

    function playBeep(times = 1, interval = 200) {
        if (!audioUnlocked) return;
        initAudio();

        for (let i = 0; i < times; i++) {
            setTimeout(() => {
                const oscillator = audioCtx.createOscillator();
                const gainNode   = audioCtx.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(900, audioCtx.currentTime);
                gainNode.gain.setValueAtTime(0.25, audioCtx.currentTime);

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.3);
            }, i * interval);
        }
    }

    // Restore previous setting
    if (localStorage.getItem('soundEnabled') === 'true') {
        audioUnlocked = true;
        initAudio();
        if (wrapper) wrapper.style.display = 'none';
    }

    if (enableBtn) {
        enableBtn.addEventListener('click', () => {
            initAudio();
            audioCtx.resume();
            audioUnlocked = true;
            localStorage.setItem('soundEnabled', 'true');
            enableBtn.style.transition = "opacity 0.5s ease";
            enableBtn.style.opacity = 0;
            setTimeout(() => enableBtn.style.display = 'none', 500);
        });
    }

    if (testBtn) {
        testBtn.addEventListener('click', () => {
            initAudio();
            audioCtx.resume();
            audioUnlocked = true;
            playBeep();
        });
    }

    // ----------------------------
    // Livewire new patient highlight (multi-patient)
    // ----------------------------
    Livewire.on('new-patient-arrived', () => {
        const table = document.querySelector('tbody');
        if (!table) return;

        const currentIds = Array.from(table.querySelectorAll('tr[data-visit-id]'))
                                .map(row => row.dataset.visitId);

        // Identify new patients
        const newIds = currentIds.filter(id => !lastPatientIds.includes(id));
        if (newIds.length > 0) {
            playBeep(newIds.length); // Play beep once per new patient

            newIds.forEach(id => {
                const row = table.querySelector(`tr[data-visit-id="${id}"]`);
                if (row) {
                    row.classList.add('bg-green-100', 'animate-pulse');
                    setTimeout(() => row.classList.remove('bg-green-100', 'animate-pulse'), 2000);
                }
            });
        }

        lastPatientIds = currentIds;
    });

    // ----------------------------
    // Waiting time update
    // ----------------------------
    function updateWaitingTimes() {
        const now = Math.floor(Date.now() / 1000);
        document.querySelectorAll('.waiting-time').forEach(el => {
            const createdAt = parseInt(el.dataset.time);
            const diff = now - createdAt;
            if (isNaN(diff)) return;

            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            let display = '';
            if (hours > 0) display = `${hours}h ${minutes}m ${seconds}s`;
            else if (minutes > 0) display = `${minutes}m ${seconds}s`;
            else display = `${seconds}s`;

            if (el.innerText !== display) el.innerText = display;
        });
    }

    setInterval(updateWaitingTimes, 1000);
    updateWaitingTimes();

});
</script>

<script>
function updateWaitingTimes() {
    document.querySelectorAll('.waiting-time').forEach(function (el) {
        const createdAt = parseInt(el.dataset.time);
        const now = Math.floor(Date.now() / 1000);
        const diff = now - createdAt;

        const hours = Math.floor(diff / 3600);
        const minutes = Math.floor((diff % 3600) / 60);
        const seconds = diff % 60;

        let display = '';

        if (hours > 0) {
            display = `${hours}h ${minutes}m ${seconds}s`;
        } else if (minutes > 0) {
            display = `${minutes}m ${seconds}s`;
        } else {
            display = `${seconds}s`;
        }

        el.innerText = display;
    });
}

setInterval(updateWaitingTimes, 1000);
updateWaitingTimes();
</script>
</div>