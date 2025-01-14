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
// Enhanced form validation and submission handler
$("#add-branch-transaction").submit(async function (event) {
  event.preventDefault();

  // Improved datalist value getter with type checking
  const getDatalistValue = (input, datalistId) => {
    const value = $(input).val()?.trim();
    if (!value) return null;

    const option = $(`#${datalistId} option`).filter(function () {
      return this.value === value;
    });
    return option.length > 0 ? option.data("id") : null;
  };

  // Form validation
  const validateForm = () => {
    const errors = [];

    // Required field validation
    const requiredFields = {
      "TO Reference": "#add-to-reference",
      GUIA: "#add-guia",
      Hauler: "#add-hauler",
      "Plate Number": "#add-plate-number",
      "Driver Name": "#add-driver-name",
      "Helper Name": "#add-helper-name",
      "No of Bales": "#add-no-of-bales",
      Kilos: "#add-kilos",
    };

    Object.entries(requiredFields).forEach(([label, selector]) => {
      if (!$(selector).val()?.trim()) {
        errors.push(`${label} is required`);
      }
    });

    // Numeric validation
    if (!/^\d+$/.test($("#add-no-of-bales").val())) {
      errors.push("No of Bales must be a valid number");
    }

    if (!/^\d+(\.\d{1,2})?$/.test($("#add-kilos").val())) {
      errors.push("Kilos must be a valid number with up to 2 decimal places");
    }

    // Time validation
    const departureTime = new Date($("#add-time-departure").val());
    if (departureTime < new Date()) {
      errors.push("Time of Departure cannot be in the past");
    }

    return errors;
  };

  // Validate datalist selections
  const haulerId = getDatalistValue("#add-hauler", "add-haulers");
  const plateNumberId = getDatalistValue(
    "#add-plate-number",
    "add-plate-numbers"
  );
  const driverId = getDatalistValue("#add-driver-name", "add-driver-names");
  const helperId = getDatalistValue("#add-helper-name", "add-helper-names");

  if (!haulerId || !plateNumberId || !driverId || !helperId) {
    alert(
      "Please ensure all selection fields are chosen from the provided options."
    );
    return;
  }

  // Validate form
  const validationErrors = validateForm();
  if (validationErrors.length > 0) {
    alert(validationErrors.join("\n"));
    return;
  }

  // Prepare form data
  const formData = {
    action: "branch add transaction",
    "to-reference": $("#add-to-reference").val().trim(),
    guia: $("#add-guia").val().trim(),
    "hauler-id": haulerId,
    "vehicle-id": plateNumberId,
    "driver-id": driverId,
    "helper-id": helperId,
    "project-id": $("#add-project").val(),
    "no-of-bales": $("#add-no-of-bales").val(),
    kilos: $("#add-kilos").val(),
    "origin-id": $("#add-origin_id").val(),
    "time-departure": $("#add-time-departure").val(),
  };

  try {
    const response = await fetch("../../api/transaction.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(formData),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      alert("Transaction successfully added!");
      $("#add-branch-transaction")[0].reset();
      // Optionally refresh any related data displays
      if (typeof refreshTransactionList === "function") {
        refreshTransactionList();
      }
    } else {
      alert(
        result.message || "An error occurred while saving the transaction."
      );
    }
  } catch (error) {
    console.error("Error submitting transaction:", error);
    alert("A network error occurred. Please try again later.");
  }
});

// Clear form handler
$("#add-clear").click(function () {
  if (confirm("Are you sure you want to clear all fields?")) {
    $("#add-branch-transaction")[0].reset();
    $(".is-invalid").removeClass("is-invalid");
  }
});

// Real-time numeric validation
$("#add-no-of-bales, #add-kilos").on("input", function () {
  const value = this.value;
  const isKilos = this.id === "add-kilos";
  const pattern = isKilos ? /^\d*\.?\d{0,2}$/ : /^\d*$/;

  if (!pattern.test(value)) {
    this.value = value.slice(0, -1);
  }
});

$("#to-reference, #no-of-bales, #kilos").on("input", function (e) {
  let value = e.target.value;
  value = value.replace(/\D/g, "");
  e.target.value = value;
});
