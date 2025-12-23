@extends('layouts.app')

@section('title', 'Statement')

@section('content')
<!-- Date Range Filter - hidden on print -->
<div class="bg-white rounded-lg shadow p-6 mb-6 no-print">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Generate Statement</h2>
    <form method="GET" action="{{ route('transactions.statement') }}" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[150px]">
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        </div>
        <div class="flex-1 min-w-[150px]">
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        </div>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
            Generate
        </button>
        <button type="button" onclick="window.print()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">
            <span class="inline-flex items-center">
                <svg class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M7 8V3h10v5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M6 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-1" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M7 14h10v7H7v-7Z" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                Print
            </span>
        </button>
    </form>
</div>

<!-- Statement Container - printable -->
<div class="bg-white rounded-lg shadow print-container">
    <!-- Statement Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Partner Account Statement</h1>
                <p class="text-sm text-gray-600 mt-1">Account Summary</p>
            </div>
            <div class="text-right text-sm text-gray-600">
                <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                <p><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase border border-gray-300 w-10 no-print"></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-gray-300">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-gray-300">Description</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase border border-gray-300">Debit</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase border border-gray-300">Credit</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase border border-gray-300">Balance</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase border border-gray-300 no-print">Actions</th>
                </tr>
            </thead>
            <tbody id="statementTransactions">
                @php $runningBalance = $openingBalance; @endphp

                <tr class="bg-gray-50">
                    <td class="px-2 py-3 text-center border border-gray-300 no-print">-</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border border-gray-300">
                        -
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-800 border border-gray-300">
                        Opening Balance
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600 border border-gray-300">-</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600 border border-gray-300">-</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold border border-gray-300 {{ $openingBalance >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        {{ $openingBalance >= 0 ? '' : '-' }}{{ number_format(abs($openingBalance), 2) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center border border-gray-300 no-print">
                        -
                    </td>
                </tr>
                
                @forelse($transactions as $transaction)
                @php
                    // Calculate running balance
                    if ($transaction->type === 'credit') {
                        $runningBalance += $transaction->amount;
                    } else {
                        $runningBalance -= $transaction->amount;
                    }
                @endphp
                <tr class="hover:bg-gray-50" data-id="{{ $transaction->id }}">
                    <td class="px-2 py-3 text-center border border-gray-300 cursor-move drag-handle no-print">
                        <svg class="h-5 w-5 text-gray-400 mx-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border border-gray-300">
                        {{ $transaction->transaction_date->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-800 border border-gray-300">
                        {{ $transaction->description }}
                        @if($transaction->category)
                        <span class="text-xs text-gray-500">({{ $transaction->category }})</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600 border border-gray-300">
                        {{ $transaction->type === 'debit' ? number_format($transaction->amount, 2) : '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600 border border-gray-300">
                        {{ $transaction->type === 'credit' ? number_format($transaction->amount, 2) : '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium border border-gray-300 {{ $runningBalance >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        {{ $runningBalance >= 0 ? '' : '-' }}{{ number_format(abs($runningBalance), 2) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center border border-gray-300 no-print">
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
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 border border-gray-300">
                        No transactions found for this period
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Summary</h3>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 border-collapse bg-white">
                <tbody>
                    <tr>
                        <td class="px-4 py-2 border border-gray-300 text-sm text-gray-700">Total Credits (Received)</td>
                        <td class="px-4 py-2 border border-gray-300 text-sm text-right font-semibold text-green-600">
                            {{ number_format($periodCredits, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 border border-gray-300 text-sm text-gray-700">Total Debits (Expenses)</td>
                        <td class="px-4 py-2 border border-gray-300 text-sm text-right font-semibold text-red-600">
                            {{ number_format($periodDebits, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 border border-gray-300 text-sm text-gray-700">Net Movement</td>
                        <td class="px-4 py-2 border border-gray-300 text-sm text-right font-semibold {{ ($periodCredits - $periodDebits) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ ($periodCredits - $periodDebits) >= 0 ? '+' : '-' }}{{ number_format(abs($periodCredits - $periodDebits), 2) }}
                        </td>
                    </tr>
                    <tr class="bg-blue-50">
                        <td class="px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-800">Closing Balance</td>
                        <td class="px-4 py-2 border border-gray-300 text-sm text-right font-bold {{ $closingBalance >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ $closingBalance >= 0 ? '' : '-' }}{{ number_format(abs($closingBalance), 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer for print -->
    <div class="px-6 py-4 border-t border-gray-200 text-center text-sm text-gray-500 print-only">
        <p>This is a computer-generated statement. No signature required.</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Initialize drag-and-drop for statement transactions
    document.addEventListener('DOMContentLoaded', function() {
        initSortableTable('statementTransactions');
    });
</script>
@endsection
