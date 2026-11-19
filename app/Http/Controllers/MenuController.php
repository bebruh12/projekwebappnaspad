<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Review;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::withAvg('reviews', 'rating');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $query->where('nama_lauk', 'like', '%' . $request->search . '%');
        }

        $menus = $query->latest()->get();

        return view('home', compact('menus'));
    }

    public function show(Menu $menu)
    {
        $menu->load('reviews.user');

        return view('menu.show', compact('menu'));
    }

    public function storeReview(Request $request, Menu $menu)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
        ]);

        Review::updateOrCreate(
            ['user_id' => auth()->id(), 'menu_id' => $menu->id],
            ['rating' => $request->rating, 'komentar' => $request->komentar]
        );

        return back()->with('success', 'Ulasan berhasil disimpan.');
    }
}