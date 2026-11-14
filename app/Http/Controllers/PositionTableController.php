<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Queries\Standings;
use Inertia\Inertia;

class PositionTableController extends Controller
{
    public function index(Category $category)
    {
        $clubs = Standings::forCategory($category);

        return Inertia::render('PositionTable', compact('category', 'clubs'));
    }
}
