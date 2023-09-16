<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Club;
use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClubController extends Controller
{
    public function index(Category $category)
    {
        $clubs = Club::select('g.name AS gname', 'clubs.*')
            ->leftJoin('groups AS g', 'g.id', 'group_id')
            ->where('clubs.category_id', $category->id)->get();

        $groups = Group::where('category_id', $category->id)->get();

        return Inertia::render('Club/Index', compact('category', 'clubs', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:300',
            'address' => 'required|max:300'
        ]);

        $club = new Club($request->all());
        $club->save();
    }

    public function update(Request $request, Club $club)
    {
        $request->validate([
            'name' => 'required|max:300',
            'address' => 'required|max:300'
        ]);

        $club->update($request->input());
    }

    public function destroy(Club $club)
    {
        $club->delete();
    }
}
