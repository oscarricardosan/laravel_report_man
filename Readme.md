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



