<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class ApiController extends Controller
{
    public function list(Request $request)
    {
        $limit = $request->input('limit');
        return Transaction::with(['details'])->paginate($limit);
    }

    public function detail(Request $request, $id)
    {
        // Cara 1
        // return Transaction::with(['details'])->find($id);

        // Cara 2
        return Transaction::where('id', $id)->with(['details'])->first();
    }

    public function store(Request $request)
    {
        //Mengambil semua request data dari JSON API
        $data = $request->all();

        //Mengambil semua id dari array collection products
        $productIds = collect($data['products'])->pluck('id');

        //Query untuk mengambil product dengan menggunakan whereIn id array
        $product = Product::whereIn('id', $productIds)->get();
        $total_amount = 0;

        foreach ($data['products'] as $value) {
            //melakukan pencarian produk berdasarkan id
            $product = $product->firstWhere('id', $value['id']);

            //menghitung total amount dari hasil $product
            $total_amount += ($product ? $product->price : 0) * $value['qty'];
        }

        //memulai session untuk query transaction
        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'id' => Uuid::uuid4()->toString(),
                'customer' => $data['customer_name'],
                'total_amount' => $total_amount
            ]);

            $transaction_details = [];

            foreach ($data['products'] as $key => $value) {
                $product = $product->firstWhere('id', $value['id']);
                $transaction_details[] = [
                    'id' => Uuid::uuid4()->toString(),
                    'transaction_id' => $transaction->id,
                    'product_id' => $value['id'],
                    'quantity' => $value['qty'],
                    'amount' => $product ? $product->price : 0,
                    'created_at' => Carbon::now()
                ];
            }

            if ($transaction_details) {
                TransactionDetail::insert($transaction_details);
            }
            //Menyimpan data create ke database
            $paymenturl = $this->createInvoice($transaction);
            DB::commit();
            return $paymenturl;
        } catch (\Throwable $th) {
            //melakukan rollback/membatalkan query jika terjadi kesalahan
            DB::rollBack();
            return $th;
        }
    }
    public function createInvoice($transaction){
         // set konnfigrasi midtrans ngambil dari config/midrtrans.php
         Config::$serverKey = config('midtrans.serverKey');
         Config::$isProduction = config('midtrans.isProduction');
         Config::$isSanitized = config('midtrans.isSanitized');
         Config::$is3ds = config('midtrans.is3ds');

         // buat params untuk dikirim ke midtrans
        $midtrans_params = [
            'transaction_details' =>[
                'order_id' => $transaction->id,
                'gross_amount' => (int) $transaction->total_amount //ditetapkan harus int yang dikirim
            ],
            'customer_details' =>[
                'first_name' => $transaction->customer,
                'email' => "rahmadhidayatputra47@gmail.com",
            ],
        ];
        $paymentUrl = Snap::createTransaction($midtrans_params)->redirect_url;
        return $paymentUrl;
    }
}
