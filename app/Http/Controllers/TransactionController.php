<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // Show dashboard with balance summary
    public function index()
    {
        $user = Auth::user();
        
        // Calculate totals
        $totalCredits = Transaction::where('user_id', $user->id)->credits()->sum('amount');
        $totalDebits = Transaction::where('user_id', $user->id)->debits()->sum('amount');
        $balance = $totalCredits - $totalDebits;
        
        // Get recent transactions ordered by sort_order (for drag reordering)
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('sort_order', 'asc')
            ->take(10)
            ->get();

        return view('dashboard', compact('totalCredits', 'totalDebits', 'balance', 'recentTransactions'));
    }

    // Show form to add transaction
    public function create()
    {
        return view('transactions.create');
    }

    // Store new transaction (single)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
        ]);

        $validated['user_id'] = Auth::id();

        Transaction::create($validated);

        return response()->json(['success' => true, 'message' => 'Transaction added successfully!']);
    }

    // Store multiple transactions at once (bulk)
    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'transactions' => 'required|array|min:1',
            'transactions.*.type' => 'required|in:credit,debit',
            'transactions.*.amount' => 'required|numeric|min:0.01',
            'transactions.*.description' => 'required|string|max:255',
            'transactions.*.category' => 'nullable|string|max:100',
            'transactions.*.transaction_date' => 'required|date',
        ]);

        $userId = Auth::id();
        $count = 0;

        // Insert each transaction
        foreach ($validated['transactions'] as $txn) {
            $txn['user_id'] = $userId;
            Transaction::create($txn);
            $count++;
        }

        return response()->json([
            'success' => true, 
            'message' => "{$count} transaction(s) added successfully!"
        ]);
    }

    // Update a transaction (edit)
    public function update(Request $request, Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Validate input
        $validated = $request->validate([
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
        ]);

        // Update record
        $transaction->update($validated);

        return response()->json(['success' => true, 'message' => 'Transaction updated successfully!']);
    }

    // Show statement page with filters
    public function statement(Request $request)
    {
        $user = Auth::user();
        
        // Default to current month if no dates provided
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Calculate opening balance (all transactions before start date)
        $openingCredits = Transaction::where('user_id', $user->id)
            ->credits()
            ->where('transaction_date', '<', $startDate)
            ->sum('amount');
        $openingDebits = Transaction::where('user_id', $user->id)
            ->debits()
            ->where('transaction_date', '<', $startDate)
            ->sum('amount');
        $openingBalance = $openingCredits - $openingDebits;

        // Get transactions in date range ordered by sort_order (for drag reordering)
        $transactions = Transaction::where('user_id', $user->id)
            ->dateRange($startDate, $endDate)
            ->orderBy('sort_order', 'asc')
            ->get();

        // Calculate period totals
        $periodCredits = $transactions->where('type', 'credit')->sum('amount');
        $periodDebits = $transactions->where('type', 'debit')->sum('amount');
        $closingBalance = $openingBalance + $periodCredits - $periodDebits;

        return view('transactions.statement', compact(
            'transactions',
            'startDate',
            'endDate',
            'openingBalance',
            'periodCredits',
            'periodDebits',
            'closingBalance'
        ));
    }

    // Delete a transaction
    public function destroy(Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $transaction->delete();

        return response()->json(['success' => true, 'message' => 'Transaction deleted!']);
    }

    // Reorder transactions (drag-and-drop)
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:transactions,id',
        ]);

        $userId = Auth::id();

        // Update sort_order for each transaction
        foreach ($validated['order'] as $position => $transactionId) {
            Transaction::where('id', $transactionId)
                ->where('user_id', $userId)
                ->update(['sort_order' => $position]);
        }

        return response()->json(['success' => true, 'message' => 'Order updated!']);
    }
}
