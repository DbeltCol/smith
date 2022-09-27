<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductImport implements ToModel
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        $product = Product::create([
            'name' => $row[1]
        ]);

        return $product;
    }
}
