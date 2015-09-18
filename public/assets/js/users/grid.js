$(function () {
    var lastSel = -1,
        jqGrid = $('#jgGrid'),
        groupSelected = {},
        afterSave = function (response) {
            if (response.responseJSON != undefined) {
                groupSelected[response.responseJSON.id] = response.responseJSON.groups_id;
                return [true];
            }
            return [false];
        };

    jqGrid.jqGridHistory({
        gridview: true,
        datatype: 'json',
        mtype: 'POST',
        url: '/users/grid-data',
        rowNum: 10,
        rowList: [5, 10, 20, 50, 100, 200],
        sortname: 'id',
        height: false,
        autowidth: true,
        autoheight: true,
        model: centerInfoDialog,
        localreader: 'id',
        colModel: [
            {
                searchoptions: {
                    sopt: numopts
                },
                searchrules: {
                    integer: true
                },
                label: 'ID',
                name: 'id',
                width: '30',
                sorttype: 'int',
                key: true
            },
            {
                searchoptions: {
                    sopt: stropts
                },
                label: 'Name',
                name: 'name',
                editable: true,
                editrules: {required: true, minValue: 5}
            },
            {
                searchoptions: {
                    sopt: stropts
                },
                label: 'Username',
                name: 'username',
                editable: true,
                editrules: {required: true, minValue: 5}
            },
            {
                searchoptions: {
                    sopt: stropts
                },
                label: 'Email',
                name: 'email',
                editable: true,
                editrules: {required: true, minValue: 5, email: true},
                formatter: 'email'
            },
            {
                searchoptions: {
                    sopt: boolopts,
                    value: ":All;1:Active;0:Inactive"
                },
                label: 'Active',
                name: 'active',
                editable: true,
                formatter: 'select',
                edittype: 'select',
                editoptions: {value: "false:No;true:Yes"},
                stype: "select"
            },
            {
                searchoptions: {
                    sopt: dateopts,
                    dataInit: function (element) {
                        $(element).datepicker({
                            dateFormat: 'yy-mm-dd',
                            onSelect: function (dateText, inst) {
                                jqGrid[0].triggerToolbar();
                            }
                        });
                    }
                },
                label: 'Last Activity',
                name: 'last_activity',
                editable: true,
                editrules: {required: false, date: true},
                formatter: 'date',
                formatoptions: {newformat: 'Y-m-d'},
                editoptions: {
                    dataInit: function (element) {
                        return $(element).datepicker({
                            dateFormat: 'yy-mm-dd'
                        });
                    }
                }
            },
            {
                searchoptions: {
                    sopt: numopts,
                    value: ":All",
                    dataInit: function (element) {
                        var options = '<option value="" >All</option>';
                        $.each(groups, function (i, group) {
                            options += '<option value="' + group.id + '" >' + group.name + '</option>';
                        });
                        $(element).empty().append(options);
                    }
                },
                label: 'Group',
                name: 'groups_id',
                sortable: true,
                editable: true,
                edittype: 'select',
                stype: 'select',
                editrules: {integer: true},
                formatter: function (cellValue, options) {
                    if (!groupSelected[options.rowId] && cellValue) {
                        groupSelected[options.rowId] = cellValue;
                    }
                    var value = '<div class="tags" >';
                    $.each(groups, function (i, group) {
                        if (group.id == groupSelected[options.rowId]) {
                            value += '<span class="tag">' + $.jgrid.htmlEncode(group.name) + '</span> ';
                        }
                    });

                    value += '</div>';
                    return value;
                },
                editoptions: {
                    dataInit: function (element, options) {
                        var options = '';
                        $.each(groups, function (i, group) {
                            var selected = '';
                            if (group.id == groupSelected[jqGrid.jqGrid('getGridParam', 'selrow')]) {
                                selected = 'selected';
                            }
                            options += '<option value="' + group.id + '" ' + selected + '>' + group.name + '</option>';
                        });
                        $(element).empty().append(options);
                        $(element).attr({
                            id: 'groups_id',
                            name: 'groups_id'
                        });
                    }
                }
            },
            {
                label: 'Password',
                name: 'password',
                editable: true,
                hidden: true,
                viewable: false,
                editrules: {edithidden: true, required: false, minValue: 5},
                "formatter": function () {
                    return '';
                },
                "edittype": "password"
            }
        ],
        pager: 'jgGridPager',
        viewrecords: true,
        cellEdit: false,
        editurl: '/users/grid-data',
        multiselect: true,
        onSelectRow: function (id) {
            if (id && id !== lastSel) {
                jqGrid.jqGrid('restoreRow', lastSel);
                lastSel = id;
            }
        },
        ondblClickRow: function (id) {
            jqGrid.jqGrid('resetSelection');
            jqGrid.setSelection(id, true);
            jqGrid.jqGrid('editRow', id, {
                keys: true,
                errorfunc: function (id, response) {
                    var error = processResponseErrors(response);
                    $.jgrid.info_dialog($.jgrid.errors.errcap,
                        '<div class="ui-state-error">' + error + '</div>',
                        $.jgrid.edit.bClose,
                        {buttonalign: 'right'}
                    );
                    centerInfoDialog();
                },
                successfunc: afterSave
            });
        }
    });
    jqGrid.jqGrid('navGrid', '#jgGridPager',
        {
            add: true,
            addtext: 'Add',
            edit: true,
            edittext: 'Edit',
            del: true,
            deltext: 'Delete',
            search: true,
            searchtext: 'Search',
            view: true,
            viewtext: 'View',
            refresh: true,
            refreshtext: 'Reload',
            alertleft: Math.round(($('#content').width()) / 2) - 100,
            alerttop: 230

        },
        {
            /*edit options*/
            errorTextFormat: processResponseErrors,
            afterSubmit: afterSave,
            afterShowForm: centerInfoDialog,
            reloadAfterSubmit: false,
            recreateForm: true,
            viewPagerButtons: false
        },
        {
            /*add options*/
            errorTextFormat: processResponseErrors,
            afterSubmit: afterSave,
            afterShowForm: centerInfoDialog,
            recreateForm: true
        },
        {
            /*delete options*/
            errorTextFormat: processResponseErrors,
            afterShowForm: centerInfoDialog
        },
        {
            /*search options*/
            multipleSearch: true,
            errorTextFormat: processResponseErrors,
            afterShowSearch: centerInfoDialog
        },
        {
            /*view options*/
            closeOnEscape: true,
            beforeShowForm: function () {
                setTimeout(centerInfoDialog, 1);
            }
        }
    );
    jqGrid.jqGrid('filterToolbar', {
        stringResult: true,
        searchOnEnter: false,
        clearSearch: true
    }).jqGrid('navButtonAdd', '#jgGridPager', {
        caption: 'Filters',
        tile: 'Filters',
        buttonicon: 'ui-icon-pin-s',
        onClickButton: function () {
            jqGrid[0].toggleToolbar();
        }
    }).jqGrid('navButtonAdd', '#jgGridPager', {
        id: 'pager_excel',
        caption: 'xls',
        title: 'Export To Excel',
        onClickButton: function (e) {
            try {
                jqGrid.jqGrid('excelExport', {tag: 'xls', url: '/users/export-data'});
            } catch (e) {
                window.location = '/users/export-data?oper=xls';
            }
        },
        buttonicon: 'ui-icon-newwin'
    }).jqGrid('navButtonAdd', '#jgGridPager', {
        id: 'pager_csv',
        caption: 'csv',
        title: 'Export To CSV',
        onClickButton: function (e) {
            try {
                jqGrid.jqGrid('excelExport', {tag: 'csv', url: '/users/export-data'});
            } catch (e) {
                window.location = '/users/export-data?oper=csv';
            }
        },
        buttonicon: 'ui-icon-arrowthickstop-1-s'
    });
    $(window).bind('resize', function () {
        jqGrid.setGridWidth($('#content').width(), true);
    }).trigger('resize');
});