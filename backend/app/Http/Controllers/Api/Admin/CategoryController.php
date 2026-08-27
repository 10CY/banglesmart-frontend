<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
{
    $categories = Category::with('parent:id,name')
        ->withCount('children')
        ->orderBy('sort_order')
        ->orderBy('id', 'desc')
        ->get();

    $categories = $categories->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image' => $category->image,
            'status' => $category->status,
            'sort_order' => $category->sort_order,
            'parent_id' => $category->parent_id,
            'parent' => $category->parent
                ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                ]
                : null,
            'children_count' => $category->children_count,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $categories,
    ]);
}

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'status' => ['required', Rule::in(['active', 'inactive'])],
        'sort_order' => ['nullable', 'integer', 'min:0'],
        'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        'image' => ['nullable', 'image', 'max:2048'],
    ]);


    $baseSlug = Str::slug($validated['name']);

    $slug = $baseSlug;

    $count = 1;


    while (Category::where('slug', $slug)->exists()) {

        $slug = $baseSlug . '-' . $count++;

    }



    // Upload image

    $imagePath = null;


    if ($request->hasFile('image')) {

        $imagePath =
            $request->file('image')
            ->store('categories', 'public');

    }




    $category = Category::create([

        'name' => $validated['name'],

        'slug' => $slug,

        'description' =>
            $validated['description'] ?? null,

        'image' =>
            $imagePath,

        'status' =>
            $validated['status'],

        'sort_order' =>
            $validated['sort_order'] ?? 0,

        'parent_id' =>
            $validated['parent_id'] ?? null,

    ]);



    return response()->json([
        'success' => true,
        'message' => 'Category created successfully.',
        'data' => $category->load('parent:id,name'),
    ], 201);
}

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'parent_id' => [
                    'nullable',
                    'integer',
                    'exists:categories,id',
                    Rule::notIn([$category->id]),
                ],

                'image' => [
                    'nullable',
                    'image',
                    'max:2048',
                ],
            ]);

        if ($category->name !== $validated['name']) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $count = 1;

            while (
                Category::where('slug', $slug)
                    ->where('id', '!=', $category->id)
                    ->exists()
            ) {
                $slug = $baseSlug . '-' . $count++;
            }

            $category->slug = $slug;
        }

        // Prevent circular category trees.
        if (!empty($validated['parent_id'])) {
            $parentId = (int) $validated['parent_id'];
            $cursor = Category::find($parentId);
            while ($cursor) {
                if ((int) $cursor->id === (int) $category->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A category cannot be placed inside itself or its own child category.',
                    ], 422);
                }
                $cursor = $cursor->parent_id ? Category::find($cursor->parent_id) : null;
            }
        }

        $category->parent_id = $validated['parent_id'] ?? null;
        $category->name = $validated['name'];
        $category->description = $validated['description'] ?? null;
        $category->status = $validated['status'];
        $category->sort_order = $validated['sort_order'] ?? 0;
        if ($request->hasFile('image')) {
                $category->image =
                    $request->file('image')
                    ->store('categories','public');
            }
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category->fresh('parent:id,name'),
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }
}
