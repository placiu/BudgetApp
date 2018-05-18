$(function(){

    var i = 1;
    var saveBudget = $('#saveBudget');
    saveBudget.attr('disabled', 'disabled');
    createFormRow(i);

    function createFormRow(i) {
        var form = $('<form>', {'method': 'post', 'id': 'form-' + i});
        var form_row = $('<div>', {'id': 'row-'+ i, 'class': 'd-md-flex pd-y-20 pd-md-y-0 mg-b-20'});
        var form_name_input = $('<input>', {'id': 'name-' + i, 'type': 'text', 'name': 'budget-'+ i, 'class': 'form-control', 'placeholder': 'Budget'});
        var form_value_input = $('<input>', {'id': 'value-' + i, 'type': 'number', 'name': 'value-'+ i, 'class': 'form-control mg-md-l-10 mg-t-10 mg-md-t-0', 'placeholder': 'Value'});
        var form_add_input = $('<input>', {'value': 'Add', 'id': 'add-'+ i, 'type': 'submit', 'class': 'btn btn-info pd-y-13 pd-x-20 bd-0 mg-md-l-10 mg-t-10 mg-md-t-0'});
        var form_edit_input = $('<input>', {'value': 'Edit', 'id': 'edit-'+ i, 'type': 'submit', 'class': 'btn btn-warning pd-y-13 pd-x-20 bd-0 mg-md-l-10 mg-t-10 mg-md-t-0'});
        var form_delete_input = $('<input>', {'value': 'Delete', 'id': 'delete-'+ i, 'type': 'submit', 'class': 'btn btn-danger pd-y-13 pd-x-20 bd-0 mg-md-l-10 mg-t-10 mg-md-t-0'});

        form_name_input.appendTo(form_row);
        form_value_input.appendTo(form_row);
        form_add_input.appendTo(form_row);
        form.append(form_row);
        $('#budget_form_content').append(form);

        $('#row-' + i).on('click', '#add-' + i, function(event) {
            event.preventDefault();
            var budgetName = $(this).prev().prev();
            var budgetValue = $(this).prev();
            if (budgetName.val() !== '' && budgetValue.val() !== '') {
                var name = $('#name-' + i).val();
                var value = $('#value-' + i).val();

                form_name_input.attr('readonly', 'readonly');
                form_value_input.attr('readonly', 'readonly');
                form_add_input.remove();
                form_edit_input.appendTo(form_row);
                form_delete_input.appendTo(form_row);

                budgetName.css('border-color', '');
                budgetValue.css('border-color', '');
                saveBudget.removeAttr('disabled');
                i = i + 1;
                createFormRow(i);
            } else {
                budgetName.css('border-color', 'red');
                budgetValue.css('border-color', 'red');
            }
        });

        saveBudget.on('click', function() {
            for (var x = 1; x < i; x++) {
                //console.log($('#name-' + i));
                var name = $('#name-' + i);
                var value = $('#value-' + i);
                console.log(name);
                console.log(value);
                //budgets.push([name, value]);
            }
            //console.log(budgets);
        });

    }
});