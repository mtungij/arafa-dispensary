<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-sidebar')]
final class Transactions extends Component
{
    public string $search = '';

    /** @var array<int, array<string, mixed>> */
    public array $transactions = [];

    public string $editName = '';

    public string $editAmount = '';

    public string $editCategory = '';

    public string $editDate = '';

    public int $editingIndex = -1;

    public string $newName = '';

    public string $newAmount = '';

    public string $newCategory = 'Income';

    public string $newDate = '';

    public function mount(): void
    {
        $this->transactions = [
            ['name' => 'Salary Deposit', 'amount' => 4500.00, 'type' => 'credit', 'category' => 'Income', 'date' => '2026-02-21', 'ref' => 'TXN-20260221-001'],
            ['name' => 'Rent Payment', 'amount' => 1200.00, 'type' => 'debit', 'category' => 'Housing', 'date' => '2026-02-20', 'ref' => 'TXN-20260220-002'],
            ['name' => 'Grocery Store', 'amount' => 87.45, 'type' => 'debit', 'category' => 'Food', 'date' => '2026-02-19', 'ref' => 'TXN-20260219-003'],
            ['name' => 'Freelance Payment', 'amount' => 750.00, 'type' => 'credit', 'category' => 'Income', 'date' => '2026-02-18', 'ref' => 'TXN-20260218-004'],
            ['name' => 'Electric Bill', 'amount' => 142.30, 'type' => 'debit', 'category' => 'Utilities', 'date' => '2026-02-17', 'ref' => 'TXN-20260217-005'],
            ['name' => 'Netflix Subscription', 'amount' => 15.99, 'type' => 'debit', 'category' => 'Entertainment', 'date' => '2026-02-16', 'ref' => 'TXN-20260216-006'],
            ['name' => 'Gas Station', 'amount' => 54.20, 'type' => 'debit', 'category' => 'Transport', 'date' => '2026-02-15', 'ref' => 'TXN-20260215-007'],
            ['name' => 'Client Invoice #312', 'amount' => 2100.00, 'type' => 'credit', 'category' => 'Income', 'date' => '2026-02-14', 'ref' => 'TXN-20260214-008'],
            ['name' => 'Restaurant Dinner', 'amount' => 63.50, 'type' => 'debit', 'category' => 'Food', 'date' => '2026-02-13', 'ref' => 'TXN-20260213-009'],
            ['name' => 'Gym Membership', 'amount' => 45.00, 'type' => 'debit', 'category' => 'Health', 'date' => '2026-02-12', 'ref' => 'TXN-20260212-010'],
        ];

        $this->newDate = now()->format('Y-m-d');
    }

    /** @return array<int, array<string, mixed>> */
    public function getFilteredTransactionsProperty(): array
    {
        if (blank($this->search)) {
            return $this->transactions;
        }

        $search = mb_strtolower($this->search);

        return array_values(array_filter($this->transactions, function (array $txn) use ($search): bool {
            return str_contains(mb_strtolower($txn['name']), $search)
                || str_contains(mb_strtolower($txn['category']), $search)
                || str_contains(mb_strtolower($txn['ref']), $search);
        }));
    }

    public function openEdit(int $index): void
    {
        $txn = $this->transactions[$index] ?? null;

        if (! $txn) {
            return;
        }

        $this->editingIndex = $index;
        $this->editName = $txn['name'];
        $this->editAmount = (string) $txn['amount'];
        $this->editCategory = $txn['category'];
        $this->editDate = $txn['date'];
    }

    public function saveEdit(): void
    {
        if ($this->editingIndex < 0) {
            return;
        }

        $this->transactions[$this->editingIndex]['name'] = $this->editName;
        $this->transactions[$this->editingIndex]['amount'] = (float) $this->editAmount;
        $this->transactions[$this->editingIndex]['category'] = $this->editCategory;
        $this->transactions[$this->editingIndex]['date'] = $this->editDate;

        $this->editingIndex = -1;

        $this->dispatch('close-modal', id: 'edit-transaction-modal');
    }

    public function addTransaction(): void
    {
        $type = in_array($this->newCategory, ['Income']) ? 'credit' : 'debit';
        $ref = 'TXN-'.now()->format('Ymd').'-'.str_pad((string) (count($this->transactions) + 1), 3, '0', STR_PAD_LEFT);

        array_unshift($this->transactions, [
            'name' => $this->newName,
            'amount' => (float) $this->newAmount,
            'type' => $type,
            'category' => $this->newCategory,
            'date' => $this->newDate,
            'ref' => $ref,
        ]);

        $this->reset('newName', 'newAmount', 'newCategory');
        $this->newCategory = 'Income';
        $this->newDate = now()->format('Y-m-d');

        $this->dispatch('close-modal', id: 'add-transaction-modal');
    }

    public function delete(int $index): void
    {
        unset($this->transactions[$index]);
        $this->transactions = array_values($this->transactions);
    }

    public function render()
    {
        return view('livewire.transactions');
    }
}
