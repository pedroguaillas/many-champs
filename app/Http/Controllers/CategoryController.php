<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Del campeonato actual donde se encuentra logeado el usuario
        $categories = Category::where('team_id', $user->currentTeam->id)->get();

        return Inertia::render('Category/Index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:20',
            'inscription' => 'required|numeric',
            'gender' => 'required|max:20'
        ]);

        $inputs = $request->input();

        $user = Auth::user();

        // Agregar el id del Campeonato actual donde se encuentra logeado el usuario
        $inputs += ['team_id' => $user->currentTeam->id];

        $category = new Category($inputs);
        $category->save();
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|max:20',
            'inscription' => 'required|numeric',
            'gender' => 'required|max:20'
        ]);

        $category = $category->update($request->input());
    }

    public function destroy(Category $category)
    {
        $category->delete();
    }
}
