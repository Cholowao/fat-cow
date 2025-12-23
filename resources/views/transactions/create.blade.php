@extends('layouts.app')

@section('title', 'Add Transactions')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Add Transactions</h2>
            <div class="flex gap-2">
                <button type="button" onclick="addRow('credit')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                    Add Credit
                </button>
                <button type="button" onclick="addRow('debit')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                    Add Debit
                </button>
            </div>
        </div>
        
        <form id="bulkTransactionForm" class="p-6">
            <div class="mb-4">
                <label for="sharedDate" class="block text-sm font-medium text-gray-700 mb-1">Date (for all rows)</label>
                <input type="date" id="sharedDate" value="{{ date('Y-m-d') }}"
                    class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <!-- Transaction Rows Container -->
            <div id="transactionRows" class="space-y-4">
                <!-- Row template will be added here dynamically -->
            </div>

            <!-- Error message container -->
            <div id="form-error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm hidden mt-4"></div>

            <!-- Submit button -->
            <div class="mt-6 flex gap-3">
                <button type="submit" id="submitBtn"
                    class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Save All Transactions
                </button>
                <button type="button" onclick="clearAll()" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Clear All
                </button>
            </div>
        </form>
    </div>

    <!-- Quick summary -->
    <div class="mt-4 bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-sm text-gray-500">Total Credits</p>
                <p id="totalCredits" class="text-xl font-bold text-green-600">0.00</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Debits</p>
                <p id="totalDebits" class="text-xl font-bold text-red-600">0.00</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Net</p>
                <p id="netAmount" class="text-xl font-bold text-blue-600">0.00</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let rowCounter = 0;
    const today = '{{ date("Y-m-d") }}';

    // Row template HTML generator
    function createRowHTML(index) {
        return `
        <div class="transaction-row border-2 rounded-lg p-4 bg-green-50 border-green-300" data-index="${index}" data-type="credit">
            <div class="flex justify-between items-center mb-3">
                <span class="text-sm font-medium text-gray-600">Transaction #${index + 1}</span>
                <button type="button" onclick="removeRow(${index})" class="text-red-500 hover:text-red-700 text-sm">
                    <span class="inline-flex items-center">
                        <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Remove
                    </span>
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Type -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <input type="hidden" name="type_${index}" value="credit" class="type-input" />
                    <div class="inline-flex w-full rounded-lg overflow-hidden border border-gray-300">
                        <button type="button" onclick="setRowType(${index}, 'credit')" class="type-btn-credit w-1/2 px-3 py-2 text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition">
                            Credit
                        </button>
                        <button type="button" onclick="setRowType(${index}, 'debit')" class="type-btn-debit w-1/2 px-3 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-red-50 transition">
                            Debit
                        </button>
                    </div>
                </div>
                <!-- Amount -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Amount</label>
                    <input type="number" name="amount_${index}" step="0.01" min="0.01" required
                        class="amount-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="0.00" onchange="updateTotals()" oninput="updateTotals()">
                </div>
                <!-- Description -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <input type="text" name="description_${index}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="e.g., Bought 50kg beef">
                </div>
            </div>
            <!-- Category (collapsible) -->
            <div class="mt-2">
                <select name="category_${index}" class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Category (optional)</option>
                    <option value="Meat Purchase">Meat Purchase</option>
                    <option value="Transport">Transport</option>
                    <option value="Storage">Storage</option>
                    <option value="Partner Payment">Partner Payment</option>
                    <option value="Labor">Labor</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
        `;
    }

    // Add a new row
    function addRow(type = 'credit') {
        const container = document.getElementById('transactionRows');
        const div = document.createElement('div');
        div.innerHTML = createRowHTML(rowCounter);
        container.appendChild(div.firstElementChild);
        // Set initial type styling based on the button used
        setRowType(rowCounter, type);
        rowCounter++;
        updateTotals();
    }

    // Remove a row
    function removeRow(index) {
        const row = document.querySelector(`.transaction-row[data-index="${index}"]`);
        if (row) {
            row.remove();
            updateTotals();
        }
    }

    // Set row type via buttons (credit/debit) and refresh row colors + totals
    function setRowType(index, type) {
        const row = document.querySelector(`.transaction-row[data-index="${index}"]`);
        if (!row) return;

        // Update hidden value
        const input = row.querySelector('.type-input');
        if (input) input.value = type;

        // Update button styles
        const creditBtn = row.querySelector('.type-btn-credit');
        const debitBtn = row.querySelector('.type-btn-debit');
        if (creditBtn && debitBtn) {
            if (type === 'credit') {
                creditBtn.classList.add('bg-green-600', 'text-white');
                creditBtn.classList.remove('bg-white', 'text-gray-700');
                debitBtn.classList.add('bg-white', 'text-gray-700');
                debitBtn.classList.remove('bg-red-600', 'text-white');
            } else {
                debitBtn.classList.add('bg-red-600', 'text-white');
                debitBtn.classList.remove('bg-white', 'text-gray-700');
                creditBtn.classList.add('bg-white', 'text-gray-700');
                creditBtn.classList.remove('bg-green-600', 'text-white');
            }
        }

        // Update row background/border and totals
        updateRowColor(index);
        updateTotals();
    }

    // Update row background color based on type (green for credit, red for debit)
    function updateRowColor(index) {
        const row = document.querySelector(`.transaction-row[data-index="${index}"]`);
        const type = row.querySelector('.type-input')?.value || 'credit';
        
        // Remove existing color classes
        row.classList.remove('bg-green-50', 'border-green-300', 'bg-red-50', 'border-red-300');
        
        // Add new color classes based on type
        if (type === 'credit') {
            row.classList.add('bg-green-50', 'border-green-300');
        } else {
            row.classList.add('bg-red-50', 'border-red-300');
        }
        row.dataset.type = type;
    }

    // Clear all rows
    function clearAll() {
        if (confirm('Clear all transaction rows?')) {
            document.getElementById('transactionRows').innerHTML = '';
            rowCounter = 0;
            addRow('credit'); // Add one empty row
        }
    }

    // Update totals display
    function updateTotals() {
        let totalCredits = 0;
        let totalDebits = 0;

        document.querySelectorAll('.transaction-row').forEach(row => {
            const type = row.querySelector('.type-input')?.value || 'credit';
            const amount = parseFloat(row.querySelector('.amount-input').value) || 0;
            
            if (type === 'credit') {
                totalCredits += amount;
            } else {
                totalDebits += amount;
            }
        });

        document.getElementById('totalCredits').textContent = totalCredits.toFixed(2);
        document.getElementById('totalDebits').textContent = totalDebits.toFixed(2);
        
        const net = totalCredits - totalDebits;
        const netEl = document.getElementById('netAmount');
        netEl.textContent = (net >= 0 ? '+' : '') + net.toFixed(2);
        netEl.className = `text-xl font-bold ${net >= 0 ? 'text-green-600' : 'text-red-600'}`;
    }

    // Collect all transactions from rows
    function collectTransactions() {
        const transactions = [];
        const sharedDate = document.getElementById('sharedDate').value;

        if (!sharedDate) {
            return transactions;
        }

        document.querySelectorAll('.transaction-row').forEach(row => {
            const index = row.dataset.index;
            const type = row.querySelector(`[name="type_${index}"]`).value;
            const amount = row.querySelector(`[name="amount_${index}"]`).value;
            const description = row.querySelector(`[name="description_${index}"]`).value;
            const category = row.querySelector(`[name="category_${index}"]`).value;

            if (amount && description) {
                transactions.push({ type, amount, description, category, transaction_date: sharedDate });
            }
        });
        return transactions;
    }

    // Form submission
    const form = document.getElementById('bulkTransactionForm');
    const submitBtn = document.getElementById('submitBtn');
    const formError = document.getElementById('form-error');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const transactions = collectTransactions();

        if (!document.getElementById('sharedDate').value) {
            formError.textContent = 'Please select a date.';
            formError.classList.remove('hidden');
            return;
        }
        
        if (transactions.length === 0) {
            formError.textContent = 'Please add at least one valid transaction.';
            formError.classList.remove('hidden');
            return;
        }

        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        formError.classList.add('hidden');

        try {
            const response = await fetch('{{ route("transactions.store.bulk") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ transactions })
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message);
                // Reload page to reset form
                setTimeout(() => location.reload(), 500);
            } else if (data.errors) {
                // Show validation errors
                formError.textContent = 'Please check all fields are filled correctly.';
                formError.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save All Transactions';
            } else {
                formError.textContent = data.message || 'Failed to save transactions';
                formError.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save All Transactions';
            }
        } catch (error) {
            formError.textContent = 'An error occurred. Please try again.';
            formError.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save All Transactions';
        }
    });

    // Initialize with one row
    addRow('credit');
</script>
@endsection
