<?php

namespace App\Http\Controllers;

use App\Models\Bom;

class BomController extends Controller
{
    public function items(Bom $bom)
    {
        return view('boms.items', compact('bom'));
    }
}
