<?php

namespace App\Http\Controllers\Dictionary;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dictionary\SparePartsRequest;
use App\Models\SpareParts;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;


class SparePartsController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:admin,editor-dictionary')
            ->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        return Inertia::render('Dictionary/SpareParts/Index', [           
            'items' => $this->transform(SpareParts::filter(Request::only(['search']))->get()),
            'filters' => Request::all(['search']),
            'labels' => SpareParts::labels(),
        ]);
    }

    private function transform(Collection $model)
    {
        return $model->transform(function (SpareParts $sparePart) {
            return [
                'id' => $sparePart->id,
                'name' => $sparePart->name,
                'description' => $sparePart->description,
                'author' => [
                    'name' => $sparePart->author->name,
                    'fio' => $sparePart->author->fio,
                    'company' => $sparePart->author->company,
                    'department' => $sparePart->author->department,
                    'post' => $sparePart->author->post,
                ],
                'created_at' => $sparePart->created_at,
                'updated_at' => $sparePart->updated_at,
            ];
        });
    }    


    public function create()
    {
        return Inertia::render('Dictionary/SpareParts/Create', [
            'labels' => SpareParts::labels(),
        ]);
    }


    public function store(SparePartsRequest $request)
    {
        $model = SpareParts::create($request->only(['name', 'description']));
        if (!$model) {
            return redirect()->back();
        }
        return redirect()->route('dictionary.spare-parts.index')
            ->with('success', 'Запись успешно добавлена!');
    }


    public function edit(SpareParts $sparePart)
    {
        return Inertia::render('Dictionary/SpareParts/Edit', [
            'labels' => SpareParts::labels(),
            'sparePart' => $sparePart,
        ]);
    }

    public function update(SparePartsRequest $request, SpareParts $sparePart)
    {
        $model = $sparePart->update($request->only(['name', 'description']));
        if (!$model) {
            return redirect()->back();
        }
        return redirect()->route('dictionary.spare-parts.index')
            ->with('success', 'Запись успешно обновлена!');
    }

    
    public function destroy(SpareParts $sparePart)
    {
        $sparePart->delete();
        return redirect()->route('dictionary.spare-parts.index')
            ->with('success', 'Запись успешно удалена!');
    }
}
