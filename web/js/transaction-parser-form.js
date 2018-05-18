$(function(){
    'use strict';
    var i = 0;
    var form = $('#form-layout');

    var descriptions = [];

    for(var x = 0; x < amounts.length; x++) {
        var dateCheck = dates[x];
        dateCheck = dateCheck.replace("-0", "-");
        dateCheck = dateCheck.substr(0, 6);
        var workingDate = year+'-'+month;

        if(dateCheck == workingDate) {
            createFormRow(x);
        }

    }

    function createFormRow(i) {

        var form_layout = $('<div>', {'id': 'form-layout', 'class': 'form-layout form-layout-2'});
        var input_id_hidden = $('<input>', {'type': 'hidden', 'name': 'id['+i+']', 'value': i});
        var row = $('<div>', {'class': 'row no-gutters mg-b-20'});

        input_id_hidden.appendTo(form_layout);
        form_layout.appendTo(form);
        row.appendTo(form_layout);

        var cel1 = $('<div>', {'class': 'col-md-4'});
        var cel1_divFormGroup = $('<div>', {'class': 'form-group'});
        var cel1_label = $('<label>', {'class': 'form-control-label'});
        var cel1_input = $('<input>', {'class': 'form-control', 'type': 'text', 'name': 'date['+i+']', 'value': dates[i], 'placeholder': 'RRRR-MM-DD', 'required': 'required'});

        cel1_divFormGroup.appendTo(cel1);
        cel1_label.appendTo(cel1_divFormGroup);
        cel1_label.html('Date');
        cel1_input.appendTo(cel1_divFormGroup);
        cel1.appendTo(row);

        var cel2 = $('<div>', {'class': 'col-md-4'});
        var cel2_divFormGroup = $('<div>', {'class': 'form-group mg-md-l--1'});
        var cel2_label = $('<label>', {'class': 'form-control-label'});
        var cel2_input = $('<input>', {'class': 'form-control', 'type': 'text', 'name': 'type['+i+']', 'value': types[i], 'placeholder': 'Enter type', 'required': 'required'});

        cel2_divFormGroup.appendTo(cel2);
        cel2_label.appendTo(cel2_divFormGroup);
        cel2_label.html('Type');
        cel2_input.appendTo(cel2_divFormGroup);
        cel2.appendTo(row);

        var cel3 = $('<div>', {'class': 'col-md-4'});
        var cel3_divFormGroup = $('<div>', {'class': 'form-group mg-md-l--1 bg-gray-200'});
        var cel3_label = $('<label>', {'class': 'form-control-label'});
        var cel3_input = $('<input>', {'class': 'form-control', 'type': 'text', 'name': 'amount['+i+']', 'value': amounts[i], 'placeholder': 'Enter amount', 'required': 'required'});

        cel3_divFormGroup.appendTo(cel3);
        cel3_label.appendTo(cel3_divFormGroup);
        cel3_label.html('Amount');
        cel3_input.appendTo(cel3_divFormGroup);
        cel3.appendTo(row);

        var cel4 = $('<div>', {'class': 'col-md-4'});
        var cel4_divFormGroup = $('<div>', {'class': 'form-group bd-t-0-force'});
        var cel4_label = $('<label>', {'class': 'form-control-label'});
        var cel4_textarea = $('<textarea>', {'class': 'form-control', 'rows': 2, 'name': 'recipient['+i+']', 'placeholder': 'Enter recipient', 'required': 'required'});

        cel4_divFormGroup.appendTo(cel4);
        cel4_label.appendTo(cel4_divFormGroup);
        cel4_label.html('Recipient');
        cel4_textarea.appendTo(cel4_divFormGroup);
        cel4_textarea.val(recipients[i]);
        cel4.appendTo(row);

        var cel5 = $('<div>', {'class': 'col-md-4'});
        var cel5_divFormGroup = $('<div>', {'class': 'form-group mg-md-l--1 bd-t-0-force'});
        var cel5_label = $('<label>', {'class': 'form-control-label'});
        var cel5_textarea = $('<textarea>', {'class': 'form-control', 'rows': 2, 'name': 'description['+i+']', 'placeholder': 'Enter description'});

        cel5_divFormGroup.appendTo(cel5);
        cel5_label.appendTo(cel5_divFormGroup);
        cel5_label.html('Description');
        cel5_textarea.appendTo(cel5_divFormGroup);
        cel5.appendTo(row);

        var cel6 = $('<div>', {'class': 'col-md-4'});
        var cel6_divFormGroup = $('<div>', {'class': 'form-group mg-md-l--1 bd-t-0-force'});
        var cel6_label = $('<label>', {'class': 'form-control-label'});
        var cel6_select = $('<select>', {'class': 'form-control select2', 'name': 'budget['+i+']', 'data-placeholder': 'Choose budget', 'required': 'required'});
        var cel6_select_label = $('<option>', {'label': 'Choose budget'});

        cel6_divFormGroup.appendTo(cel6);
        cel6_label.appendTo(cel6_divFormGroup);
        cel6_label.html('Budget');
        cel6_select_label.appendTo(cel6_select);
        cel6_select.appendTo(cel6_divFormGroup);
        cel6.appendTo(row);

        for (var y = 0; y < budgets.length; y++) {
            var cel6_select_option = $('<option>', {'value': budgets[y]});
            cel6_select_option.appendTo(cel6_select);
            cel6_select_option.html(budgets[y]);
        }

        if (amounts[i] > 0) {
            cel4_label.html('Sender');
            cel6_label.html('No budget for income transaction');
            cel6_select.remove();
            var cel6_input = $('<input>', {'type': 'hidden', 'name': 'budget['+i+']', 'value': 'none'});
            cel6_input.appendTo(cel6_divFormGroup);
        }

    }



});