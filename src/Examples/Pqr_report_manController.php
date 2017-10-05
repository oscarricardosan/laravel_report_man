<?php

namespace App\Http\Controllers\Report_mans;


use App\Report_mans\Pqr_report_man;
use Illuminate\Http\Request;
use Oscarricardosan\Report_man\Report_manController;

class Pqr_report_manController extends Report_manController
{

    public function __construct(Pqr_report_man $pqr_report_man)
    {
        $this->report_man = $pqr_report_man;
    }

    public function index()
    {
        return view('reports_man.pqrs', [
            'report'=> $this->report_man
        ]);
    }
}