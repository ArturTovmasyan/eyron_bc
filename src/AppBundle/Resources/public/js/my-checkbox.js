$(document).ready(function(){

    $('input[type=checkbox]:not(".onoffswitch-checkbox")').iCheck({
        checkboxClass: 'icheckbox_square-purple',
        increaseArea: '20%'
    }).on('ifChanged', function (event) {
        $(event.target).trigger('change');
    });
});
