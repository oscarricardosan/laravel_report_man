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

    protected function get_file_title()
    {
        return "Reporte";
    }

    public function generate_xls(Request $request)
    {
        $this->validate($request, ['fields'=> 'array|required', 'filters'=> 'array|sometimes|required']);
        return $this->generate($request, $this->get_file_title(), 'xlsx');
    }

    public function generate_xlsx(Request $request)
    {
        $this->validate($request, ['fields'=> 'array|required', 'filters'=> 'array|sometimes|required']);
        return $this->generate($request, $this->get_file_title(), 'xls');
    }

    public function generate_csv(Request $request)
    {
        $this->validate($request, ['fields'=> 'array|required', 'filters'=> 'array|sometimes|required']);
        return $this->generate($request, $this->get_file_title(), 'csv');
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