<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    // Create New Transaction
    public function index(Request $request, $account_id)
    {
        // Check if Account exists
        if (Account::where('id', $account_id)->where('user_id', $request->user()->id)->doesntExist()) {
            // Return Failed response
            return response('Bad request !', 400);
        }

        // Filter by type and date
        if ($request->query('type') && $request->query('date_from') && $request->query('date_to')) {

            // Dates
            $date_from = date($request->query('date_from'));
            $date_to = date($request->query('date_to'));

            return Transaction::where('account_id', $account_id)
                ->orderBy('date', 'desc')
                ->where('type', $request->query('type'))
                ->whereBetween('date', [$date_from, $date_to])
                ->select('id', 'type', 'date', 'amount', 'details')->paginate(10);
        }

        // Filter by type
        if ($request->query('type')) {
            return Transaction::where('account_id', $account_id)
                ->orderBy('date', 'desc')
                ->where('type', $request->query('type'))
                ->select('id', 'type', 'date', 'amount', 'details')->paginate(10);
        }

        // Get Transactions
        return Transaction::where('account_id', $account_id)
            ->orderBy('date', 'desc')
            ->select('id', 'type', 'date', 'amount', 'details')->paginate(10);
    }

    // Create New Chart
    public function chart(Request $request, $account_id)
    {
        // Check if Account exists
        if (Account::where('id', $account_id)->where('user_id', $request->user()->id)->doesntExist()) {
            // Return Failed response
            return response('Bad request !', 400);
        }

        // Conditions
        $this->validate($request, [
            'type' => "required|string|in:i/e,i/s,e/s",
            'date_from' => "bail|required|date",
            'date_to' => "bail|required|date"
        ]);

        // Bar Colours
        $colours = [
            'income' => '#00B28F',
            'expense' => '#F8D12F',
            'savings' => '#6B58E2'
        ];

        // Dates
        $date_from = date($request->date_from);
        $date_to = date($request->date_to);


        if ($request->type == 'i/e') {
            $income_transactions = Transaction::where('account_id', $account_id)->where('type', 'income')->whereBetween('date', [$date_from, $date_to])->get();
            $expense_transactions = Transaction::where('account_id', $account_id)->where('type', 'expense')->whereBetween('date', [$date_from, $date_to])->get();

            $income_total_amount = 0;
            $expense_total_amount = 0;

            foreach ($income_transactions as $value) {
                $income_total_amount += $value->amount;
            }
            foreach ($expense_transactions as $value) {
                $expense_total_amount += $value->amount;
            }

            $total_ie_amount = $income_total_amount + $expense_total_amount;


            $income_ratio = ($income_total_amount / $total_ie_amount) * 100;
            $expense_ratio = ($expense_total_amount / $total_ie_amount) * 100;

            $data = [
                ["Element", "Density", ['role' => "style"]],
                ["Income", round($income_ratio, 2), $colours['income']],
                ["Expenses", round($expense_ratio, 2), $colours['expense']],
            ];

            return response()->json(['data' => $data], 200);
        }
        if ($request->type == 'i/s') {
            $income_transactions = Transaction::where('account_id', $account_id)->where('type', 'income')->whereBetween('date', [$date_from, $date_to])->get();
            $savings_transactions = Transaction::where('account_id', $account_id)->where('type', 'savings')->whereBetween('date', [$date_from, $date_to])->get();

            $income_total_amount = 0;
            $savings_total_amount = 0;

            foreach ($income_transactions as $value) {
                $income_total_amount += $value->amount;
            }
            foreach ($savings_transactions as $value) {
                $savings_total_amount += $value->amount;
            }

            $total_ie_amount = $income_total_amount + $savings_total_amount;


            $income_ratio = ($income_total_amount / $total_ie_amount) * 100;
            $savings_ratio = ($savings_total_amount / $total_ie_amount) * 100;

            $data = [
                ["Element", "Density", ['role' => "style"]],
                ["Income", round($income_ratio, 2), $colours['income']],
                ["Savings", round($savings_ratio, 2), $colours['savings']],
            ];

            return response()->json(['data' => $data], 200);
        }
        if ($request->type == 'e/s') {
            $savings_transactions = Transaction::where('account_id', $account_id)->where('type', 'savings')->whereBetween('date', [$date_from, $date_to])->get();
            $expense_transactions = Transaction::where('account_id', $account_id)->where('type', 'expense')->whereBetween('date', [$date_from, $date_to])->get();

            $savings_total_amount = 0;
            $expense_total_amount = 0;

            foreach ($savings_transactions as $value) {
                $savings_total_amount += $value->amount;
            }
            foreach ($expense_transactions as $value) {
                $expense_total_amount += $value->amount;
            }

            $total_ie_amount = $savings_total_amount + $expense_total_amount;


            $savings_ratio = ($savings_total_amount / $total_ie_amount) * 100;
            $expense_ratio = ($expense_total_amount / $total_ie_amount) * 100;

            $data = [
                ["Element", "Density", ['role' => "style"]],
                ["Expenses", round($expense_ratio, 2), $colours['expense']],
                ["Savngs", round($savings_ratio, 2), $colours['savings']],
            ];

            return response()->json(['data' => $data], 200);
        }
    }


    // Create New Transaction
    public function store(Request $request, $account_id)
    {
        // Check if Account exists
        if (Account::where('id', $account_id)->where('user_id', $request->user()->id)->doesntExist()) {
            // Return Failed response
            return response('Bad request !', 400);
        }

        // Validation
        $this->validate($request, [
            'type' => 'required|string|in:income,expense,savings',
            'date' => 'bail|required|date',
            'amount' => 'bail|required|integer',
            'details' => 'bail|required|string|max:1500',
        ]);

        // Create transaction
        Transaction::create([
            'type' => $request->type,
            'date' => $request->date,
            'amount' => $request->amount,
            'details' => $request->details,
            'account_id' => $account_id
        ]);

        // Return response
        return response('success', 200);
    }

    // Delete a transaction
    public function destroy(Request $request, $id)
    {
        // Remove transaction
        Transaction::where('id', $id)->delete();

        // Return response
        return response('success', 200);
    }
}
