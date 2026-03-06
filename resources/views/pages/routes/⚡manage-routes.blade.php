<?php

use App\Models\SystemRoute;
use Livewire\Component;

use Livewire\Attributes\Layout;



new #[Layout('components.layouts.app-sidebar')] class extends Component
{
  public $name;
    public $label;

    public $routes = [];

    public function mount()
    {
        $this->loadRoutes();
    }

       public function loadRoutes()
    {
        $this->routes = SystemRoute::latest()->get()->toArray();
    }


    public function save()
    {
        $this->validate([
            'name' => 'required|unique:routes,name',
            'label' => 'required|string|max:255',
        ]);

      SystemRoute::create([
            'name' => $this->name,
            'label' => $this->label,
        ]);

        $this->reset(['name','label']);

        $this->loadRoutes();

        session()->flash('message','Route added successfully.');
    }

    public function delete($id)
    {
      SystemRoute::find($id)?->delete();

        $this->loadRoutes();
    }

};
?>
<div class="p-6">

    <h2 class="text-lg font-bold mb-4">System Routes</h2>

    @if(session()->has('message'))
        <div class="bg-green-100 text-green-700 p-2 mb-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white shadow p-4 rounded mb-6">

        <div class="grid grid-cols-2 gap-4">

            <div>
                <label class="text-sm">Route Name</label>
                <input type="text" wire:model="name"
                    class="w-full border rounded p-2"
                    placeholder="patients.index">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="text-sm">Label</label>
                <input type="text" wire:model="label"
                    class="w-full border rounded p-2"
                    placeholder="Patients">
                @error('label') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

        </div>

        <button wire:click="save"
            class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
            Save Route
        </button>

    </div>


    <table class="w-full border text-sm">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border">#</th>
                <th class="p-2 border">Route Name</th>
                <th class="p-2 border">Label</th>
                <th class="p-2 border">Action</th>
            </tr>
        </thead>

        <tbody>

            @forelse($routes as $index => $route)

                <tr>
                    <td class="border p-2">{{ $index + 1 }}</td>
                    <td class="border p-2">{{ $route['name'] }}</td>
                    <td class="border p-2">{{ $route['label'] }}</td>

                    <td class="border p-2">

                        <button
                            wire:click="delete({{ $route['id'] }})"
                            class="text-red-600">
                            Delete
                        </button>

                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="4" class="text-center p-4">
                        No routes added
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>