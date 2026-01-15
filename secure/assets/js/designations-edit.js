$(function () {
    $('#designationEditForm').on('submit', function (e) {
      e.preventDefault();
      let form = this;
      $(form).removeClass('was-validated');

      $.ajax({
        url: baseUrl('api/designations-update'),
        type: 'POST',
        data: $(form).serialize(),
        dataType: 'json',
        success: function (res) {
          if (res.status === 'success') {
            $('#alertBox').html(
              `<div class="alert alert-success">${res . message}</div>`
            );
            // setTimeout(function () {
            //   window.location.href = '<?= baseUrl('designations') ?>';
            // }, 1200);
          } else {
            $('#alertBox').html(
              `<div class="alert alert-danger">${res . message}</div>`
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