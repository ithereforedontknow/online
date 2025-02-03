const transactionManager = {
  apiBase: "../../api/transaction.php",
  async request(action, data = {}) {
    try {
      const response = await $.ajax({
        url: this.apiBase,
        type: "POST",
        data: { action, ...data },
        dataType: "json",
      });
      if (!response.success) {
        throw new Error(response.message);
      }
      return response;
    } catch (error) {
      console.error("Error:", error.message);
      showError(error.message || "An unexpected error occurred.");
      throw error;
    }
  },
  async createTransaction(data) {
    return this.request("create", data);
  },
  async updateTransaction(data) {
    return this.request("update", data);
  },
  async cancelTransaction(data) {
    return this.request("cancel", data);
  },
  async divertTransaction(data) {
    return this.request("divert", data);
  },
  async updateFinishedTransaction(data) {
    return this.request("update finished", data);
  },
};
$(document).ready(() => {
  // Initially load the Departed list
  refreshDepartedList();
  refreshFinishedTransactions();

  // Pagination navigation click event
  $(".pagination-nav").click(function (e) {
    e.preventDefault(); // Prevent default action (i.e., navigating to href="#")

    // Hide all tables
    $(".departed-table, .arrived-table, .cancelled-table").addClass("d-none");

    // Remove 'active' class from all pagination buttons
    $(".pagination-nav").parent().removeClass("active");

    // Add 'active' class to clicked button
    $(this).parent().addClass("active");

    // Check which button was clicked and load corresponding list
    if ($(this).text().trim() === "Departed") {
      refreshDepartedList();
    } else if ($(this).text().trim() === "Arrived") {
      refreshArrivedList();
    } else if ($(this).text().trim() === "Cancelled") {
      refreshCancelledList();
    }
  });
});

function showError(message) {
  // Implement error display logic (e.g., alert or a designated error div)
  // alert(message);
  console.error(message);
}
function openOffCanvas(offcanvasId) {
  const offcanvasElement = document.querySelector(offcanvasId);
  if (offcanvasElement) {
    const offcanvas = new bootstrap.Offcanvas(offcanvasElement);
    offcanvas.show();
  } else {
    console.error(`Offcanvas with ID ${offcanvasId} not found.`);
  }
}

function editTransaction(transaction) {
  document.querySelector("#edit-transaction-id-new").value =
    transaction.transaction_id;
  document.querySelector("#edit-to-reference").value = transaction.to_reference;
  document.querySelector("#edit-guia").value = transaction.guia;
  document.querySelector("#edit-project").value = transaction.project_id;
  document.querySelector("#edit-no-of-bales").value = transaction.no_of_bales;
  document.querySelector("#edit-kilos").value = transaction.kilos;
  document.querySelector("#edit-origin").value = transaction.origin_id;
  document.querySelector("#edit-time-departure").value =
    transaction.time_of_departure;
  $("#editTransactionModal").modal("show");
}

$("#add-transaction").submit(async function (e) {
  e.preventDefault();
  function getDatalistValue(inputSelector, datalistId) {
    const value = $(inputSelector).val(); // Get the input's value
    const option = $(`#${datalistId} option`).filter(function () {
      return $(this).val() === value; // Match the option's value exactly
    });
    return option.length ? option.attr("data-id") : null; // Return the data-id if found
  }

  // Extracting IDs from the datalist inputs
  const hauler_id = getDatalistValue("#hauler", "haulers");
  const vehicle_id = getDatalistValue("#plate-number", "plate-numbers");
  const driver_id = getDatalistValue("#driver-name", "driver-names");
  const helper_id = getDatalistValue("#helper-name", "helper-names");

  // DateTime validation
  const arrivalDateTime = $("#arrival-time").val();
  const departureDateTime = $("#time-departure").val();
  function validateDateNotInPast(dateTime) {
    if (!dateTime) return false;

    const today = new Date();
    today.setHours(0, 0, 0, 0); // Set to start of the day
    const inputDate = new Date(dateTime);

    // Ensure the input date is not before today
    return inputDate >= today;
  }

  function validateDateTime(arrivalDateTime, departureDateTime) {
    if (!arrivalDateTime || !departureDateTime) return false;

    const arrival = new Date(arrivalDateTime);
    const departure = new Date(departureDateTime);

    // Ensure arrival time is after departure time
    return arrival > departure;
  }

  function validateDepartureTime(departureDateTime) {
    if (!departureDateTime) return false;

    const today = new Date();
    const departure = new Date(departureDateTime);

    // Ensure departure time is in the future
    return departure > today;
  }

  // Check if departure date is not in the past
  if (!validateDateNotInPast(departureDateTime)) {
    $("#time-departure").addClass("is-invalid");
    if (!$("#time-departure").siblings(".invalid-feedback").length) {
      $("#time-departure").after(
        '<div class="invalid-feedback">Departure date must not be in the past</div>'
      );
    } else {
      $("#time-departure")
        .siblings(".invalid-feedback")
        .text("Departure date must not be in the past");
    }
    return;
  } else {
    $("#time-departure").removeClass("is-invalid");
  }

  // Check arrival time vs departure time
  if (arrivalDateTime && departureDateTime) {
    if (!validateDateTime(arrivalDateTime, departureDateTime)) {
      $("#arrival-time").addClass("is-invalid");
      if (!$("#arrival-time").siblings(".invalid-feedback").length) {
        $("#arrival-time").after(
          '<div class="invalid-feedback">Arrival time must be later than departure time</div>'
        );
      } else {
        $("#arrival-time")
          .siblings(".invalid-feedback")
          .text("Arrival time must be later than departure time");
      }
      return;
    } else {
      $("#arrival-time").removeClass("is-invalid");
    }
  }

  // Check if arrival date is not in the past
  if (!validateDateNotInPast(arrivalDateTime)) {
    $("#arrival-time").addClass("is-invalid");
    if (!$("#arrival-time").siblings(".invalid-feedback").length) {
      $("#arrival-time").after(
        '<div class="invalid-feedback">Arrival date must not be in the past</div>'
      );
    } else {
      $("#arrival-time")
        .siblings(".invalid-feedback")
        .text("Arrival date must not be in the past");
    }
    return;
  } else {
    $("#arrival-time").removeClass("is-invalid");
  }

  // Validation for Datalist Values
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
  const data = {
    "to-reference": $("#to-reference").val(),
    guia: $("#guia").val(),
    hauler_id: hauler_id,
    vehicle_id: vehicle_id,
    driver_id: driver_id,
    helper_id: helper_id,
    project_id: $("#project").val(),
    "no-of-bales": $("#no-of-bales").val(),
    kilos: $("#kilos").val(),
    origin: $("#origin").val(),
    "arrival-time": arrivalDateTime,
    time_departure: departureDateTime,
    created_by: $("#created_by").val(),
  };

  try {
    await transactionManager.createTransaction(data);
    $("#addTransactionModal").modal("hide");
    refreshArrivedList();
  } catch (error) {
    console.log(data);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error,
      showConfirmButton: false,
      timer: 1500,
    });
    console.error("Transaction creation failed:", error);
    // Optionally, show an error message to the user
  }
});
$("#edit-transaction-actual-form").submit(async function (e) {
  e.preventDefault();

  function getDatalistValue(inputSelector, datalistId) {
    const value = $(inputSelector).val(); // Get the input's value
    const option = $(`#${datalistId} option`).filter(function () {
      return $(this).val() === value; // Match the option's value exactly
    });
    return option.length ? option.attr("data-id") : null; // Return the data-id if found
  }

  // Extracting IDs from the datalist inputs
  const hauler_id = getDatalistValue("#edit-hauler", "edit-haulers");
  const vehicle_id = getDatalistValue(
    "#edit-plate-number",
    "edit-plate-numbers"
  );
  const driver_id = getDatalistValue("#edit-driver-name", "edit-driver-names");
  const helper_id = getDatalistValue("#edit-helper-name", "edit-helper-names");

  // Extracting other form values
  const transaction_id = $("#edit-transaction-id-new").val();
  const to_reference = $("#edit-to-reference").val();
  const guia = $("#edit-guia").val();
  const origin_id = $("#edit-origin").val();

  // Validation for Datalist Values
  if (!hauler_id) {
    $("#edit-hauler").addClass("is-invalid");
    $("#edit-hauler")
      .siblings(".invalid-feedback")
      .text("Hauler does not exist");
    return;
  } else {
    $("#edit-hauler").removeClass("is-invalid");
  }

  if (!vehicle_id) {
    $("#edit-plate-number").addClass("is-invalid");
    $("#edit-plate-number")
      .siblings(".invalid-feedback")
      .text("Plate Number does not exist");
    return;
  } else {
    $("#edit-plate-number").removeClass("is-invalid");
  }

  if (!driver_id) {
    $("#edit-driver-name").addClass("is-invalid");
    $("#edit-driver-name")
      .siblings(".invalid-feedback")
      .text("Driver does not exist");
    return;
  } else {
    $("#edit-driver-name").removeClass("is-invalid");
  }

  if (!helper_id) {
    $("#edit-helper-name").addClass("is-invalid");
    $("#edit-helper-name")
      .siblings(".invalid-feedback")
      .text("Helper does not exist");
    return;
  } else {
    $("#edit-helper-name").removeClass("is-invalid");
  }

  if (!to_reference) {
    $("#edit-to-reference").addClass("is-invalid");
    $("#edit-to-reference")
      .siblings(".invalid-feedback")
      .text("TO Reference is required");
    return;
  } else {
    $("#edit-to-reference").removeClass("is-invalid");
  }

  if (!guia) {
    $("#edit-guia").addClass("is-invalid");
    $("#edit-guia").siblings(".invalid-feedback").text("GUIA is required");
    return;
  } else {
    $("#edit-guia").removeClass("is-invalid");
  }

  // Constructing the data object
  const data = {
    transaction_id: transaction_id,
    to_reference: to_reference,
    guia: guia,
    hauler_id: hauler_id,
    vehicle_id: vehicle_id,
    driver_id: driver_id,
    helper_id: helper_id,
    origin_id: origin_id,
    project_id: $("#project").val(),
    no_of_bales: $("#edit-no-of-bales").val(),
    kilos: $("#edit-kilos").val(),
    time_departure: $("#edit-time-departure").val(),
  };

  try {
    await transactionManager.updateTransaction(data);
    $("#editTransactionModal").modal("hide");
    refreshDepartedList();
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error,
      showConfirmButton: false,
      timer: 1500,
    });
    // Optionally, show an error message to the user
  }
});

function cancelTransaction(transaction) {
  console.log(transaction);

  const data = {
    transaction_id: transaction.transaction_id,
    created_by: transaction.created_by,
    vehicle_id: transaction.vehicle_id,
    driver_id: transaction.driver_id,
    helper_id: transaction.helper_id,
  };
  Swal.fire({
    title: "Are you sure?",
    text: "Confirm canceling this transaction",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, cancel it!",
    confirmButtonColor: "#1f3a69",
    cancelButtonColor: "#5c636a",
  }).then((result) => {
    if (result.isConfirmed) {
      transactionManager
        .cancelTransaction(data)
        .then(() => {
          refreshDepartedList();
        })
        .catch((error) => {
          console.error("Transaction cancellation failed:", error);
          // Optionally, show an error message to the user
        });
    }
  });
}
function cancelArrivedTransaction(transaction) {
  console.log(transaction);

  const data = {
    transaction_id: transaction.transaction_id,
    to_reference: transaction.to_reference,
    created_by: transaction.created_by,
    vehicle_id: transaction.vehicle_id,
    driver_id: transaction.driver_id,
    helper_id: transaction.helper_id,
  };
  Swal.fire({
    title: "Are you sure?",
    text: "Confirm canceling this transaction",
    icon: "warning",
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: "Yes, cancel it!",
    denyButtonText: "Divert to other location",
    confirmButtonColor: "#1f3a69",
    denyButtonColor: "#5c636a",
    cancelButtonColor: "#5c636a",
  }).then((result) => {
    if (result.isConfirmed) {
      transactionManager
        .cancelTransaction(data)
        .then(() => {
          refreshArrivedList();
        })
        .catch((error) => {
          console.error("Transaction cancellation failed:", error);
          // Optionally, show an error message to the user
        });
    } else if (result.isDenied) {
      $("#divert-transaction-transaction-id").val(transaction.transaction_id);
      $("#divert-transaction-to-reference").val(transaction.to_reference);
      $("#divertTransactionModal").modal("show");
    }
  });
}

function printTransaction(transaction_id) {
  const formData = new FormData();
  formData.append("action", "print transaction");
  formData.append("transaction_id", transaction_id);

  fetch("../../api/transaction.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        return response.json().then((err) => Promise.reject(err));
      }
      return response.blob();
    })
    .then((blob) => {
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = `transaction_${transaction_id}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    })
    .catch((error) => {
      console.error("Failed to print transaction:", error);
      alert("Failed to generate PDF. Please try again.");
    });
}
function editFinishedtransactions(transaction) {
  console.log(transaction.origin_id);
  $("#finished-transaction-id").val(transaction.transaction_id);
  $("#finished-to-reference").val(transaction.to_reference);
  $("#finished-guia").val(transaction.guia);
  // $("#finished-hauler").val(transaction.hauler_id);
  // $("#finished-plate-number").val(transaction.vehicle_id);
  // $("#finished-driver-name").val(transaction.driver_id);
  // $("#finished-helper-name").val(transaction.helper_id);
  $("#finished-project").val(transaction.project_id);
  $("#finished-no-of-bales").val(transaction.no_of_bales);
  $("#finished-kilos").val(transaction.kilos);
  $("#finished-origin").val(transaction.origin_id);
  $("#finished-arrival-time").val(transaction.arrival_time.slice(0, -3));
  $("#finished-time-departure").val(transaction.timeOfDeparture.slice(0, -3));
  $("#finished-transfer-in-line").val(transaction.transfer_in_line);
  $("#finished-queue-ordinal").val(transaction.ordinal);
  $("#finished-queue-shift").val(transaction.shift);
  $("#finished-queue-schedule").val(transaction.schedule);
  $("#finished-queue-number").val(transaction.queue_number);
  $("#finished-queue-priority").val(transaction.priority);
  $("#finished-time-entry").val(transaction.time_of_entry.slice(0, -3));
  $("#finished-unloading-start").val(
    transaction.unloading_time_start.slice(0, -3)
  );
  $("#finished-unloading-end").val(transaction.unloading_time_end.slice(0, -3));
  $("#finished-departure").val(transaction.time_of_departure.slice(0, -3));
  $("#finished-transfer-out-kilos").val(transaction.transfer_out_kilos);
  $("#finished-scrap").val(transaction.scrap);
  $("#finished-remarks").val(transaction.remarks);
  $("#editFinishedTransactionModal").modal("show");
}
// Modified event handler
$(document).on("submit", ".arrival-transaction-form", async function (event) {
  event.preventDefault();
  const transactionId = $(this).find("#arrived-transaction-id").val();
  const arrivalTime = $(this).find("#arrived-arrival-time").val();

  if (!transactionId || !arrivalTime) {
    Swal.fire({
      title: "Error",
      text: "Transaction ID and Arrival Time are required.",
      icon: "error",
      confirmButtonText: "OK",
    });
    return;
  }

  const data = {
    action: "add to arrived",
    transaction_id: transactionId,
    arrival_time: arrivalTime,
  };

  try {
    const response = await $.ajax({
      url: "../../api/transaction.php",
      method: "POST",
      data: data,
      dataType: "json",
    });

    if (response.success) {
      Swal.fire({
        title: "Updated!",
        text: "Transaction added to arrived successfully.",
        icon: "success",
        showConfirmButton: true,
        confirmButtonText: "Print transaction form",
        confirmButtonColor: "#1f3a69",
        showCancelButton: true,
        cancelButtonText: "No",
        cancelButtonColor: "#5c636a",
      }).then((result) => {
        console.log(transactionId);
        if (result.isConfirmed) {
          printTransaction(transactionId);
        }
      });
      refreshDepartedList();
    } else {
      throw new Error(response.message || "Unknown error occurred.");
    }
  } catch (error) {
    console.error("Error submitting form:", error);
    Swal.fire({
      title: "Error",
      text: error.message || "Failed to add transaction to arrived.",
      icon: "error",
      showConfirmButton: false,
      timer: 1500,
    });
  }
});
$("#divert-transaction-form").submit(async function (e) {
  e.preventDefault();

  const result = await Swal.fire({
    title: "Set Status",
    text: "Make Drivers, Helpers, and Vehicle Available",
    icon: "info",
    showCancelButton: true,
    confirmButtonColor: "#1f3a69",
    cancelButtonColor: "#5c636a",
    confirmButtonText: "Yes",
    denyButtonText: "No",
    showDenyButton: true,
    denyButtonColor: "#5c636a",
  });

  if (!result.isDismissed) {
    const data = {
      action: "divert",
      transaction_id: $("#divert-transaction-transaction-id").val(),
      to_reference: $("#divert-transaction-to-reference").val(),
      origin_id: $("#divert-transaction-branch").val(),
      remarks: $("#divert-transaction-remarks").val(),
      set_available: result.isConfirmed,
    };

    try {
      const response = await $.ajax({
        url: "../../api/transaction.php",
        method: "POST",
        data: data,
        dataType: "json",
      });

      if (response.success) {
        refreshArrivedList();
        $("#divertTransactionModal").modal("hide");

        const printResult = await Swal.fire({
          title: "Updated!",
          text: "Transaction diverted to another branch.",
          icon: "success",
          showConfirmButton: true,
          confirmButtonText: "Print transaction form",
          confirmButtonColor: "#1f3a69",
          showCancelButton: true,
          cancelButtonText: "No",
          cancelButtonColor: "#5c636a",
        });

        if (printResult.isConfirmed) {
          $("#divertTransactionModal").modal("hide");
          printTransaction(data.transaction_id);
        }
      } else {
        throw new Error(response.data || "Transaction diversion failed");
      }
    } catch (error) {
      console.error("Transaction diversion failed:", error);
      Swal.fire({
        title: "Error",
        text:
          error.message || "Failed to divert transaction. Please try again.",
        icon: "error",
        confirmButtonColor: "#1f3a69",
      });
    }
  }
});

$("#finished-transaction-form").submit(async function (e) {
  e.preventDefault();

  function getDatalistValue(inputSelector, datalistId) {
    const value = $(inputSelector).val();
    const option = $(`#${datalistId} option`).filter(function () {
      return $(this).val() === value;
    });
    return option.length ? option.attr("data-id") : null;
  }

  // Extract IDs from datalist inputs
  const hauler_id = getDatalistValue("#finished-hauler", "finished-haulers");
  const vehicle_id = getDatalistValue(
    "#finished-plate-number",
    "finished-plate-numbers"
  );
  const driver_id = getDatalistValue(
    "#finished-driver-name",
    "finished-driver-names"
  );
  const helper_id = getDatalistValue(
    "#finished-helper-name",
    "finished-helper-names"
  );

  // DateTime validation functions
  function validateDateNotInPast(dateTime) {
    if (!dateTime) return false;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const inputDate = new Date(dateTime);
    return inputDate >= today;
  }
  const arrivalTime = $("#finished-arrival-time").val();
  const timeDeparture = $("#finished-time-departure").val();
  const timeEntry = $("#finished-time-entry").val();
  const unloadingStart = $("#finished-unloading-start").val();
  const unloadingEnd = $("#finished-unloading-end").val();
  const departure = $("#finished-departure").val();
  // Validate time sequence
  if (
    timeDeparture &&
    arrivalTime &&
    new Date(timeDeparture) >= new Date(arrivalTime)
  ) {
    // Show error for invalid time sequence
    $("#finished-time-departure, #finished-arrival-time")
      .addClass("is-invalid")
      .siblings(".invalid-feedback")
      .text("Finished time departure must be less than finished arrival time");
    return;
  }
  if (
    timeEntry &&
    unloadingStart &&
    new Date(timeEntry) >= new Date(unloadingStart)
  ) {
    // Show error for invalid time sequence
    $("#finished-time-entry, #finished-unloading-start")
      .addClass("is-invalid")
      .siblings(".invalid-feedback")
      .text("Finished time entry must be less than unloading start");
    return;
  }
  if (
    unloadingStart &&
    unloadingEnd &&
    new Date(unloadingStart) >= new Date(unloadingEnd)
  ) {
    // Show error for invalid time sequence
    $("#finished-unloading-start, #finished-unloading-end")
      .addClass("is-invalid")
      .siblings(".invalid-feedback")
      .text("Unloading start must be less than unloading end");
    return;
  }
  if (
    unloadingEnd &&
    departure &&
    new Date(unloadingEnd) >= new Date(departure)
  ) {
    // Show error for invalid time sequence
    $("#finished-unloading-end, #finished-departure")
      .addClass("is-invalid")
      .siblings(".invalid-feedback")
      .text("Unloading end must be less than departure");
    return;
  }

  // Validate datalist inputs
  const datalistValidations = [
    {
      id: "finished-hauler",
      value: hauler_id,
      message: "Hauler does not exist",
    },
    {
      id: "finished-plate-number",
      value: vehicle_id,
      message: "Plate Number does not exist",
    },
    {
      id: "finished-driver-name",
      value: driver_id,
      message: "Driver does not exist",
    },
    {
      id: "finished-helper-name",
      value: helper_id,
      message: "Helper does not exist",
    },
  ];

  for (const validation of datalistValidations) {
    if (!validation.value) {
      $(`#${validation.id}`)
        .addClass("is-invalid")
        .siblings(".invalid-feedback")
        .text(validation.message);
      return;
    } else {
      $(`#${validation.id}`).removeClass("is-invalid");
    }
  }

  // Collect all form data
  const data = {
    transaction_id: $("#finished-transaction-id").val(),
    to_reference: $("#finished-to-reference").val(),
    guia: $("#finished-guia").val(),
    hauler_id: hauler_id,
    vehicle_id: vehicle_id,
    driver_id: driver_id,
    helper_id: helper_id,
    project_id: $("#finished-project").val(),
    no_of_bales: $("#finished-no-of-bales").val(),
    kilos: $("#finished-kilos").val(),
    origin_id: $("#finished-origin").val(),
    time_departure: timeDeparture,
    arrival_time: arrivalTime,

    // Queue information
    transfer_in_line: $("#finished-transfer-in-line").val(),
    queue_ordinal: $("#finished-queue-ordinal").val(),
    queue_shift: $("#finished-queue-shift").val(),
    queue_schedule: $("#finished-queue-schedule").val(),
    queue_number: $("#finished-queue-number").val(),
    queue_priority: $("#finished-queue-priority").val(),

    // Timeline information
    time_entry: timeEntry,
    unloading_start: unloadingStart,
    unloading_end: unloadingEnd,
    departure: departure,

    // Additional information
    transfer_out_kilos: $("#finished-transfer-out-kilos").val(),
    scrap: $("#finished-scrap").val(),
    remarks: $("#finished-remarks").val(),
  };

  try {
    // Assuming you have a transactionManager with a method to handle finished transactions
    await transactionManager.updateFinishedTransaction(data);
    // Hide the form modal/offcanvas if needed
    $("#editFinishedTransactionModal").modal("hide");
    refreshFinishedTransactions();
  } catch (error) {
    console.error("Finished transaction creation failed:", error);
    // Optionally show an error message to the user
  }
});
async function refreshDepartedList() {
  $(".departed-table").removeClass("d-none");
  try {
    const response = await $.ajax({
      url: "../../api/transaction.php",
      method: "POST",
      data: { action: "list", status: "departed" },
      dataType: "json",
    });

    if (response.success) {
      const list = $("#departed-list");

      if ($.fn.DataTable.isDataTable("#departed-table")) {
        $("#departed-table").DataTable().destroy();
      }

      const rows = response.data.transactions
        .map(
          (transaction) => `
          <tr>
            <td class="text-center">${transaction.to_reference}</td>
            <td class="text-center">${transaction.hauler_name}</td>
            <td class="text-center">${transaction.plate_number}</td>
            <td class="text-center">${transaction.project_name}</td>
            <td class="text-center">${transaction.origin_name}</td>
            <td class="text-center">
              <form class="arrival-transaction-form d-flex justify-content-center align-items-center">
                <input type="hidden" name='arrived-transaction-id' id="arrived-transaction-id" value="${
                  transaction.transaction_id
                }" />
                <input type="datetime-local" class="form-control" name='arrived-arrival-time' id="arrived-arrival-time" required style="width: auto;">
                <button type="submit" class="btn btn-primary ms-2">Save</button>
              </form>
            </td>
            <td class="text-center">
              <button type="button" class="btn btn-primary ms-2" onclick='editTransaction(${JSON.stringify(
                transaction
              )})'>Edit</button>
              <button type="button" class="btn btn-secondary ms-2" onclick='cancelTransaction(${JSON.stringify(
                transaction
              )})'><i class="fa-solid fa-cancel"></i></button>
            </td>
          </tr>
        `
        )
        .join("");

      list.html(rows);
      $("#departed-table").DataTable({ responsive: true, lengthChange: false });
    } else {
      showError(response.message || "Unable to fetch departed transactions.");
    }
  } catch (error) {
    console.error("Error fetching departed transactions:", error);
  }
}

async function refreshCancelledList() {
  $(".cancelled-table").removeClass("d-none");
  try {
    const response = await $.ajax({
      url: "../../api/transaction.php",
      method: "POST",
      data: { action: "list", status: "cancelled" },
      dataType: "json",
    });

    if (response.success) {
      const list = $("#cancelled-list");

      if ($.fn.DataTable.isDataTable("#cancelled-table")) {
        $("#cancelled-table").DataTable().destroy();
      }

      const rows = response.data.transactions
        .map(
          (transaction) => `
          <tr>
            <td class="text-center">${transaction.to_reference}</td>
            <td class="text-center">${transaction.guia}</td>
            <td class="text-center">${transaction.hauler_name}</td>
            <td class="text-center">${transaction.plate_number}</td>
            <td class="text-center">${transaction.project_name}</td>
            <td class="text-center">${transaction.origin_name}</td>
            <td class="text-center">${
              new Date(transaction.time_of_departure).toLocaleString("en-US", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
              }) || "-"
            }</td>
            <td class="text-center">&#x20B1; ${parseFloat(
              transaction.demurrage
            ).toFixed(2)}</td>
          </tr>
        `
        )
        .join("");

      list.html(rows);
      $("#cancelled-table").DataTable({
        responsive: true,
        lengthChange: false,
      });
    } else {
      showError(response.message || "Unable to fetch cancelled transactions.");
    }
  } catch (error) {
    console.error("Error fetching cancelled transactions:", error);
  }
}
async function refreshArrivedList() {
  $(".arrived-table").removeClass("d-none");
  try {
    const response = await $.ajax({
      url: "../../api/queue.php",
      method: "POST",
      data: { action: "list arrived", status: "arrived" },
      dataType: "json",
    });

    if (response.success) {
      const list = $("#arrived-list");

      if ($.fn.DataTable.isDataTable("#arrived-table")) {
        $("#arrived-table").DataTable().destroy();
      }

      const rows = response.data.transactions
        .map(
          (transaction) => `
          <tr>
            <td class="text-center">${transaction.to_reference}</td>
            <td class="text-center">${transaction.hauler_name}</td>
            <td class="text-center">${transaction.plate_number}</td>
            <td class="text-center">${transaction.project_name}</td>
            <td class="text-center">${transaction.origin_name}</td>
            <td class="text-center">${transaction.arrival_time}</td>
            <td class="text-center">
              <button type="button" class="btn btn-primary" onclick="queueTransaction(${
                transaction.transaction_id
              })">Queue</button>
              <button type="button" class="btn btn-secondary ms-2" onclick='cancelArrivedTransaction(${JSON.stringify(
                transaction
              )})'><i class="fa-solid fa-cancel"></i></button>
              <button type="button" class="btn btn-secondary ms-2" onclick="printTransaction(${
                transaction.transaction_id
              })"><i class="fa-solid fa-print"></i></button>
            </td>
          </tr>
        `
        )
        .join("");

      list.html(rows);
      $("#arrived-table").DataTable({
        responsive: true,
        lengthChange: false,
      });
    } else {
      showError(response.message || "Unable to fetch arrived transactions.");
    }
  } catch (error) {
    console.error("Error fetching arrived transactions:", error);
  }
}

async function refreshTransactionStatus() {
  try {
    const selectedStatus = $("#statusFilter").val();
    const response = await $.ajax({
      url: "../../api/transaction.php",
      method: "POST",
      data: { action: "transaction status" },
      dataType: "json",
    });

    if (response.success) {
      const statusProgressMap = {
        departed: 17.5,
        arrived: 34,
        queue: 50.5,
        standby: 67,
        ongoing: 83.5,
      };

      const filteredTransactions = selectedStatus
        ? response.data.filter(
            (transaction) =>
              transaction.status.toLowerCase() === selectedStatus.toLowerCase()
          )
        : response.data;

      const cards = filteredTransactions.map((transaction) => {
        const progressValue =
          statusProgressMap[transaction.status.toLowerCase()] || 0;

        return `
              <div class="col-md-4" style='cursor: pointer'>
                  <div class="flip-card">
                      <div class="flip-card-inner">
                          <!-- Front of card -->
                          <div class="flip-card-front">
                              <div class="card shadow-sm mb-4">
                                  <div class="card-body">
                                      <h6 class="card-title fw-bold mb-3">Plate Number: ${
                                        transaction.plate_number
                                      }</h6>
                                      <h6 class="card-title fw-bold mb-3">Driver: ${
                                        transaction.driver_fname
                                      } ${transaction.driver_lname}</h6>
                                      <h6 class="card-title fw-bold mb-2">Helper: ${
                                        transaction.helper_fname
                                      } ${transaction.helper_lname}</h6>
                                      <p class="card-text">Status: ${
                                        transaction.status
                                      }</p>
                                      <div class="progress mb-3">
                                          <div class="progress-bar progress-bar-striped progress-bar-animated ${
                                            progressValue === 100
                                              ? "bg-success"
                                              : "bg-primary"
                                          }"
                                              role="progressbar"
                                              style="width: ${progressValue}%"
                                              aria-valuenow="${progressValue}"
                                              aria-valuemin="0"
                                              aria-valuemax="100">
                                              ${progressValue}%
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          
                          <!-- Back of card -->
                          <div class="flip-card-back">
                              <div class="card shadow-sm">
                                  <div class="card-body">
                                      <div class="details-row">
                                          <span class="details-label">Arrival Time:</span>
                                          <span>${
                                            transaction.arrival_time
                                              ? new Intl.DateTimeFormat(
                                                  "en-US",
                                                  {
                                                    year: "numeric",
                                                    month: "2-digit",
                                                    day: "2-digit",
                                                    hour: "2-digit",
                                                    minute: "2-digit",
                                                  }
                                                ).format(
                                                  new Date(
                                                    transaction.arrival_time
                                                  )
                                                )
                                              : "N/A"
                                          }</span>
                                      </div>
                                      <div class="details-row">
                                          <span class="details-label">Time of Entry (to unload):</span>
                                          <span>${
                                            transaction.time_of_entry
                                              ? new Intl.DateTimeFormat(
                                                  "en-US",
                                                  {
                                                    year: "numeric",
                                                    month: "2-digit",
                                                    day: "2-digit",
                                                    hour: "2-digit",
                                                    minute: "2-digit",
                                                  }
                                                ).format(
                                                  new Date(
                                                    transaction.time_of_entry
                                                  )
                                                )
                                              : "N/A"
                                          }</span>
                                      </div>
                                      <div class="details-row">
                                          <span class="details-label">Ordinal:</span>
                                          <span>${
                                            transaction.ordinal || "N/A"
                                          }</span>
                                      </div>
                                      <div class="details-row">
                                          <span class="details-label">Shift:</span>
                                          <span>${
                                            transaction.shift || "N/A"
                                          }</span>
                                      </div>
                                      <div class="details-row">
                                          <span class="details-label">Schedule:</span>
                                          <span>${
                                            transaction.schedule || "N/A"
                                          }</span>
                                      </div>
                                      <div class="details-row">
                                          <span class="details-label">Transfer in Line:</span>
                                          <span>${
                                            transaction.transfer_in_line ||
                                            "N/A"
                                          }</span>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>`;
      });

      $("#transaction-column").html(cards.join(""));

      // Add click event listeners to the cards
      $(".flip-card").click(function () {
        $(this).toggleClass("flipped");
      });
    } else {
      showError(response.message || "Unable to fetch transaction status.");
    }
  } catch (error) {
    console.error("Error fetching transaction status:", error);
  }
}

// Attach change event listener to the filter dropdown
$("#statusFilter").on("change", function () {
  refreshTransactionStatus();
});

// Initial call to load all transactions
refreshTransactionStatus();

function queueTransaction(transactionId) {
  // open addQueueOffcanvas
  $("#add-queue-transaction-id").val(transactionId);
  $("#addQueueModal").modal("show");
}
$("#add-queue-transaction").submit(async function (e) {
  e.preventDefault();
  const data = {
    action: "add to queue",
    transaction_id: $("#add-queue-transaction-id").val(),
    transfer_in_line: $("#add-queue-transfer-in-line").val(),
    ordinal: $("#add-queue-ordinal").val(),
    shift: $("#add-queue-shift").val(),
    schedule: $("#add-queue-schedule").val(),
    queue_number: $("#add-queue-number").val(),
    priority: $("#add-queue-priority").val(),
  };

  try {
    const response = await $.ajax({
      url: "../../api/queue.php",
      method: "POST",
      data: data,
      dataType: "json",
    });
    if (response.success) {
      refreshArrivedList();
      $("#addQueueModal").modal("hide");
    } else {
      $("#add-queue-number").addClass("is-invalid");
      console.error("Error queueing transaction:", response.message);
    }
  } catch (error) {
    console.error("Transaction queueing failed:", error);
    // Optionally, show an error message to the user
  }
});
// Finished transactions

async function refreshFinishedTransactions() {
  try {
    const response = await $.ajax({
      url: "../../api/transaction.php",
      method: "POST",
      data: { action: "list finished", status: "done" },
      dataType: "json",
    });

    if (response.success) {
      const list = $("#finished-transactions-list");

      if ($.fn.DataTable.isDataTable("#finished-transactions-table")) {
        $("#finished-transactions-table").DataTable().destroy();
      }

      const rows = response.data
        .map(
          (transaction) => `
          <tr>
            <td class="text-center">${
              transaction.to_reference
            }</td><td class="text-center">&#8369; ${parseFloat(
            transaction.demurrage
          ).toFixed(2)}</td>
            <td class="text-center">${transaction.kilos}</td>
            <td class="text-center">
              ${
                transaction.transfer_out_kilos
                  ? transaction.transfer_out_kilos
                  : `<form id="insert-transfer-out-scrap-remarks-${
                      transaction.transaction_id
                    }">
                        <input type="hidden" name="transaction_id" value="${
                          transaction.transaction_id
                        }">
                        <input 
                          type="number" 
                          class="form-control transfer-out-input" 
                          name="transfer_out_kilos" 
                          required 
                          value="${transaction.transfer_out_kilos || ""}" 
                          onkeypress="return isNumberKey(event)">
                     </form>`
              }
            </td>
            <td class="text-center">
              ${
                transaction.scrap
                  ? transaction.scrap
                  : `<input 
                        type="number" 
                        form="insert-transfer-out-scrap-remarks-${
                          transaction.transaction_id
                        }" 
                        class="form-control scrap-input" 
                        name="scrap" 
                        required 
                        value="${transaction.scrap || ""}" 
                        onkeypress="return isNumberKey(event)">`
              }
            </td>
            <td class="text-center">
              ${
                transaction.remarks
                  ? transaction.remarks
                  : `<input 
                        type="text" 
                        form="insert-transfer-out-scrap-remarks-${
                          transaction.transaction_id
                        }" 
                        class="form-control" 
                        name="remarks" 
                        required 
                        value="${transaction.remarks || ""}">`
              }
            </td>
            <td class="text-center">
              ${
                transaction.transfer_out_kilos &&
                transaction.scrap &&
                transaction.remarks
                  ? `<button type="button" class="btn btn-primary" onclick='editFinishedtransactions(${JSON.stringify(
                      transaction
                    )})'>Edit</button>`
                  : `<button type="submit" form="insert-transfer-out-scrap-remarks-${transaction.transaction_id}" class="btn btn-primary">Save</button>`
              }
            </td>
          </tr>
        `
        )
        .join("");

      list.html(rows);
      $("#finished-transactions-table").DataTable({
        responsive: true,
        lengthChange: false,
        ordering: false,
      });

      // Attach submit handler to dynamically created forms
      $("form[id^='insert-transfer-out-scrap-remarks-']").on(
        "submit",
        function (event) {
          event.preventDefault();
          const form = $(this);
          const formData = form.serialize();

          $.ajax({
            url: "../../api/transaction.php",
            method: "POST",
            data: {
              action: "update transaction",
              ...Object.fromEntries(new URLSearchParams(formData)),
            },
            success: (response) => {
              if (response.success) {
                Swal.fire({
                  title: "Success!",
                  text: "Transaction updated successfully.",
                  icon: "success",
                  showConfirmButton: false,
                  timer: 1500,
                });
                refreshFinishedTransactions();
              } else {
                Swal.fire({
                  title: "Error!",
                  text: response.message || "Failed to update transaction.",
                  icon: "error",
                  showConfirmButton: false,
                  timer: 1500,
                });
              }
            },
            error: (error) => {
              console.error("Error updating transaction:", error);
            },
          });
        }
      );
    } else {
      showError(response.message || "Unable to fetch finished transactions.");
    }
  } catch (error) {
    console.error("Error fetching finished transactions:", error);
  }
}

// Function to restrict input to numbers only
function isNumberKey(evt) {
  const charCode = evt.which ? evt.which : evt.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    return false;
  }
  return true;
}
