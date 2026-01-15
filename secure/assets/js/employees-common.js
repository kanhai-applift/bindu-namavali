$(function () {
  // Configure datepicker for DD/MM/YYYY format with year range 1950-2090
  $.datepicker.setDefaults({
    dateFormat: 'dd/mm/yy',
    changeMonth: true,
    changeYear: true,
    yearRange: '1950:2090',
    showButtonPanel: true
  });

  let retirementDateManuallyEdited = false;


  // Calculate retirement date on page load if birth date exists
  if ($('#janma_tarik').val() && isValidDate($('#janma_tarik').val())) {
    // Check if retirement date matches auto-calculation
    const birthDate = $('#janma_tarik').val();
    const calculatedDate = calculateRetirementDateFromBirthDate(birthDate);
    const currentRetirementDate = $('#sevaniroti_dinank').val();

    if (calculatedDate !== currentRetirementDate) {
      setRetirementDateManuallyEdited(true);
    } else {
      setRetirementDateManuallyEdited(false);
    }
  }


  // Helper function to calculate retirement date without setting the field
  function calculateRetirementDateFromBirthDate(birthDate) {
    if (!isValidDate(birthDate)) return '';

    var parts = birthDate.split("/");
    var day = parseInt(parts[0], 10);
    var month = parseInt(parts[1], 10);
    var year = parseInt(parts[2], 10);

    // Add 58 years to birth date
    var retirementYear = year + 58;

    // Calculate last day of the retirement month
    var lastDayOfMonth = new Date(retirementYear, month, 0).getDate();
    return lastDayOfMonth.toString().padStart(2, '0') + '/' +
      month.toString().padStart(2, '0') + '/' +
      retirementYear;
  }

  // Function to set manual edit status
  function setRetirementDateManuallyEdited(edited) {
    retirementDateManuallyEdited = edited;
    const retirementField = $('#sevaniroti_dinank');
    const infoDiv = $('#retirementInfo');

    if (edited) {
      retirementField.removeClass('auto-calculated').addClass('manually-edited');
      infoDiv.removeClass('auto-calculation-info').addClass('manual-edit-info')
        .text('Manually edited retirement date');
    } else {
      retirementField.removeClass('manually-edited').addClass('auto-calculated');
      infoDiv.removeClass('manual-edit-info').addClass('auto-calculation-info')
        .text('Auto-calculated: Birth Date + 58 years (Month end date)');
    }
  }

  // Function to check if retirement date was manually edited
  function isRetirementDateManuallyEdited() {
    return retirementDateManuallyEdited;
  }


  // Function to calculate retirement date (birth date + 58 years to month end)
  function calculateRetirementDate() {
    console.log('Trigger retirement date calculation');
    var birthDate = $('#janma_tarik').val();

    if (isValidDate(birthDate) && !isRetirementDateManuallyEdited()) {
      var parts = birthDate.split("/");
      var day = parseInt(parts[0], 10);
      var month = parseInt(parts[1], 10);
      var year = parseInt(parts[2], 10);

      // Add 58 years to birth date
      var retirementYear = year + 58;

      // Calculate last day of the retirement month
      var lastDayOfMonth = new Date(retirementYear, month, 0).getDate();
      var retirementDate = lastDayOfMonth.toString().padStart(2, '0') + '/' +
        month.toString().padStart(2, '0') + '/' +
        retirementYear;

      if (isValidDate(retirementDate)) {
        $('#sevaniroti_dinank').val(retirementDate);
        setRetirementDateManuallyEdited(false);
      }
    }
  }

  // Function to validate DD/MM/YYYY date
  function isValidDate(dateString) {
    if (!dateString) return false;

    var parts = dateString.split("/");
    if (parts.length !== 3) return false;

    var day = parseInt(parts[0], 10);
    var month = parseInt(parts[1], 10);
    var year = parseInt(parts[2], 10);

    if (isNaN(day) || isNaN(month) || isNaN(year)) return false;
    if (month < 1 || month > 12) return false;
    if (day < 1 || day > 31) return false;

    // Check for valid days in month
    var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    // Adjust for leap years
    if (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)) {
      monthLength[1] = 29;
    }

    return day <= monthLength[month - 1];
  }

  // Initialize datepicker for ALL date fields including retirement date
  $('#pad_niyukt_dinank, #janma_tarik, #sevaniroti_dinank').datepicker({
    onSelect: function (dateText, inst) {
      // Format validation
      if (isValidDate(dateText)) {
        $(this).val(dateText);

        // If birth date is changed, update retirement date (unless manually edited)
        if (this.id === 'janma_tarik' && !isRetirementDateManuallyEdited()) {
          calculateRetirementDate();
        }

        // If retirement date is manually edited, update the styling
        if (this.id === 'sevaniroti_dinank') {
          setRetirementDateManuallyEdited(true);
        }
      }
      else {

        console.log('Trigger retirement date calculation 1');
      }
    }
  });
});