<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Menu;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::with('menu')->where('user_id', auth()->id())->get();
        $total = $carts->sum(fn ($item) => $item->menu->harga * $item->jumlah);

        return view('cart.index', compact('carts', 'total'));
    }

    public function store(Request $request, Menu $menu)
    {
        $request->validate([
            'jumlah' => 'nullable|integer|min:1|max:' . $menu->stok,
            'catatan' => 'nullable|string|max:255',
        ]);

        $jumlah = $request->input('jumlah', 1);

        $cart = Cart::where('user_id', auth()->id())
            ->where('menu_id', $menu->id)
            ->first();

        if ($cart) {
            $cart->update([
                'jumlah' => min($cart->jumlah + $jumlah, $menu->stok),
                'catatan' => $request->catatan ?? $cart->catatan,
            ]);
        } else {
            Cart::create([
                'user_id' => auth()->id(),
                'menu_id' => $menu->id,
                'jumlah' => $jumlah,
                'catatan' => $request->catatan,
            ]);
        }

        return back()->with('success', $menu->nama_lauk . ' masuk ke keranjang.');
    }

    public function update(Request $request, Cart $cart)
    {
        abort_if($cart->user_id !== auth()->id(), 403);

        $request->validate([
            'jumlah' => 'required|integer|min:1|max:' . $cart->menu->stok,
            'catatan' => 'nullable|string|max:255',
        ]);

        $cart->update($request->only('jumlah', 'catatan'));

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Cart $cart)
    {
        abort_if($cart->user_id !== auth()->id(), 403);
        $cart->delete();

        return back()->with('success', 'Item dihapus dari keranjang.');
    }
}