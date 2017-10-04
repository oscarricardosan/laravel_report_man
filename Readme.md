Laravel package.

Register Report_manServiceProvider in providers (config/app.php).

To use de default component to show the tables:

view('report_man::components/dashboard')

https://laravel.com/docs/master/packages#views

Need: Jquery, bootstrap and underscore
Exec this script:


$.fn.renderTpl= function(data) {
    var tpl= '';
    this.each(function(index, element) {
        var template= _.template($(this).html());
        tpl+= template(data);
    });
    return tpl;
};


To export Excel needs
http://www.maatwebsite.nl/laravel-excel/docs/getting-started#installation

To export PDF needs dompdf/dompdf

"maatwebsite/excel": "~2.1.0",
"dompdf/dompdf": "~0.6.1"

