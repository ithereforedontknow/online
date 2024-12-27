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
$("#status-filter").on("change", async function () {
  var selectedStatus = $(this).val();
  if (selectedStatus !== "cancelled") {
    try {
      await refreshDepartedList();
    } catch (error) {
      console.error("Error refreshing departed transaction list:", error);
      showError("Failed to retrieve departed transaction list.");
    }
  } else {
    try {
      await refreshCancelledList();
    } catch (error) {
      console.error("Error refreshing cancelled transaction list:", error);
      showError("Failed to retrieve cancelled transaction list.");
    }
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
      const departedList = $("#departed-list");
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#departed-table")) {
        $("#departed-table").DataTable().destroy();
      }

      // Populate the table with transaction data
      departedList.html(
        response.data.transactions
          .map(
            (transaction) => `<tr>
                <td class="text-center">${transaction.to_reference}</td>
                <td class="text-center">${transaction.guia}</td>
                <td class="text-center">${transaction.hauler_name}</td>
                <td class="text-center">${transaction.plate_number}</td>
                <td class="text-center">${transaction.project_name}</td>
                <td class="text-center">${transaction.origin_name}</td>
                <td class="text-center">
                  <form id="edit-transaction-form" class="d-flex justify-content-center align-items-center">
                    <input type="hidden" id="edit-transaction-id" name="edit-transaction-id" value="${
                      transaction.transaction_id
                    }" />
                    <input type="datetime-local" class="form-control" id="edit-arrival-time" name="edit-arrival-time" required style="width: auto;">
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
                </tr>`
          )
          .join("")
      );

      // Reinitialize the DataTable
      $("#departed-table").DataTable({
        responsive: true,
      });
    } else {
      showError(response.message || "Unable to fetch departed transactions.");
    }
  } catch (error) {
    console.error("Error fetching departed transaction list:", error);
    showError("Failed to retrieve departed transaction list.");
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
      const cancelledList = $("#departed-list");

      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#departed-table")) {
        $("#departed-table").DataTable().destroy();
      }

      // Populate the table with transaction data
      cancelledList.html(
        response.data.transactions
          .map(
            (transaction) => `<tr>
                <td class="text-center">${transaction.to_reference}</td>
                <td class="text-center">${transaction.guia}</td>
                <td class="text-center">${transaction.hauler_name}</td>
                <td class="text-center">${transaction.plate_number}</td>
                <td class="text-center">${transaction.project_name}</td>
                <td class="text-center">${transaction.origin_name}</td>
                <td class="text-center">${"-"}</td>
                <td class="text-center">
                  <button type="button" class="btn btn-primary ms-2" onclick="restoreTransaction(${
                    transaction.transaction_id
                  })">Restore</button>
                </td>
              </tr>`
          )
          .join("")
      );

      // Reinitialize the DataTable
      $("#departed-table").DataTable({
        responsive: true,
      });
    } else {
      showError(response.message || "Unable to fetch cancelled transactions.");
    }
  } catch (error) {
    console.error("Error fetching cancelled transaction list:", error);
    showError("Failed to retrieve cancelled transaction list.");
  }
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

  // Function to validate datetime
  function validateDateTime(arrivalDateTime, departureDateTime) {
    if (!arrivalDateTime || !departureDateTime) return false;

    const arrival = new Date(arrivalDateTime);
    const departure = new Date(departureDateTime);

    // Ensure arrival time is after departure time
    return arrival > departure;
  }

  // Extracting IDs from the datalist inputs
  const hauler_id = getDatalistValue("#hauler", "haulers");
  const vehicle_id = getDatalistValue("#plate-number", "plate-numbers");
  const driver_id = getDatalistValue("#driver-name", "driver-names");
  const helper_id = getDatalistValue("#helper-name", "helper-names");

  // DateTime validation
  const arrivalDateTime = $("#arrival-time").val();
  const departureDateTime = $("#time-departure").val();

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
    refreshDepartedList();
    refreshCancelledList();
    $("#add-transaction").trigger("reset");
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
    })
    .catch((error) => {
      console.error("Transaction restoration failed:", error);
      // Optionally, show an error message to the user
    });
}
$(document).ready(function () {
  refreshDepartedList().then(() => refreshCancelledList());
});