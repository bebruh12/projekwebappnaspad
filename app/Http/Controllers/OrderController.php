<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout()
    {
        $carts = Cart::with('menu')->where('user_id', auth()->id())->get();

        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $total = $carts->sum(fn ($item) => $item->menu->harga * $item->jumlah);

        return view('checkout.index', compact('carts', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pemesan' => 'required|string|max:100',
            'tipe_pesanan' => 'required|in:makan_di_tempat,bungkus',
            'metode_bayar' => 'required|in:tunai,qris,transfer',
            'bukti_bayar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $carts = Cart::with('menu')->where('user_id', auth()->id())->get();

        if ($carts->isEmpty()) {
            return redirect()->route('home');
        }

        $order = DB::transaction(function () use ($request, $carts) {
            $totalHarga = $carts->sum(fn ($item) => $item->menu->harga * $item->jumlah);

            $order = Order::create([
                'user_id' => auth()->id(),
                'nama_pemesan' => $request->nama_pemesan,
                'tipe_pesanan' => $request->tipe_pesanan,
                'total_harga' => $totalHarga,
                'status' => 'menunggu',
            ]);

            foreach ($carts as $cart) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $cart->menu_id,
                    'jumlah' => $cart->jumlah,
                    'subtotal' => $cart->menu->harga * $cart->jumlah,
                ]);

                $cart->menu->decrement('stok', $cart->jumlah);
            }

            $buktiPath = $request->hasFile('bukti_bayar')
                ? $request->file('bukti_bayar')->store('payments', 'public')
                : null;

            Payment::create([
                'order_id' => $order->id,
                'metode_bayar' => $request->metode_bayar,
                'bukti_bayar' => $buktiPath,
                'status_bayar' => $buktiPath ? 'menunggu_verifikasi' : 'belum_bayar',
                'tanggal_bayar' => $buktiPath ? now() : null,
            ]);

            Cart::where('user_id', auth()->id())->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan berhasil dibuat!');
    }

    public function index()
    {
        $orders = Order::with(['payment', 'items.menu'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        $order->load(['items.menu', 'payment']);

        return view('orders.show', compact('order'));
    }
}