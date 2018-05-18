$(function(){

    var divForm = $('#form');
    var i = 1;

    createFormRow(i);

    function createFormRow(i) {

        var form = $('<form>', {'id': 'form-' + i});
        var divInputs = $('<div>', {'id': 'inputs-' + i, 'class': 'form-layout form-layout-7 mg-b-20'});

        form.appendTo(divForm);
        divInputs.appendTo(form);

        var divRow1 = $('<div>', {'class': 'row no-gutters'});
        var divRow2 = $('<div>', {'class': 'row no-gutters'});
        var divCol4_1 = $('<div>', {'id': 'col4_1-' + i, 'class': 'col-4'});
        var divCol4_2 = $('<div>', {'id': 'col4_2-' + i, 'class': 'col-4'});
        var divCol8_1 = $('<div>', {'id': 'col8_1-' + i, 'class': 'col-8'});
        var divCol8_2 = $('<div>', {'id': 'col8_2-' + i, 'class': 'col-8'});
        var inputName = $('<input>', {'id': 'name-' + i, 'name': 'name-' + i, 'type': 'text', 'class': 'form-control', 'placeholder': 'Enter budget name'});
        var inputValue = $('<input>', {'id': 'value-' + i, 'name': 'value-' + i, 'type': 'number', 'class': 'form-control', 'placeholder': 'Enter budget value'});

        divRow1.appendTo(divInputs);
            divCol4_1.appendTo(divRow1);
                divCol4_1.text('Budget Name');
            divCol8_1.appendTo(divRow1);
                inputName.appendTo(divCol8_1);

        divRow2.appendTo(divInputs);
            divCol4_2.appendTo(divRow2);
                divCol4_2.text('Budget Value');
            divCol8_2.appendTo(divRow2);
                inputValue.appendTo(divCol8_2);

        var divSubmit = $('<div>', {'id': 'submit-' + i, 'class': 'row'});
        var divCol10 = $('<div>', {'class': 'col-10'});
        var divCol2 = $('<div>', {'class': 'col-2'});
        var inputSubmit = $('<input>', {'id': 'submit-' + i, 'name': 'submit-' + i, 'value': 'Create', 'type': 'submit', 'class': 'btn btn-primary btn-block bd-0'});

        divSubmit.appendTo(form);
        divCol10.appendTo(divSubmit);
        divCol2.appendTo(divSubmit);
        inputSubmit.appendTo(divCol2);

        inputSubmit.click(function(event) {
            event.preventDefault();
            var name = $('#name-' + i);
            var value = $('#value-' + i);
            var budgetName = name.val();
            var budgetValue = value.val();
            var col8_1 = $('#col8_1-' + i);
            var col8_2 = $('#col8_2-' + i);

            if (budgetName !== '' && budgetValue !== '') {
                $('#submit-' + i).remove();
                name.attr('readonly', 'readonly');
                value.attr('readonly', 'readonly');
                col8_1.addClass('bg-gray-200');
                col8_2.addClass('bg-gray-200');
                col8_1.css('border-color', '');
                col8_2.css('border-color', '');

                $('#done').removeAttr('disabled');

                $.ajax({
                    url: '/api/budget',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        "name": budgetName,
                        "value": budgetValue,
                        "year": year,
                        "month": month
                    }
                }).done(function (response) {
                    console.log(response);
                });

                i = i + 1;
                createFormRow(i);
            } else {
                $('#col8_1-' + i).css('border-color', 'red');
                $('#col8_2-' + i).css('border-color', 'red');
            }

        });

    }

    $('#done').click(function() {
        location.reload();
    });

});