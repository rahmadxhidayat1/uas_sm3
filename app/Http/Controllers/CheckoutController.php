<?php

namespace App\Http\Controllers;
use Midtrans\Snap;
use Midtrans\Config;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use RealRashid\SweetAlert\Facades\Alert;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        if ($search) {
            $data = Product::with('category')->where('name', 'like', "%$search%")->paginate(100);
        } else {
            $products = Cache::remember('all-products', 60, function () {
                return Product::with('category')->paginate(50);
            });
            $data = $products;
        }
        return view('admin.pages.checkout.product', [
            'title' => 'List Product',
            'data' => $data
        ]);
    }
    public function create(Request $request)
    {
        $productID = $request->input('product_id');
        $qty = (int) $request->input('qty', 1);
        $checkout = [
            'products' => [],
            'user' => [
                'name' => '',
                'address' => '',
            ],
        ];
        $data = Cache::get('checkout', $checkout);
        $temp = null;
        if (isset($data['products'][$productID])) {
            $temp =  [
                "id" => $productID,
                "qty" => $qty + $data['products'][$productID]['qty']
            ];
        } else {
            $temp =  [
                "id" => $productID,
                "qty" => $qty
            ];
        }
        $data['products'][$productID] = $temp;

        Cache::put('checkout', $data);
        Alert::success('Checkout', 'Barang sudah ditambahkan');
        return redirect()->back();
    }

    public function store(Request $request){

        $data = $request->all();
        // dd($data);
        //Mengambil semua id dari array collection products
        $productIds = $data['product_ids'];
        $productPrices = $data['price'];
        $productQty = $data['qty'];

        // // //Query untuk mengambil product dengan menggunakan whereIn id array
        
        $product = Product::whereIn('id', $productIds)->get();

        //memulai session untuk query transaction
        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'id' => Uuid::uuid4()->toString(),
                'customer' => $data['name'],
                'total_amount' => $data['total'],
                'email' => $data['email'],
                'address' => $data['address'],
            ]);

            $transaction_details = [];

            foreach ($productIds as $key => $value) {
                $product = $product->firstWhere('id', $value);
                $transaction_details[] = [
                    'id' => Uuid::uuid4()->toString(),
                    'transaction_id' => $transaction->id,
                    'product_id' => $product['id'],
                    'quantity' => $productQty[$key],
                    'amount' => $productPrices[$key],
                    'created_at' => Carbon::now()
                ];
            }

            if ($transaction_details) {
                TransactionDetail::insert($transaction_details);
            }
            $paymentUrl = $this->createInvoice($transaction);
            cache()->flush();
            //Menyimpan data create ke database
            DB::commit();
            return redirect()->route('checkout.index')->with([
                Alert::html('apakah anda ingin melanjutkan pembayaran ?',
                "<a target='_blank' href='$paymentUrl'>Silahkan klik link ini untuk melanjutkan pembayaran</a>",'info')
            ]);
        } catch (\Throwable $th) {
            //melakukan rollback/membatalkan query jika terjadi kesalahan
            DB::rollBack();
            return $th;
        }
    }

    public function createInvoice($transaction)
    {
        // set konnfigrasi midtrans ngambil dari config/midrtrans.php
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');

        // buat params untuk dikirim ke midtrans
        $midtrans_params = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => (int) $transaction->total_amount //ditetapkan harus int yang dikirim
            ],
            'customer_details' => [
                'first_name' =>$transaction->customer,
                'email' => $transaction->email,
            ],
        ];
        $paymentUrl = Snap::createTransaction($midtrans_params)->redirect_url;
        return $paymentUrl;
    }

    public function chart()
    {
        $data = Cache::get('checkout');
        $prices = [];
        $qty = [];
        $id = [];
        if (!empty($data['products'])) {
            foreach ($data['products'] as $product) {
                $id[] = $product['id'];
                $qty[] = $product['qty'];
            }
            $products = Product::whereIn('id', $id)->get();
            foreach ($products as $product) {
                $prices[] = $product->price;
            }

            $totalPrice = 0;

            foreach ($prices as $key => $price) {
                $totalPrice += $price * $qty[$key];
            }

            return view('admin.pages.checkout.chart', ['data' => $data], [
                'title' => 'My Chart',
                'products' => $products,
                'totalprice' => $totalPrice,
            ]
        );
    }
}
}
