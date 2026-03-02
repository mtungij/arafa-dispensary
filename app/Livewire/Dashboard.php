<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasToast;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-sidebar')]
class Dashboard extends Component
{
    use HasToast;

    public string $selectedTab = 'overview';

    public string $selectedFruit = '';

    public string $selectedStatus = '';

    public string $selectedCity = '';

    public array $selectedSkills = [];

    public array $selectedMembers = [];

    public string $invalidSelection = '';

    public string $disabledValue = '';

    public bool $switchValue = false;

    public bool $notificationsEnabled = true;

    public bool $darkModeSwitch = false;

    public string $sampleInput = '';

    public string $sampleTextarea = '';

    public function showSuccessToast(): void
    {
        $this->toastSuccess('Action completed successfully!');
    }

    public function showErrorToast(): void
    {
        $this->toastError('Something went wrong.');
    }

    public function showWarningToast(): void
    {
        $this->toastWarning('Proceed with caution.');
    }

    public function showInfoToast(): void
    {
        $this->toastInfo('Here is some information.');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }


    public function getDailyTotalsProperty()
{
    $companyId = Auth::user()->company_id;

    $today = now()->toDateString();

    $cashTotal = Invoice::where('company_id', $companyId)
        ->whereDate('created_at', $today)
        ->where('status', 'paid')
        ->where('patient_amount', '>', 0)
        ->sum('patient_amount');

    $insuranceTotal = Invoice::where('company_id', $companyId)
        ->whereDate('created_at', $today)
        ->where('status', 'covered_by_insurance')
        ->sum('insurance_amount');

        $cashCount = Invoice::where('company_id', $companyId)
    ->whereDate('created_at', $today)
    ->where('status', 'paid')
    ->where('patient_amount', '>', 0)
    ->count();

    $insuranceCount = Invoice::where('company_id', $companyId)
    ->whereDate('created_at', $today)
    ->where('status', 'covered_by_insurance')
    ->count();

    return [
        'cash' => $cashTotal,
        'insurance' => $insuranceTotal,
        'cashcount'=> $cashCount,
        'insurancecount' => $insuranceCount,
        'total' => $cashTotal + $insuranceTotal
    ];
}
}
