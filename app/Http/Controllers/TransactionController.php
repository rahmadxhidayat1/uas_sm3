<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Midtrans\Config;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use Ramsey\Uuid\Uuid;
use App\Models\Product;
use App\Models\Category;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $data = Transaction::all();
        // return view('admin.pages.transaction.list',
        //  compact('data'), [
        //     'title' => 'List transaction'
        // ]);
        Config::$serverKey = config('midtrans.serverKey');
        $transactions = Transaction::get();
        $status = [];
        foreach ($transactions as $transaction) {
            try {
                $id = $transaction->id;
                $status[] = \Midtrans\Transaction::status("RBA-$id");
            } catch (\Exception $e) {
                $status[] = (object) [
                    "order_id" => "RBA-$id",
                    "transaction_status" => "unknown"
                ];
            }
        }
        return view('admin.pages.transaction.list', [
            'transactions' => $transactions,
            'status' => $status
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTransactionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransactionRequest $request)
    {
        // $products = [1, 2, 3, 4, 5, 6];

        // //memulai session untuk query transaction
        // DB::beginTransaction();
        // try {
        //     $transaction = Transaction::create([
        //         'id' => Uuid::uuid4()->toString(),
        //         'customer' => 'galang',
        //         'total_amount' => 200000
        //     ]);

        //     $transaction_details = [];

        //     foreach ($products as $key => $value) {
        //         $transaction_details[] = [
        //             'id' => Uuid::uuid4()->toString(),
        //             'transaction_id' => $transaction->id,
        //             'product_id' => $value,
        //             'quantity' => 20,
        //             'amount' => 3000,
        //             'created_at' => Carbon::now()
        //         ];
        //     }

        //     if ($transaction_details) {
        //         TransactionDetail::insert($transaction_details);
        //     }
        //     //Menyimpan data create ke database
        //     DB::commit();
        //     return "Data Masuk";
        // } catch (\Throwable $th) {
        //     //melakukan rollback/membatalkan query jika terjadi kesalahan
        //     DB::rollBack();
        //     return $th;
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        // $id = $transaction->id;
        // $details = TransactionDetail::where('transaction_id',$id)->with(['product'])->get();
        // return view('admin.pages.transaction.detail', [
        //     'details' => $details,
        //     'transactions'=>$transaction
        // ]);
        //------------------------------//
        Config::$serverKey = config('midtrans.serverKey');
        $id = $transaction->id;
        try {
            $status = \Midtrans\Transaction::status("RBA-$id");
        } catch (\Exception $e) {
            $status = (object) [
                "order_id" => "RBA-$id",
                "transaction_status" => "unknown"
            ];
        }
        $details = TransactionDetail::where('transaction_id', $id)->with(['product'])->get();
        return view('admin.pages.transaction.detail', [
            'details' => $details,
            'transaction' => $transaction,
            'status' => $status
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTransactionRequest  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
