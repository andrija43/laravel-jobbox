'use strict'
$(document).ready(function () {
    $('#company_id').on('change', function () {
        $.ajax({
            type: 'GET',
            cache: false,
            url: route('ajax.company.show', $(this).val()),
            success: (res) => {
                if (!res.error) {
                    if (res.data) {
                        $('#address').val(res.data.address)
                        $('#city_id')
                            .append('<option value="' + res.data.city.id + '">' + res.data.city.name + '</option>')
                            .val(res.data.city.id)
                            .trigger('change')
                        $('#state_id')
                            .append('<option value="' + res.data.state.id + '">' + res.data.state.name + '</option>')
                            .val(res.data.state.id)
                            .trigger('change')
                    }
                } else {
                    Botble.showError(res.message)
                }
            },
            error: (res) => {
                Botble.handleError(res)
            },
        })
    })

    $(document).on('click', '#btn-add-company', (event) => {
        event.preventDefault()
        let _self = $(event.currentTarget)
        _self.addClass('button-loading')

        $.ajax({
            type: 'POST',
            cache: false,
            url: route('ajax.company.create'),
            data: {
                name: $('#company_name').val(),
            },
            success: (res) => {
                if (!res.error) {
                    $('#company_id')
                        .append('<option value="' + res.data.id + '">' + res.data.name + '</option>')
                        .val(res.data.id)
                        .trigger('change')
                    $('#add-company-modal').modal('hide')
                    $('#company_name').val('')
                } else {
                    Botble.showError(res.message)
                }
                _self.removeClass('button-loading')
            },
            error: (res) => {
                Botble.handleError(res)
                _self.removeClass('button-loading')
            },
        })
    })

    $(document).on('change', '#never_expired', (event) => {
        if ($(event.currentTarget).is(':checked') === true) {
            $('#auto_renew').closest('.form-group').addClass('hidden').fadeOut()
        } else {
            $('#auto_renew').closest('.form-group').removeClass('hidden').fadeIn()
        }
    })
})
