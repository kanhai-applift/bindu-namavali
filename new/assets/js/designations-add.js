$(function () {

  $('#designationForm').on('submit', function (e) {
    e.preventDefault();

    let form = this;

    // Remove previous validation state
    $(form).removeClass('was-validated');

    $.ajax({
      url: baseUrl('api/designations-save'),
      type: 'POST',
      data: $(form).serialize(),
      dataType: 'json',

      success: function (res) {

        if (res.status === 'success') {

          $('#alertBox').html(
            `<div class="alert alert-success">${res.message}</div>`
          );

          // Reset form WITHOUT triggering validation
          form.reset();
          $(form).removeClass('was-validated');

        } else {

          $('#alertBox').html(
            `<div class="alert alert-danger">${res.message}</div>`
          );

        }
      },

      error: function () {
        $('#alertBox').html(
          `<div class="alert alert-danger">Something went wrong. Please try again.</div>`
        );
      }
    });

  });

});