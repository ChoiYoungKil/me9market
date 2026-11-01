<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductsImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Throwable;

class AdminProductImageUpload
{
    public function store(int $productId, array $uploads): void
    {
        $writtenPaths = [];
        try {
            DB::transaction(function () use ($productId, $uploads, &$writtenPaths) {
                Product::whereKey($productId)->lockForUpdate()->firstOrFail();
                foreach ($uploads as $upload) {
                    $filename = Str::uuid().'.'.$upload->extension();
                    foreach (['large' => 1000, 'medium' => 500, 'small' => 250] as $size => $limit) {
                        $directory = public_path('front/images/product_images/'.$size);
                        File::ensureDirectoryExists($directory);
                        $path = $directory.'/'.$filename;
                        $writtenPaths[] = $path;
                        $image = Image::make($upload)->orientate();
                        try {
                            $image->resize($limit, $limit, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })->save($path);
                        } finally {
                            $image->destroy();
                        }
                    }
                    $record = new ProductsImage;
                    $record->product_id = $productId;
                    $record->image = $filename;
                    $record->status = 1;
                    $record->save();
                }
            });
        } catch (Throwable $error) {
            foreach ($writtenPaths as $path) {
                if (is_file($path)) {
                    File::delete($path);
                }
            }
            throw $error;
        }
    }
}
