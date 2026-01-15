(function () {
    const form = document.getElementById('registrationForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity() || password.value !== confirmPassword.value) {
            event.preventDefault();
            event.stopPropagation();

            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords do not match");
            } else {
                confirmPassword.setCustomValidity("");
            }
        }

        form.classList.add('was-validated');
    });
})();

$('#registrationForm').on('submit', function(e) {
    e.preventDefault();

    // prevent form submission if email already exist
    if ($('#emailHelp').hasClass('text-danger')) {
        $('#msg').html('<div class="alert alert-danger">Please use a different email.</div>');
        return;
    }

    $.ajax({
        url: baseUrl('api/register-save'),
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                $('#msg').html('<div class="alert alert-success">' + res.message + '</div>');
                
                const form = $('#registrationForm')[0];
                form.reset();
                form.classList.remove('was-validated');
                // Optional: remove any custom validity messages
                $(form).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                // Optional: clear help messages
                $('#emailHelp').text('');

            } else {
                $('#msg').html('<div class="alert alert-danger">' + res.message + '</div>');
            }
        },
        error: function() {
            $('#msg').html('<div class="alert alert-danger">Server error. Try again.</div>');
        }
    });
});

let emailTimer = null;

$('#email').on('keyup blur', function () {
    clearTimeout(emailTimer);

    let email = $(this).val().trim();
    let csrf_token = $('#csrf_token').val();
    let emailHelp = $('#emailHelp');

    emailHelp.removeClass().text('');

    if (email === '') return;

    // Basic email format check (client-side)
    if (!/^\S+@\S+\.\S+$/.test(email)) {
        emailHelp.addClass('text-danger').text('Invalid email format');
        return;
    }

    emailTimer = setTimeout(function () {
        $.ajax({
            url: baseUrl('api/email-check'),
            type: 'POST',
            dataType: 'json',
            data: { email: email, csrf_token: csrf_token },
            success: function (res) {
                if (res.status === 'available') {
                    emailHelp.addClass('text-success').text('Email is available');
                } else {
                    emailHelp.addClass('text-danger').text('Email already exists');
                }
            },
            error: function () {
                emailHelp.addClass('text-danger').text('Unable to verify email');
            }
        });
    }, 400); // debounce
});
