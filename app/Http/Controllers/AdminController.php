<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalMenu = Menu::count();
        $pesananBaru = Order::where('status', 'menunggu')->count();
        $totalPendapatan = Order::where('status', 'selesai')->sum('total_harga');
        $recentOrders = Order::with('payment')->latest()->take(5)->get();

        return view('admin.dashboard', compact('totalMenu', 'pesananBaru', 'totalPendapatan', 'recentOrders'));
    }

    public function menus()
    {
        $menus = Menu::latest()->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function createMenu()
    {
        return view('admin.menus.create');
    }

    public function storeMenu(Request $request)
    {
        $data = $request->validate([
            'nama_lauk' => 'required|string|max:100',
            'kategori' => 'required|string|max:50',
            'harga' => 'required|integer|min:1000',
            'stok' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('menus', 'public');
        }

        Menu::create($data);

        return redirect()->route('admin.menus.index')->with('success', 'Lauk baru ditambahkan.');
    }

    public function editMenu(Menu $menu)
    {
        return view('admin.menus.edit', compact('menu'));
    }

    public function updateMenu(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'nama_lauk' => 'required|string|max:100',
            'kategori' => 'required|string|max:50',
            'harga' => 'required|integer|min:1000',
            'stok' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($menu->foto) {
                Storage::disk('public')->delete($menu->foto);
            }
            $data['foto'] = $request->file('foto')->store('menus', 'public');
        }

        $menu->update($data);

        return redirect()->route('admin.menus.index')->with('success', 'Data lauk diperbarui.');
    }

    public function destroyMenu(Menu $menu)
    {
        if ($menu->foto) {
            Storage::disk('public')->delete($menu->foto);
        }
        $menu->delete();

        return back()->with('success', 'Lauk dihapus.');
    }

    public function orders()
    {
        $orders = Order::with(['items.menu', 'payment', 'user'])->latest()->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:menunggu,diproses,selesai']);
        $order->update(['status' => $request->status]);

        return back()->with('success', 'Status pesanan diperbarui.');
    }

    public function verifyPayment(Payment $payment)
    {
        $payment->update([
            'status_bayar' => 'lunas',
            'tanggal_bayar' => now(),
        ]);

        return back()->with('success', 'Pembayaran diverifikasi lunas.');
    }
}