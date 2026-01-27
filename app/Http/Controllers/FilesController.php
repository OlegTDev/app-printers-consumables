<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{    
    public function download($path)
    {        
        return Storage::download($path);
    }

    public function preview($path)
    {        
        return response()->file(Storage::path($path));
    }
}
