/* Base js */

//jqgrid search options 'sopt'
var numopts = ['eq', 'ne', 'lt', 'le', 'gt', 'ge', 'nu', 'nn', 'in', 'ni'],
    stropts = ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni'],
    boolopts = ['eq', 'ne'],
    dateopts = ['gt', 'ge', 'lt', 'le', 'nu', 'nn', 'eq', 'ne'];

/*
 *  process JSON from response and set Errors message
 *
 *  @return string
 */
var processResponseErrors = function (response) {
    if (response.responseJSON == undefined || ( response.responseJSON.errors == undefined && response.responseJSON.error == undefined))
        return 'Could not save records due to an internal server error ';
    //Validation Errors
    if ($.isArray(response.responseJSON.errors))
        return response.responseJSON.errors.join('<br />');
    //Exception
    if (typeof response.responseJSON.error.message !== undefined)
        return 'Exception: ' + response.responseJSON.error.message;
};

/*
 *  Encode HTML chars
 *
 *  @return string
 */
var htmlEncode = function (value) {
    return $('<div/>').text(value).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
};

/*
 *  Decode HTML chars
 *
 *  @return string
 */
var htmlDecode = function (value) {
    return $('<div/>').html(value).text();
};

/*
 *  enter jqGrid dialog
 *
 *  @return void
 */
var centerInfoDialog = function () {
    var infoDlg = $('.ui-jqdialog:visible');
    if (infoDlg.length) {
        infoDlg.css({
            'min-width': '400px',
            left: Math.round(($('#content').width() - infoDlg.width()) / 2) + "px"
        });
        $('.FormGrid', infoDlg).css({
            'padding': '15px'
        });

    }
};


$(function () {

});

