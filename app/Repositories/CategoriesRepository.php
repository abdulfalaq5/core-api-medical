<?php

namespace App\Repositories;

use App\Models\Categories;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CategoriesRepository
{
    protected $categories;

    public function __construct(Categories $categories)
    {
        $this->categories = $categories;
    }

    public function getAllCategories()
    {
        return $this->categories->all();
    }

    public function store(Request $request)
    {
        return $this->categories->create($request->all());
    }

    public function update(Request $request, $id)
    {
        return $this->categories->find($id)->update($request->all());
    }

    public function destroy($id)
    {
        return $this->categories->find($id)->delete();
    }
} 