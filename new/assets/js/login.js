$('#loginForm').on('submit', function (e) {
    e.preventDefault();

    const form = this;

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $.ajax({
        url: baseUrl('api/login-check'),
        type: 'POST',
        dataType: 'json',
        data: $(form).serialize(),
        beforeSend: function () {
            $('button[type=submit]').prop('disabled', true);
        },
        success: function (res) {
            if (res.status === 'success') {
                window.location.href = res.redirect;
            } else {
                $('#msg').html(
                    `<div class="alert alert-danger">${res.message}</div>`
                );
                $('button[type=submit]').prop('disabled', false);
            }
        },
        error: function () {
            $('#msg').html(
                '<div class="alert alert-danger">Login failed</div>'
            );
            $('button[type=submit]').prop('disabled', false);
        }
    });
});
