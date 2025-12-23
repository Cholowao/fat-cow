@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Balance Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <!-- Total Received (Credits) -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Money Received</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($totalCredits, 2) }}</p>
            </div>
            <div class="text-green-600">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4 7h16v10H4V7Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M7 10h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M17 12a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Spent (Debits) -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($totalDebits, 2) }}</p>
            </div>
            <div class="text-red-600">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M8 11l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 21h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Current Balance -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Current Balance</p>
                <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    {{ $balance >= 0 ? '' : '-' }}{{ number_format(abs($balance), 2) }}
                </p>
            </div>
            <div class="{{ $balance >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                @if($balance >= 0)
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @else
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 9v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M10.3 4.6 2.4 18.4A2 2 0 0 0 4.1 21h15.8a2 2 0 0 0 1.7-2.6L13.7 4.6a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    </svg>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="flex flex-wrap gap-3 mb-8">
    <a href="{{ route('transactions.create') }}" 
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
        <svg class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Add Transaction
    </a>
    <a href="{{ route('transactions.statement') }}" 
        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">
        <svg class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M7 3h8l4 4v14H7V3Z" stroke="currentColor" stroke-width="1.5"/>
            <path d="M9 11h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M9 15h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        View Statement
    </a>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Recent Transactions</h2>
    </div>
    
    @if($recentTransactions->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase border border-gray-300 w-10"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-gray-300">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-gray-300">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-gray-300">Type</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase border border-gray-300">Amount</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase border border-gray-300">Action</th>
                </tr>
            </thead>
            <tbody id="dashboardTransactions">
                @foreach($recentTransactions as $transaction)
                <tr class="hover:bg-gray-50" id="transaction-{{ $transaction->id }}" data-id="{{ $transaction->id }}">
                    <td class="px-2 py-4 text-center border border-gray-300 cursor-move drag-handle">
                        <svg class="h-5 w-5 text-gray-400 mx-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border border-gray-300">
                        {{ $transaction->transaction_date->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-800 border border-gray-300">
                        {{ $transaction->description }}
                        @if($transaction->category)
                        <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">{{ $transaction->category }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap border border-gray-300">
                        <span class="px-2 py-1 text-xs font-medium rounded {{ $transaction->type === 'credit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $transaction->type === 'credit' ? 'Credit' : 'Debit' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium border border-gray-300 {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center border border-gray-300">
                        <div class="inline-flex gap-2">
                            @php
                                $transactionPayload = [
                                    'id' => $transaction->id,
                                    'type' => $transaction->type,
                                    'amount' => (string) $transaction->amount,
                                    'description' => $transaction->description,
                                    'category' => $transaction->category,
                                    'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
                                ];
                            @endphp
                            <button type="button"
                                onclick="openEditModal(this)"
                                data-transaction='@json($transactionPayload)'

                                class="px-3 py-1 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
                                Edit
                            </button>
                            <button type="button"
                                onclick="deleteTransaction({{ $transaction->id }})"
                                class="px-3 py-1 rounded bg-red-600 text-white text-sm hover:bg-red-700">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="px-6 py-12 text-center text-gray-500">
        <p class="text-lg mb-2">No transactions yet</p>
        <p class="text-sm">Start by adding your first transaction</p>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Initialize drag-and-drop for dashboard transactions
    document.addEventListener('DOMContentLoaded', function() {
        initSortableTable('dashboardTransactions');
    });
</script>
@endsection
