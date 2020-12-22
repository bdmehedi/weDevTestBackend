<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Expr\New_;

class ProductController extends Controller
{
    /**
     * @return mixed
     */
    public function index()
    {
        return new ProductCollection(Product::latest()->get());
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
            'image' => 'required',
        ]);

        $directory = 'productImages/' . date('Y') . '/' . date('F');
        Storage::makeDirectory($directory, $mode = 0777, true, true);
        return DB::transaction(function () use ($request, $directory) {
            return Product::create(
                [
                    'title' => $request->title,
                    'description' => $request->description,
                    'image' => $request->file('image')->store($directory),
                    'price' => $request->price,
                ]
            );
        });
    }

    public function destroy(Request $request, Product $product)
    {
        Storage::delete($product->image);
        return $product->delete();
    }

    public function show(Request $request, Product $product)
    {
        return new \App\Http\Resources\Product($product);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
        ]);

        $directory = 'productImages/' . date('Y') . '/' . date('F');
        Storage::makeDirectory($directory, $mode = 0777, true, true);
        return DB::transaction(function () use ($request, $directory, $product) {
            if ($request->hasFile('image')) {
                Storage::delete($product->image);
                return $product->update(
                    [
                        'title' => $request->title,
                        'description' => $request->description,
                        'image' => $request->file('image')->store($directory),
                        'price' => $request->price,
                    ]
                );
            } else {
                return $product->update(
                    [
                        'title' => $request->title,
                        'description' => $request->description,
                        'price' => $request->price,
                    ]
                );
            }
        });
    }
}
