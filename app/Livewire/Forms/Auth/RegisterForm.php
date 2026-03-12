<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;
use Livewire\WithFileUploads;
use App\Models\SystemRoute;
use App\Jobs\SendSmsJob;
final class RegisterForm extends Form
{
    use WithFileUploads;

    // Company Fields
    public string $company_name = '';
    public string $company_email = '';
    public string $company_phone = '';
    public $comp_logo;

    // Admin Fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public $departments = [''];


    public function addDepartment()
{
    $this->departments[] = '';
}

public function removeDepartment($index)
{
    unset($this->departments[$index]);
    $this->departments = array_values($this->departments);
}

  public function register(): void
{
    $this->validate([
        // Company validation
        'company_name' => ['required','string','min:3','max:255'],
        'company_email' => ['required','email','unique:companies,email'],
        'company_phone' => ['nullable','string','max:20'],
        'comp_logo' => ['nullable','image','max:2048'],

        // Admin validation
        'name' => ['required','string','min:3','max:255'],
        'email' => ['required','email','unique:users,email'],
        'password' => ['required','confirmed', Password::defaults()],
    ]);

    DB::transaction(function () use (&$user) {

    // Store logo
    $logoPath = null;
    if ($this->comp_logo) {
        $logoPath = $this->comp_logo->store('company_logos', 'public');
    }

    // Create company
    $company = Company::create([
        'name' => $this->company_name,
        'email' => $this->company_email,
        'phone' => $this->company_phone,
        'comp_logo' => $logoPath,
    ]);

    // Create admin user
$user = User::create([
    'name' => $this->name,
    'email' => $this->email,
    'phone'=> $this->company_phone,
    'password' => Hash::make($this->password),
    'role' => 'admin',
    'company_id' => $company->id,
]);

// SMS kwa admin
$messageAdmin = "Taarifa: Kampuni mpya imesajiliwa kwenye mfumo wa Helix.\n".
                "Jina la Kampuni: ".$this->company_name."\n".
                "Simu: ".$this->company_phone;

SendSmsJob::dispatch("255629364847", $messageAdmin);
SendSmsJob::dispatch("255747384847", $messageAdmin);

// SMS kwa company owner
$messageCompany = "Hongera ".$this->company_name."!\n".
                  "Usajili wako umefanikiwa.\n".
                  "Kama unahitaji msaada wasiliana nasi:\n".
                  "255629364847 au 255747384847.";

SendSmsJob::dispatch($this->company_phone, $messageCompany);

// ✅ Assign all routes using the helper method
$user->assignAllRoutes();

    // Create departments
    foreach ($this->departments as $deptName) {
        $deptName = trim($deptName);
        if ($deptName !== '') {
            $company->departments()->create([
                'name' => $deptName,
            ]);
        }
    }

});

    event(new Registered($user));
    Auth::login($user);

    // Reload company relationship for sidebar
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $user->load('company', 'company.departments');

}
public function debugSms()
{
    $testPhone = '255629364847'; // jaribu kwa namba yako
    $testMessage = 'Hii ni test ya SMS kutoka Helix system';

    $result = $this->sendsms($testPhone, $testMessage);

    // show result on browser console or Livewire
    dd($result); // au use logger
}



}