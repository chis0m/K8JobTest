<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductExport implements FromCollection
{
    protected $size;

    public function __construct($size = null)
    {
        $this->size = $size;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        if ($this->size) {
            return Product::query()->take($this->size)->get();
        } else {
            return Product::all();
        }
    }
}
