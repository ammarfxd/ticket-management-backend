<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use COM;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class CategoryController extends Controller
{
    public function index()
    {
       $this->authorize('viewAny', Category::class);

       return CategoryResource::collection(
           Category::query()->latest()->paginate(20)
       );
    }

    public function store(Request $request)
    {   
        $this->authorize('create', Category::class);

        $data = $request->validate([
           'name' => ['required','string','max:80','unique:categories,name'] 
        ]);

        $slug = Str::slug($data['name']);

        $base = $slug;
        $i = 2;
        while(Category::where('slug', $slug)->exists()){
            $slug = "{$base}-{$i}";
            $i++;
        }

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return response()->json([
            'message' => 'Category created.',
            'data' => $category, 
        ],201);
    }

    public function show(Category $category)
    {
        $this->authorize('view', $category);

        return $category;
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:categories,name,' . $category->id],
        ]);

        $slug = Str::slug($data['name']);
        $base = $slug;
        $i = 2;
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        $category->update([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return response()->json([
            'message' => 'Category updated.',
            'data' => $category,
        ]);
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Category deleted.',
        ]);
    }

}
