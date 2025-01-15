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
  Swal.fire({
    title: "Are you sure?",
    text: "Confirm canceling this transaction",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#1f3a69",
    cancelButtonColor: "#5c636a",
    confirmButtonText: "Yes, cancel it!",
  }).then((result) => {
    if (result.isConfirmed) {
      transactionManager
        .cancelTransaction(id)
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
function cancelArrivedTransaction(id) {
  Swal.fire({
    title: "Are you sure?",
    text: "Confirm canceling this transaction",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#1f3a69",
    cancelButtonColor: "#5c636a",
    confirmButtonText: "Yes, cancel it!",
  }).then((result) => {
    if (result.isConfirmed) {
      transactionManager
        .cancelTransaction(id)
        .then(() => {
          refreshArrivedList();
        })
        .catch((error) => {
          console.error("Transaction cancellation failed:", error);
          // Optionally, show an error message to the user
        });
    }
  });
}
function restoreTransaction(id) {
  transactionManager
    .restoreTransaction(id)
    .then(() => {
      refreshCancelledList();
    })
    .catch((error) => {
      console.error("Transaction restoration failed:", error);
      // Optionally, show an error message to the user
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
      confirmButtonText: "OK",
    });
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
            <td class="text-center">${transaction.guia}</td>
            <td class="text-center">${transaction.hauler_name}</td>
            <td class="text-center">${transaction.plate_number}</td>
            <td class="text-center">${transaction.project_name}</td>
            <td class="text-center">${transaction.origin_name}</td>
            <td class="text-center">${transaction.arrival_time}</td>
            <td class="text-center">
              <button type="button" class="btn btn-primary" onclick="queueTransaction(${transaction.transaction_id})">Queue</button>
              <button type="button" class="btn btn-secondary ms-2" onclick="cancelArrivedTransaction(${transaction.transaction_id})">Cancel</button>
              <button type="button" class="btn btn-secondary ms-2" onclick="printTransaction(${transaction.transaction_id})"><i class="fa-solid fa-print"></i></button>
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
        done: 100,
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
            <td class="text-center">${transaction.to_reference}</td>
            <td class="text-center">${transaction.kilos}</td>
            <td class="text-center">
              <form id="insert-transfer-out-scrap-remarks-${
                transaction.transaction_id
              }">
                <input type="hidden" name="transaction_id" value="${
                  transaction.transaction_id
                }">
                <input type="number" class="form-control" name="transfer_out_kilos" required value="${
                  transaction.transfer_out_kilos || ""
                }">
              </form>
            </td>
            <td class="text-center">
              <input type="number" form="insert-transfer-out-scrap-remarks-${
                transaction.transaction_id
              }" class="form-control" name="scrap" required value="${
            transaction.scrap || ""
          }">
            </td>
            <td class="text-center">
              <input type="number" form="insert-transfer-out-scrap-remarks-${
                transaction.transaction_id
              }" class="form-control" name="remarks" required value="${
            transaction.remarks || ""
          }">
            </td>
            <td class="text-center">
              <button type="submit" form="insert-transfer-out-scrap-remarks-${
                transaction.transaction_id
              }" class="btn btn-primary">Save</button>
              <button type="button" class="btn btn-secondary ms-2" onclick="editFinishedtransactions(${JSON.stringify(
                transaction
              )})">Edit</button>

          </tr>
        `
        )
        .join("");

      list.html(rows);
      $("#finished-transactions-table").DataTable({
        responsive: true,
        lengthChange: false,
      });
    } else {
      showError(response.message || "Unable to fetch finished transactions.");
    }
  } catch (error) {
    console.error("Error fetching finished transactions:", error);
  }
}
$("input[name='transfer_out_kilos'], input[name='scrap']").on(
  "input",
  function (e) {
    const val = this.value.replace(/\D/g, "");
    this.value = val;
  }
);
