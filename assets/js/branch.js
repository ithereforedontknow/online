// Handle form submission for add user

function logout_user() {
  $.ajax({
    url: "../../api/auth.php", // Adjust the path if needed
    method: "POST",
    data: { logout: true }, // Ensure the value is a string
    dataType: "json", // Expect a JSON response
    success: function (response) {
      if (response.success) {
        // alert(response.message); // Optional: Show a logout success message
        window.location.href = "../../index.php";
      } else {
        alert("Error: " + response.message); // Handle failure cases
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX error:", status, error); // Log AJAX errors for debugging
      alert("An error occurred while logging out. Please try again.");
    },
  });
}
$("#clear").click(function () {
  $(
    "#to-reference, #guia, #hauler, #plate-number, #driver-name, #helper-name, #no-of-bales, #kilos"
  ).val("");
});
$("#edit-profile-form").submit((event) => {
  event.preventDefault(); // Prevent the default form submission

  // Reset validation feedback
  const form = event.target;
  Array.from(form.elements).forEach((input) => {
    input.classList.remove("is-invalid");
    input.classList.remove("is-valid");
  });

  const newPassword = $("#edit-newPassword").val();
  const confirmPassword = $("#edit-confirmPassword").val();

  // Check if new password and confirm password match
  if (newPassword !== confirmPassword) {
    $("#edit-confirmPassword").addClass("is-invalid");
    $("#edit-newPassword")
      .addClass("is-invalid")
      .next(".invalid-feedback")
      .text("Passwords do not match.")
      .show();
    $("#edit-confirmPassword")
      .next(".invalid-feedback")
      .text("Passwords do not match.")
      .show();
    return; // Exit the function if passwords do not match
  } else if (newPassword === "" || confirmPassword === "") {
    $("#edit-confirmPassword").addClass("is-invalid");
    $("#edit-newPassword")
      .addClass("is-invalid")
      .next(".invalid-feedback")
      .text("Please provide a password.")
      .show();
    $("#edit-confirmPassword")
      .next(".invalid-feedback")
      .text("Please provide a password for confirmation.")
      .show();
    return;
  } else {
    $("#edit-confirmPassword").removeClass("is-invalid").addClass("is-valid");
    $("#edit-newPassword").removeClass("is-invalid").addClass("is-valid");
  }
  Swal.fire({
    title: "Are you sure?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#1c3464",
    cancelButtonColor: "#6c757d",
    cancelButtonText: "No",
    confirmButtonText: "Yes",
  }).then((result) => {
    if (result.isConfirmed) {
      const data = {
        userId: $("#userId").val(), // Assuming user ID is needed for the request
        username: $("#edit-username").val(),
        fname: $("#edit-firstname").val(),
        mname: $("#edit-middlename").val(),
        lname: $("#edit-lastname").val(),
        new_password: newPassword,
      };

      $.post("./api/update-profile.php", data)
        .done((response) => {
          Swal.fire({
            title: "Updated!",
            icon: "success",
            showConfirmButton: false,
            timer: 1000,
            didClose: () => {
              window.location.reload(); // Reload the page to reflect changes
            },
          });
        })
        .fail((error) => {
          // Handle error response
          alert("Error updating profile.");
        });
    }
  });
  // If validation passes, submit the form via AJAX
});
$("#add-transaction").submit(function (event) {
  event.preventDefault();
  function getDatalistValue(input, datalistId) {
    const value = $(input).val();
    const option = $(`#${datalistId} option`).filter(function () {
      return this.value === value;
    });
    return option.length ? option.data("id") : null;
  }
  const hauler_id = getDatalistValue($("#hauler"), "haulers");
  const vehicle_id = getDatalistValue($("#plate-number"), "plate-numbers");
  const driver_id = getDatalistValue($("#driver-name"), "driver-names");
  const helper_id = getDatalistValue($("#helper-name"), "helper-names");
  const time_of_departure = $("#time-departure").val();
  if (!hauler_id) {
    $("#hauler").addClass("is-invalid");
    $("#hauler").siblings(".invalid-feedback").text("Hauler does not exist");
    return;
  } else {
    $("#hauler").removeClass("is-invalid");
  }
  if (!vehicle_id) {
    $("#plate-number").addClass("is-invalid");
    $("#plate-number")
      .siblings(".invalid-feedback")
      .text("Plate Number does not exist");
    return;
  } else {
    $("#plate-number").removeClass("is-invalid");
  }
  if (!driver_id) {
    $("#driver-name").addClass("is-invalid");
    $("#driver-name")
      .siblings(".invalid-feedback")
      .text("Driver does not exist");
    return;
  } else {
    $("#driver-name").removeClass("is-invalid");
  }
  if (!helper_id) {
    $("#helper-name").addClass("is-invalid");
    $("#helper-name")
      .siblings(".invalid-feedback")
      .text("Helper does not exist");
    return;
  } else {
    $("#helper-name").removeClass("is-invalid");
  }
  Swal.fire({
    title: "Are you sure?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#1c3464",
    cancelButtonColor: "#6c757d",
    cancelButtonText: "No",
    confirmButtonText: "Yes",
  }).then((result) => {
    if (result.isConfirmed) {
      const data = {
        to_reference: $("#to-reference").val(),
        guia: $("#guia").val(),
        hauler_id: hauler_id,
        vehicle_id: vehicle_id,
        driver_id: driver_id,
        helper_id: helper_id,
        project_id: $("#project").val(),
        no_of_bales: $("#no-of-bales").val(),
        kilos: $("#kilos").val(),
        origin_id: $("#origin_id").val(),
        time_of_departure: time_of_departure,
      };
      $.post("./api/add/add-transaction.php", data)
        .done((result) => {
          if (result == "Existing TO reference!") {
            Swal.fire({
              icon: "error",
              title: "Existing TO reference!",
              showConfirmButton: false,
              timer: 1000,
            });
          } else if (result == "Invalid time of departure!") {
            $("#time-departure").addClass("is-invalid");
            $("#time-departure")
              .siblings(".invalid-feedback")
              .text("Invalid time of departure");
            return;
          } else {
            Swal.fire({
              title: "Added!",
              text: "Transaction has been added.",
              icon: "success",
              showConfirmButton: false,
              timer: 1000,
              didClose: () => {
                window.location.reload();
              },
            });
          }
        })
        .fail((err) => {
          Swal.fire("Error!", err, "error");
        });
    }
  });
});

$("#to-reference, #no-of-bales, #kilos").on("input", function (e) {
  let value = e.target.value;
  value = value.replace(/\D/g, "");
  e.target.value = value;
});
