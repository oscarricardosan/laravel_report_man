<?php

namespace Oscarricardosan\Report_man;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Oscarricardosan\Report_man\Maps\ParamsMap;

class Report_manController extends Controller
{
    /**
     * @var Report_man
     */
    protected $report_man;

    public function generate_xls(Request $request)
    {
        $this->validate($request, ['fields'=> 'array|required', 'filters'=> 'array|sometimes|required']);
        return $this->generate($request, 'Reporte de casos', 'xlsx');
    }

    public function generate_xlsx(Request $request)
    {
        $this->validate($request, ['fields'=> 'array|required', 'filters'=> 'array|sometimes|required']);
        return $this->generate($request, 'Reporte de casos', 'xls');
    }

    public function generate_csv(Request $request)
    {
        $this->validate($request, ['fields'=> 'array|required', 'filters'=> 'array|sometimes|required']);
        return $this->generate($request, 'Reporte de casos', 'csv');
    }

    protected function generate(Request $request, $file_name, $file_extension)
    {
        return response()->download(
            $this->report_man
                ->build_query(new ParamsMap($request->all()))
                ->execQuery()
                ->download($file_name, $file_extension)
        );
    }

}