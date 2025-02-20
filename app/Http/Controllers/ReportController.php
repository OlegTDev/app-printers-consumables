<?php

namespace App\Http\Controllers;

use App\Exports\ConsumableCountExport;
use App\Exports\PrintersWorkplaceExport;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Отчеты
 */
class ReportController extends Controller
{    
    
    /**
     * @return \Inertia\Response
     */
    public function index()
    {        
        return Inertia::render('Reports/Index', [
            'organizations' => Auth::user()->availableOrganizations(),
            'organizationLabels' => Organization::labels(),
        ]);
    }

    /**
     * Формирование отчета по принтерам на местах
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportPrintersWorkplace(Request $request)
    {
        // валидация
        $validator = Validator::make(
            data: $request->all(), 
            rules: [
                'selectedOrganizations' => 'required|array',            
            ], 
            messages: [
                'required' => 'Поле ":attribute" является обязательным для заполнения.',            
            ],
            customAttributes: [
                'selectedOrganizations' => 'Список организаций',            
            ],
        );                
        $validator->validate();
        $organizations = $request->post('selectedOrganizations');        

        // формирование отчета
        return Excel::download(
            new PrintersWorkplaceExport($organizations), 
            'printers-workplace.xlsx');
    }

    /**
     * Формирование отчета по остаткам картриджей
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportConsumableCount(Request $request)
    {
        $validator = Validator::make(
            data: $request->all(), 
            rules: [
                'selectedOrganizations' => 'required|array',           
            ], 
            messages: [
                'required' => 'Поле ":attribute" является обязательным для заполнения.',          
            ],
            customAttributes: [
                'selectedOrganizations' => 'Список организаций',            
            ]
        );                
        $validator->validate();
        $organizations = $request->post('selectedOrganizations');        

        // формирование отчета
        return Excel::download(
            new ConsumableCountExport($organizations), 
            'consumable-count.xlsx');
    }
    

}
