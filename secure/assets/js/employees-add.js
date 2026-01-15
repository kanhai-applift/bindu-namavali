$(function () {

  $('#employeeForm').on('submit', function (e) {
    e.preventDefault();

    let formData = new FormData(this);

    $.ajax({
      url: baseUrl('api/employees-save'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',

      success: function (res) {
        if (res.status === 'success') {
          $('#alertBox').html(
            `<div class="alert alert-success">${res.message}</div>`
          );
          $('#employeeForm')[0].reset();
          window.location.reload();
        } else {
          $('#alertBox').html(
            `<div class="alert alert-danger">${res.message}</div>`
          );
        }
      },

      error: function () {
        $('#alertBox').html(
          `<div class="alert alert-danger">Unable to process request.</div>`
        );
      }
    });

  });

});