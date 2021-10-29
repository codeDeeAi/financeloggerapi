<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    // Show all accounts
    public function index(Request $request)
    {
        // Get all accounts created by user
        return Account::where('user_id', $request->user()->id)->orderBy('name')->select('id', 'name', 'description')->get();
    }

    // Create New Account
    public function store(Request $request)
    {
        // Validation
        $this->validate($request, [
            'name' => 'required|string|max:200',
            'description' => 'bail|required|string|max:1500',
            'currency' => 'bail|required|string|in:Naira,Dollars,Pounds'
        ]);

        // Create Account
        Account::create([
            'name' => $request->name,
            'user_id' => $request->user()->id,
            'description' => $request->description,
            'currency' => $request->currency
        ]);

        // Return response
        return response('success', 200);
    }

    // Show Account 
    public function show(Request $request, $id)
    {
        // Get all accounts created by user
        return Account::where('user_id', $request->user()->id)->where('id', $id)->orderBy('name')->select('name', 'currency', 'description')->get();
    }

    // Update Existing Account
    public function update(Request $request, $id)
    {
        // Check if account exists and is created by user
        if (Account::where('id', $id)->where('user_id', $request->user()->id)->doesntExist()) {
            // Return Failed response
            return response('Bad request !', 400);
        }

        // Validation
        $this->validate($request, [
            'type' => 'required|string|in:name,currency,description',
        ]);


        // If updating name
        if ($request->type == 'name') {
            // Validation
            $this->validate($request, [
                'value' => 'required|string|max:200'
            ]);

            // Update Values
            Account::where('id', $id)->update([
                'name' => $request->value
            ]);

            // Return response
            return response('success', 200);
        }
        if ($request->type == 'currency') {
            // Validation
            $this->validate($request, [
                'value' => 'required|string|in:Naira,Dollars,Pounds'
            ]);

            // Update Values
            Account::where('id', $id)->update([
                'currency' => $request->value
            ]);

            // Return response
            return response('success', 200);
        } else {
            // Validation
            $this->validate($request, [
                'value' => 'required|string|max:1500'
            ]);

            // Update Values
            Account::where('id', $id)->update([
                'description' => $request->value
            ]);

            // Return response
            return response('success', 200);
        }
    }

    // Delete Account 
    public function destroy(Request $request, $id)
    {

        DB::beginTransaction();

        try {
            // Delete Account
            Account::where('user_id', $request->user()->id)->where('id', $id)->delete();

            // Delete Transactions
            Transaction::where('account_id', $id)->delete();

            // Commit to DB
            DB::commit();

            // Return response
            return response('success', 200);
        } catch (\Throwable $th) {
            // Commit to DB
            DB::rollBack();

            // Return response
            return response('failed !', 500);
        }
    }
}
