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
  async cancelTransaction(id) {
    return this.request("cancel", { id });
  },
  async restoreTransaction(id) {
    return this.request("restore", { id });
  },
};
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
  console.log(transaction);
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
  openOffCanvas("#editTransactionOffcanvas");
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
    refreshArrivedList();
    $("#addTransactionOffcanvas").offcanvas("hide");
  } catch (error) {
    console.error("Transaction creation failed:", error);
    // Optionally, show an error message to the user
  }
});
function cancelTransaction(id) {
  transactionManager
    .cancelTransaction(id)
    .then(() => {
      refreshDepartedList();
      refreshCancelledList();
    })
    .catch((error) => {
      console.error("Transaction cancellation failed:", error);
      // Optionally, show an error message to the user
    });
}
function restoreTransaction(id) {
  transactionManager
    .restoreTransaction(id)
    .then(() => {
      refreshCancelledList();
      refreshDepartedList();
    })
    .catch((error) => {
      console.error("Transaction restoration failed:", error);
      // Optionally, show an error message to the user
    });
}
// Event delegation for dynamically created forms
$(document).on("submit", ".arrival-transaction-form", async function (event) {
  event.preventDefault();

  // Gather data from the form
  const transactionId = $(this).find("#arrived-transaction-id").val();
  const arrivalTime = $(this).find("#arrived-arrival-time").val();

  // Validate data
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
    // Send data to the server
    const response = await $.ajax({
      url: "../../api/transaction.php",
      method: "POST",
      data: data,
      dataType: "json",
    });

    // Handle server response
    if (response.success) {
      Swal.fire({
        title: "Updated!",
        text: "Transaction added to arrived successfully.",
        icon: "success",
        showConfirmButton: false,
        timer: 1500,
      });

      // Refresh transaction list
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
      confirmButtonText: "OK",
    });
  }
});

async function refreshDepartedList() {
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
            <td class="text-center">${transaction.guia}</td>
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
              <button type="button" class="btn btn-secondary ms-2" onclick='cancelTransaction(${
                transaction.transaction_id
              })'>Cancel</button>
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
            <td class="text-center">-</td>
            <td class="text-center">
              <button type="button" class="btn btn-primary ms-2" onclick="restoreTransaction(${transaction.transaction_id})">Restore</button>
            </td>
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
            <td class="text-center">${transaction.guia}</td>
            <td class="text-center">${transaction.hauler_name}</td>
            <td class="text-center">${transaction.plate_number}</td>
            <td class="text-center">${transaction.project_name}</td>
            <td class="text-center">${transaction.origin_name}</td>
            <td class="text-center">${transaction.arrival_time}</td>
            <td class="text-center">
              <button type="button" class="btn btn-primary ms-2" onclick="queueTransaction(${transaction.transaction_id})">Queue</button>
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
    const selectedStatus = $("#statusFilter").val(); // Get the selected status

    const response = await $.ajax({
      url: "../../api/transaction.php",
      method: "POST",
      data: { action: "transaction status" },
      dataType: "json",
    });

    console.log("Response:", response); // Log the response

    if (response.success) {
      const statusProgressMap = {
        departed: 17.5,
        arrived: 34,
        queue: 50.5,
        standby: 67,
        ongoing: 83.5,
        done: 100,
      };

      // Filter transactions if a specific status is selected
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
          <div class="col-md-4">
            <div class="card shadow-sm mb-4">
              <div class="card-body">
                <h5 class="card-title fw-bold">Plate Number: ${
                  transaction.plate_number
                }</h5>
                <h5 class="card-title fw-bold">Driver: ${
                  transaction.driver_fname
                } ${transaction.driver_lname}</h5>
                <h5 class="card-title fw-bold">Helper: ${
                  transaction.helper_fname
                } ${transaction.helper_lname}</h5>
                <p class="card-text">Status: ${transaction.status}</p>
                <div class="progress ">
                  <div
                    class="progress-bar progress-bar-striped progress-bar-animated ${
                      progressValue === 100 ? "bg-success" : "bg-primary"
                    }"
                    role="progressbar"
                    style="width: ${progressValue}%"
                    aria-valuenow="${progressValue}"
                    aria-valuemin="0"
                    aria-valuemax="100"
                  >
                    ${progressValue}%
                  </div>
                </div>
              </div>
            </div>
          </div>`;
      });

      // Update the HTML content of the transaction column
      $("#transaction-column").html(cards.join(""));
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
  $("#addQueueOffcanvas").offcanvas("show");
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
      $("#addQueueOffcanvas").offcanvas("hide");
    } else {
      $("#add-queue-number").addClass("is-invalid");
      console.error("Error queueing transaction:", response.message);
    }
  } catch (error) {
    console.error("Transaction queueing failed:", error);
    // Optionally, show an error message to the user
  }
});
// Update document.ready
$(document).ready(function () {
  // Initially hide arrived and cancelled tables completely
  $("#arrived-table_wrapper, #cancelled-table_wrapper").hide();
  $("#arrived-table, #cancelled-table").addClass("d-none");

  // Show only departed table and initialize its DataTable
  $("#departed-table").removeClass("d-none");

  // Initialize DataTables but only make departed active initially
  $("#departed-table").DataTable({
    responsive: true,
    lengthChange: false,
  });

  refreshTransactionStatus();
  // Only load departed list initially
  refreshDepartedList();

  // Initialize other DataTables without loading data
  $("#arrived-table, #cancelled-table").DataTable({
    responsive: true,
    lengthChange: false,
  });

  // Update showTable function to handle data loading
  window.showTable = function (status) {
    // Hide all table wrappers
    $(
      "#departed-table_wrapper, #arrived-table_wrapper, #cancelled-table_wrapper"
    ).hide();

    // Hide all tables
    $(".table").addClass("d-none");

    // Show the selected table and its wrapper
    $(`#${status}-table`).removeClass("d-none");
    $(`#${status}-table_wrapper`).show();

    // Load data based on selected status
    switch (status) {
      case "departed":
        refreshDepartedList();
        break;
      case "arrived":
        refreshArrivedList();
        break;
      case "cancelled":
        refreshCancelledList();
        break;
    }

    // Adjust DataTable columns for proper rendering
    $(`#${status}-table`).DataTable().columns.adjust();

    // Update active state in pagination
    $(".page-item").removeClass("active");
    $(`.page-item[data-table="${status}"]`).addClass("active");
  };
});
