<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <x-ui.heading level="h1" size="xl">Transactions</x-ui.heading>
        <x-ui.text class="mt-1 opacity-60">
            Manage your bank transactions.
        </x-ui.text>
    </div>

    {{-- Toolbar: Search + Add Button --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Search transactions..."
                icon="magnifying-glass"
            />
        </div>

        <x-ui.modal
            id="add-transaction-modal"
            heading="Add Transaction"
            description="Create a new bank transaction."
            width="md"
        >
            <x-slot:trigger>
                <x-ui.button icon="plus">
                    Add Transaction
                </x-ui.button>
            </x-slot:trigger>

            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>Description</x-ui.label>
                    <x-ui.input wire:model="newName" placeholder="e.g. Salary Deposit" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Amount</x-ui.label>
                    <x-ui.input wire:model="newAmount" type="number" step="0.01" placeholder="0.00" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Category</x-ui.label>
                    <x-ui.select wire:model="newCategory" placeholder="Select category">
                        <x-ui.select.option value="Income">Income</x-ui.select.option>
                        <x-ui.select.option value="Housing">Housing</x-ui.select.option>
                        <x-ui.select.option value="Food">Food</x-ui.select.option>
                        <x-ui.select.option value="Utilities">Utilities</x-ui.select.option>
                        <x-ui.select.option value="Entertainment">Entertainment</x-ui.select.option>
                        <x-ui.select.option value="Transport">Transport</x-ui.select.option>
                        <x-ui.select.option value="Health">Health</x-ui.select.option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Date</x-ui.label>
                    <x-ui.input wire:model="newDate" type="date" />
                </x-ui.field>
            </div>

            <x-slot:footer>
                <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                <x-ui.button wire:click="addTransaction">Add Transaction</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    </div>

    {{-- Transactions Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-neutral-700">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 bg-gray-50 dark:border-neutral-700 dark:bg-neutral-800/50">
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Date</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Description</th>
                    <th class="hidden px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400 sm:table-cell">Category</th>
                    <th class="hidden px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400 md:table-cell">Reference</th>
                    <th class="px-3 py-2 text-right font-medium text-neutral-600 dark:text-neutral-400">Amount</th>
                    <th class="px-3 py-2 text-right font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->filteredTransactions as $i => $txn)
                    <tr
                        class="border-b border-gray-300 transition-colors last:border-b-0 hover:bg-gray-50 dark:border-neutral-700 dark:hover:bg-neutral-800/40"
                        wire:key="txn-{{ $txn['ref'] }}"
                    >
                        <td class="whitespace-nowrap px-3 py-2 text-neutral-500 dark:text-neutral-400">
                            {{ \Carbon\Carbon::parse($txn['date'])->format('M d') }}
                        </td>
                        <td class="px-3 py-2 font-medium text-neutral-900 dark:text-neutral-100">
                            <div>{{ $txn['name'] }}</div>
                            {{-- Show category on mobile as subtitle --}}
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 sm:hidden">
                                {{ $txn['category'] }}
                            </div>
                        </td>
                        <td class="hidden px-3 py-2 sm:table-cell">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                {{ $txn['category'] }}
                            </span>
                        </td>
                        <td class="hidden whitespace-nowrap px-3 py-2 font-mono text-xs text-neutral-500 dark:text-neutral-400 md:table-cell">
                            {{ $txn['ref'] }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-right font-medium {{ $txn['type'] === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-900 dark:text-neutral-100' }}">
                            {{ $txn['type'] === 'credit' ? '+' : '-' }}${{ number_format($txn['amount'], 2) }}
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-end gap-1">
                                {{-- View --}}
                                <x-ui.modal
                                    id="view-txn-{{ $txn['ref'] }}"
                                    heading="Transaction Details"
                                    width="sm"
                                >
                                    <x-slot:trigger>
                                        <button class="rounded p-1 text-neutral-400 transition-colors hover:bg-gray-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200" title="View">
                                            <x-ui.icon name="eye" class="size-4" />
                                        </button>
                                    </x-slot:trigger>

                                    <dl class="space-y-3 text-sm">
                                        <div>
                                            <dt class="text-neutral-500 dark:text-neutral-400">Description</dt>
                                            <dd class="font-medium text-neutral-900 dark:text-neutral-100">{{ $txn['name'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-neutral-500 dark:text-neutral-400">Amount</dt>
                                            <dd class="font-medium {{ $txn['type'] === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-900 dark:text-neutral-100' }}">
                                                {{ $txn['type'] === 'credit' ? '+' : '-' }}${{ number_format($txn['amount'], 2) }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-neutral-500 dark:text-neutral-400">Category</dt>
                                            <dd class="text-neutral-900 dark:text-neutral-100">{{ $txn['category'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-neutral-500 dark:text-neutral-400">Date</dt>
                                            <dd class="text-neutral-900 dark:text-neutral-100">{{ \Carbon\Carbon::parse($txn['date'])->format('F d, Y') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-neutral-500 dark:text-neutral-400">Reference</dt>
                                            <dd class="font-mono text-xs text-neutral-900 dark:text-neutral-100">{{ $txn['ref'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-neutral-500 dark:text-neutral-400">Type</dt>
                                            <dd>
                                                <span @class([
                                                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' => $txn['type'] === 'credit',
                                                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' => $txn['type'] === 'debit',
                                                ])>
                                                    {{ ucfirst($txn['type']) }}
                                                </span>
                                            </dd>
                                        </div>
                                    </dl>

                                    <x-slot:footer>
                                        <x-ui.button variant="outline" x-on:click="$data.close()">Close</x-ui.button>
                                    </x-slot:footer>
                                </x-ui.modal>

                                {{-- Edit --}}
                                <button
                                    class="rounded p-1 text-neutral-400 transition-colors hover:bg-gray-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                                    title="Edit"
                                    wire:click="openEdit({{ $i }})"
                                    x-on:click.debounce.50ms="$nextTick(() => $modal.open('edit-transaction-modal'))"
                                >
                                    <x-ui.icon name="pencil-square" class="size-4" />
                                </button>

                                {{-- Delete --}}
                                <x-ui.modal
                                    id="delete-txn-{{ $txn['ref'] }}"
                                    heading="Delete Transaction"
                                    description="This action cannot be undone."
                                    icon="exclamation-triangle"
                                    width="sm"
                                >
                                    <x-slot:trigger>
                                        <button class="rounded p-1 text-neutral-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400" title="Delete">
                                            <x-ui.icon name="trash" class="size-4" />
                                        </button>
                                    </x-slot:trigger>

                                    <x-ui.text>
                                        Are you sure you want to delete <strong>{{ $txn['name'] }}</strong>?
                                    </x-ui.text>

                                    <x-slot:footer>
                                        <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                                        <x-ui.button color="red" wire:click="delete({{ $i }})" x-on:click="$data.close()">Delete</x-ui.button>
                                    </x-slot:footer>
                                </x-ui.modal>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-neutral-500 dark:text-neutral-400">
                            No transactions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Edit Transaction Modal (shared) --}}
    <x-ui.modal
        id="edit-transaction-modal"
        heading="Edit Transaction"
        description="Update the transaction details."
        width="md"
    >
        <div class="space-y-4">
            <x-ui.field>
                <x-ui.label>Description</x-ui.label>
                <x-ui.input wire:model="editName" placeholder="e.g. Salary Deposit" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Amount</x-ui.label>
                <x-ui.input wire:model="editAmount" type="number" step="0.01" placeholder="0.00" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Category</x-ui.label>
                <x-ui.select wire:model="editCategory" placeholder="Select category">
                    <x-ui.select.option value="Income">Income</x-ui.select.option>
                    <x-ui.select.option value="Housing">Housing</x-ui.select.option>
                    <x-ui.select.option value="Food">Food</x-ui.select.option>
                    <x-ui.select.option value="Utilities">Utilities</x-ui.select.option>
                    <x-ui.select.option value="Entertainment">Entertainment</x-ui.select.option>
                    <x-ui.select.option value="Transport">Transport</x-ui.select.option>
                    <x-ui.select.option value="Health">Health</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Date</x-ui.label>
                <x-ui.input wire:model="editDate" type="date" />
            </x-ui.field>
        </div>

        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
            <x-ui.button wire:click="saveEdit">Save Changes</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
