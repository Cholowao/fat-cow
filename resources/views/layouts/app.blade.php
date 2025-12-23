<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Partner Account') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- SortableJS for drag-and-drop reordering -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <!-- Custom print styles -->
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            @page { margin: 0; }
            html, body { background: white !important; margin: 0 !important; padding: 0 !important; }
            main { max-width: none !important; margin: 0 !important; padding: 0 !important; }
            .print-pad { padding: 0 !important; }
            .print-container { 
                box-shadow: none !important; 
                border: none !important;
                padding: 0 !important;
            }
        }
        .print-only { display: none; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation - hidden on print -->
    <nav class="bg-white shadow-sm no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo/Brand -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">
                        <span class="inline-flex items-center">
                            <svg class="h-6 w-6 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 2C7.029 2 3 6.029 3 11v3c0 4.971 4.029 9 9 9s9-4.029 9-9v-3c0-4.971-4.029-9-9-9Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M7 11h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M9 15h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            Partner Account
                        </span>
                    </a>
                </div>
                
                <!-- Navigation Links -->
                @auth
                <div class="hidden sm:flex sm:items-center sm:space-x-4">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Dashboard
                    </a>
                    <a href="{{ route('transactions.create') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Add Transaction
                    </a>
                    <a href="{{ route('transactions.statement') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Statement
                    </a>
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center">
                    <span class="text-sm text-gray-600 mr-4 hidden sm:block">{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50">
                            Logout
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
        
        <!-- Mobile menu -->
        @auth
        <div class="sm:hidden border-t border-gray-200 pb-3">
            <div class="space-y-1 px-2 pt-2">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">
                    Dashboard
                </a>
                <a href="{{ route('transactions.create') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">
                    Add Transaction
                </a>
                <a href="{{ route('transactions.statement') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">
                    Statement
                </a>
            </div>
        </div>
        @endauth
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 no-print">
        <div class="bg-white w-full max-w-lg rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Edit Transaction</h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <form id="editTransactionForm" class="p-6 space-y-4">
                <input type="hidden" id="edit_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select id="edit_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="credit">Credit (Received)</option>
                        <option value="debit">Debit (Expense)</option>
                    </select>
                    <p class="text-red-500 text-sm mt-1 hidden" id="edit_type_error"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input type="number" step="0.01" min="0.01" id="edit_amount" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" />
                    <p class="text-red-500 text-sm mt-1 hidden" id="edit_amount_error"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" id="edit_description" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" />
                    <p class="text-red-500 text-sm mt-1 hidden" id="edit_description_error"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category (Optional)</label>
                    <input type="text" id="edit_category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" />
                    <p class="text-red-500 text-sm mt-1 hidden" id="edit_category_error"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" id="edit_transaction_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" />
                    <p class="text-red-500 text-sm mt-1 hidden" id="edit_transaction_date_error"></p>
                </div>

                <div id="edit_form_error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm hidden"></div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" id="editSubmitBtn" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Save
                    </button>
                    <button type="button" onclick="closeEditModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast notification container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 no-print"></div>

    <!-- Base JavaScript for AJAX and notifications -->
    <script>
        // Show toast notification
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg mb-2 transform transition-all duration-300`;
            toast.textContent = message;
            container.appendChild(toast);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Initialize drag-and-drop for transaction tables
        function initSortableTable(tbodyId) {
            const tbody = document.getElementById(tbodyId);
            if (!tbody) return;

            new Sortable(tbody, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-blue-100',
                onEnd: async function() {
                    // Collect all transaction IDs in new order
                    const rows = tbody.querySelectorAll('tr[data-id]');
                    const order = Array.from(rows).map(row => parseInt(row.dataset.id));

                    if (order.length === 0) return;

                    try {
                        const response = await fetch('/transactions/reorder', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ order })
                        });

                        const data = await response.json();
                        if (data.success) {
                            showToast('Order saved!');
                        } else {
                            showToast(data.message || 'Failed to save order', 'error');
                        }
                    } catch (error) {
                        showToast('Failed to save order', 'error');
                    }
                }
            });
        }

        function openEditModal(buttonEl) {
            const t = JSON.parse(buttonEl.dataset.transaction);

            document.getElementById('edit_id').value = t.id;
            document.getElementById('edit_type').value = t.type;
            document.getElementById('edit_amount').value = t.amount;
            document.getElementById('edit_description').value = t.description;
            document.getElementById('edit_category').value = t.category || '';
            document.getElementById('edit_transaction_date').value = t.transaction_date;

            clearEditErrors();

            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function clearEditErrors() {
            document.getElementById('edit_form_error').classList.add('hidden');
            const ids = ['type', 'amount', 'description', 'category', 'transaction_date'];
            ids.forEach((f) => {
                const el = document.getElementById(`edit_${f}_error`);
                if (el) {
                    el.textContent = '';
                    el.classList.add('hidden');
                }
            });
        }

        async function deleteTransaction(id) {
            if (!confirm('Are you sure you want to delete this transaction?')) return;

            try {
                const response = await fetch(`/transactions/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Transaction deleted!');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.message || 'Failed to delete', 'error');
                }
            } catch (error) {
                showToast('An error occurred', 'error');
            }
        }

        document.getElementById('editTransactionForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = document.getElementById('editSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            clearEditErrors();

            const id = document.getElementById('edit_id').value;

            try {
                const response = await fetch(`/transactions/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: document.getElementById('edit_type').value,
                        amount: document.getElementById('edit_amount').value,
                        description: document.getElementById('edit_description').value,
                        category: document.getElementById('edit_category').value,
                        transaction_date: document.getElementById('edit_transaction_date').value,
                    })
                });

                const data = await response.json();

                if (response.status === 422 && data.errors) {
                    Object.keys(data.errors).forEach((field) => {
                        const el = document.getElementById(`edit_${field}_error`);
                        if (el) {
                            el.textContent = data.errors[field][0];
                            el.classList.remove('hidden');
                        }
                    });
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save';
                    return;
                }

                if (!response.ok || !data.success) {
                    const box = document.getElementById('edit_form_error');
                    box.textContent = data.message || 'Failed to update transaction';
                    box.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save';
                    return;
                }

                showToast(data.message || 'Updated');
                closeEditModal();
                setTimeout(() => location.reload(), 500);
            } catch (error) {
                const box = document.getElementById('edit_form_error');
                box.textContent = 'An error occurred. Please try again.';
                box.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save';
            }
        });
    </script>

    @yield('scripts')
</body>
</html>
