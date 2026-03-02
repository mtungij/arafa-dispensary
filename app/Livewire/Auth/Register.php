<?php

declare(strict_types=1);

namespace App\Livewire\Auth;


use App\Constants;
use App\Livewire\Forms\Auth\RegisterForm;
use App\Support\Toast;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
final class Register extends Component
{

use WithFileUploads;
    public RegisterForm $form;

    public int $step = 1;

    public function nextStep(): void
    {
        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function register()
    {
        $this->form->register();

        Toast::success("Your account has been created successfully!");

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function addDepartment(): void
    {
        $this->form->addDepartment();
    }

    public function removeDepartment(int $index): void
    {
        $this->form->removeDepartment($index);
    }

    public function render()
    {
        /** @var View $view */
        $view = view('livewire.auth.register');

        return $view->layout('components.layouts.auth');
    }
}
