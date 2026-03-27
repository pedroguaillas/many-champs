<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function index(Category $category)
    {
        $groups = Group::where('category_id', $category->id)->get();

        return Inertia::render('Group/Index', compact('groups', 'category'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'alpha:ascii',
            'category_id' => 'exists:App\Models\Category,id'
        ]);

        // SEGURIDAD: Usar only() para prevenir mass assignment
        Group::create($request->only(['name', 'category_id']));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'alpha:ascii',
            'category_id' => 'exists:App\Models\Category,id'
        ]);

        // SEGURIDAD: Usar only() para prevenir mass assignment
        $group->update($request->only(['name', 'category_id']));
    }

    public function destroy(Group $group)
    {
        $group->delete();
    }
}
