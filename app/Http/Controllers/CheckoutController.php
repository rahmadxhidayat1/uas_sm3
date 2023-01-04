<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
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
                return Product::with('category')->paginate(100);
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
}
