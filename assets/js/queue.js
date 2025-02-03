const queueManager = {
  apiBase: "../../api/queue.php",
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
  async addQueue(data) {
    return this.request("create", data);
  },
  async updateQueue(data) {
    return this.request("update", data);
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

function viewQueue(transaction) {
  $("#edit-view-transaction-id").val(transaction.transaction_id);
  $("#add-unloading-transaction-id").val(transaction.transaction_id);
  $("#edit-view-transfer-in-line").val(transaction.transfer_in_line);
  $("#edit-view-queue-ordinal").val(transaction.ordinal);
  $("#edit-view-queue-shift").val(transaction.shift);
  $("#edit-view-queue-schedule").val(transaction.schedule);
  $("#edit-view-queue-number").val(transaction.queue_number);
  $("#edit-view-queue-priority").val(transaction.priority);
  $("#viewQueueModal").modal("show");
}
function enterQueue(transaction_id) {
  $("#enterQueueModal").modal("show");
  const now = new Date();
  const formattedDateTime =
    now.getFullYear() +
    "-" +
    (now.getMonth() + 1).toString().padStart(2, "0") +
    "-" +
    now.getDate().toString().padStart(2, "0") +
    "T" +
    now.getHours().toString().padStart(2, "0") +
    ":" +
    now.getMinutes().toString().padStart(2, "0");

  $("#confirm-enter-queue-time").val(formattedDateTime);
  $("#confirm-enter-queue-transaction-id").val(transaction_id);
}
function applyFilters() {
  const ordinalFilter = $("#ordinalFilter").val().toLowerCase();
  const shiftFilter = $("#shiftFilter").val().toLowerCase();
  const scheduleFilter = $("#scheduleFilter").val().toLowerCase();
  const lineFilter = $("#lineFilter").val().toLowerCase();

  $("#queue-list tr").each(function () {
    const row = $(this);
    const ordinal = row.find("td:nth-child(3)").text().toLowerCase();
    const shift = row.find("td:nth-child(4)").text().toLowerCase();
    const schedule = row.find("td:nth-child(5)").text().toLowerCase();
    const line = row.find("td:nth-child(6)").text().toLowerCase();

    // Check if the row matches the selected filters
    const matchesOrdinal = !ordinalFilter || ordinal.includes(ordinalFilter);
    const matchesShift = !shiftFilter || shift.includes(shiftFilter);
    const matchesSchedule =
      !scheduleFilter || schedule.includes(scheduleFilter);
    const matchesLine = !lineFilter || line.includes(lineFilter);

    // Show or hide the row based on filter matches
    if (matchesOrdinal && matchesShift && matchesSchedule && matchesLine) {
      row.show();
    } else {
      row.hide();
    }
  });
}

// Attach event listeners to filters
$("#ordinalFilter, #shiftFilter, #scheduleFilter, #lineFilter").on(
  "change",
  applyFilters
);
async function sendSms(transaction_id, force = false) {
  try {
    const response = await $.ajax({
      url: "../../api/queue.php",
      method: "POST",
      data: {
        action: "send sms",
        transaction_id: transaction_id,
        force: force ? "true" : "false", // Add force parameter
      },
      dataType: "json",
    });

    if (response.message === "SMS already sent" && !force) {
      Swal.fire({
        title: "SMS Already Sent",
        text: "Do you want to re-send the SMS?",
        icon: "info",
        showConfirmButton: true,
        confirmButtonText: "Yes",
        confirmButtonColor: "#1f3a69",
        showCancelButton: true,
        cancelButtonText: "No",
        cancelButtonColor: "#5c636a",
      }).then((result) => {
        if (result.isConfirmed) {
          sendSms(transaction_id, true); // Call sendSms again with force set to true
        }
      });
    } else if (response.success) {
      Swal.fire({
        title: "SMS Sent Successfully",
        icon: "success",
        showConfirmButton: false,
        timer: 1500,
        didClose: () => {
          refreshToEnterList();
          console.log(response);
        },
      });
    } else {
      Swal.fire({
        title: "Error",
        text: response.data || "Failed to send SMS",
        icon: "error",
        showConfirmButton: false,
        timer: 1500,
      });
    }
  } catch (error) {
    console.error("Error sending SMS:", error);
  }
}

// Refresh the queue list and reapply filters
async function refreshQueueList() {
  try {
    const response = await $.ajax({
      url: "../../api/queue.php",
      method: "POST",
      data: { action: "list queue", status: "queue" },
      dataType: "json",
    });
    if (response.success) {
      const queueList = $("#queue-list");
      if ($.fn.DataTable.isDataTable("#queue-table")) {
        $("#queue-table").DataTable().destroy();
      }
      queueList.html(
        response.data.transactions
          .map((transaction) => {
            return `<tr
                      data-priority="${transaction.priority}"
                      onclick='viewQueue(${JSON.stringify(transaction)})'
                      style="cursor: pointer;">
                        <td class="text-center">${transaction.queue_number}</td>
                        <td class="text-center">${transaction.plate_number}</td>
                        <td class="text-center">${transaction.ordinal}</td>
                        <td class="text-center">${transaction.shift}</td>
                        <td class="text-center">${transaction.schedule}</td>
                        <td class="text-center">${
                          transaction.transfer_in_line
                        }</td>
                        <td class="text-center" scope="row"><i class="fa-solid fa-arrow-right"></i></td>
                      </tr>`;
          })
          .join("")
      );
      $("#queue-table").DataTable({
        responsive: true,
        ordering: false,
        searching: false,
        lengthChange: false,
        createdRow: function (row, data, dataIndex) {
          const priority = $(row).data("priority");
          if (priority === 1) {
            $(row)
              .css("background-color", "rgb(27, 54, 103)")
              .css("color", "white");
          } else {
            $(row).css("background-color", "#6c757d");
          }
        },
      });
      // Apply filters after refreshing
      applyFilters();
    } else {
      showError(response.message || "Unable to fetch queue transactions.");
    }
  } catch (error) {
    console.error("Error fetching queue transaction list:", error);
    showError("Failed to retrieve queue transaction list.");
  }
}
async function refreshToEnterList() {
  try {
    const response = await $.ajax({
      url: "../../api/queue.php",
      method: "POST",
      data: {
        action: "list to enter",
        status: "standby",
      },
      dataType: "json",
    });
    console.log(response);
    if (response.success) {
      const toEnterList = $("#to-enter-list");
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#to-enter-table")) {
        $("#to-enter-table").DataTable().destroy();
      }

      // Populate the table with transaction data
      toEnterList.html(
        response.data.transactions
          .map((transaction) => {
            const arrivalTime = new Date(transaction.arrival_time);
            const transactionId = transaction.transaction_id; // Assuming each transaction has a unique ID

            // Create a unique ID for the timer element
            const timerId = `timer-${transactionId}`;
            // Return the row HTML
            return `<tr"
                      style="cursor: pointer;">
                        <td class="text-center">${transaction.plate_number}</td>
                        <td class="text-center" id="${timerId}">
                        Loading...
                        </td>
                        <td class="text-center"><button class="btn btn-primary" onclick="sendSms(${
                          transaction.transaction_id
                        })"><i class="fa-solid fa-sms"></i></button></td>
                        <td class="text-center" scope="row"><button type="button" class="btn ${
                          transaction.status === "standby - sms sent"
                            ? "btn-primary"
                            : "btn-secondary"
                        }" onclick="enterQueue(${
              transaction.transaction_id
            })" ${
              transaction.status === "standby - sms sent" ? "" : "disabled"
            }>Set</button></td>
                      </tr>";`;
          })
          .join("")
      );

      // Reinitialize the DataTable
      $("#to-enter-table").DataTable({
        responsive: true,
        ordering: false,
        searching: false,
        lengthChange: false,
      });

      // Start the real-time counters for each transaction
      response.data.transactions.forEach((transaction) => {
        const arrivalTime = new Date(transaction.arrival_time);
        const timerId = `timer-${transaction.transaction_id}`;

        setInterval(() => {
          const currentTime = new Date();
          const elapsedTime = new Date(currentTime - arrivalTime);
          const hours = Math.floor(elapsedTime / (1000 * 60 * 60));
          const minutes = Math.floor(
            (elapsedTime % (1000 * 60 * 60)) / (1000 * 60)
          );
          const seconds = Math.floor((elapsedTime % (1000 * 60)) / 1000);

          // Update the timer element with the new values
          $(`#${timerId}`).text(
            `${hours} hour(s), ${minutes} minutes, and ${seconds} seconds`
          );
        }, 1000); // Update every second
      });
    } else {
      showError(response.message || "Unable to fetch to enter transactions.");
    }
  } catch (error) {
    console.error("Error fetching to enter transaction list:", error);
  }
}

$("#edit-queue-form").submit(async function (e) {
  e.preventDefault();

  const data = {
    action: "update queue",
    transaction_id: $("#edit-view-transaction-id").val(),
    transfer_in_line: $("#edit-view-transfer-in-line").val(),
    ordinal: $("#edit-view-queue-ordinal").val(),
    shift: $("#edit-view-queue-shift").val(),
    schedule: $("#edit-view-queue-schedule").val(),
    queue_number: $("#edit-view-queue-number").val(),
    priority: $("#edit-view-queue-priority").val(),
  };

  try {
    const response = await $.ajax({
      url: "../../api/queue.php",
      method: "POST",
      data: data,
      dataType: "json",
    });

    if (response.success) {
      $("#viewQueueModal").modal("hide");
      refreshQueueList();
    } else {
      $("#edit-view-queue-number").addClass("is-invalid");
      showError(response.message || "Error updating transaction in queue.");
    }
  } catch (error) {
    console.log(error);
    console.error("Transaction update failed:", error);
  }
});
$("#add-unloading-form").submit(async function (e) {
  e.preventDefault();
  const data = {
    action: "enter to unload",
    transaction_id: $("#add-unloading-transaction-id").val(),
  };
  try {
    const response = await $.ajax({
      url: "../../api/queue.php",
      method: "POST",
      data: data,
      dataType: "json",
    });
    if (response.success) {
      refreshQueueList();
      refreshToEnterList();
      $("#viewQueueModal").modal("hide");
    } else {
      console.error("Transaction unloading failed:", response.message);
    }
  } catch (error) {
    console.error("Transaction unloading failed:", error);
  }
});
$("#confirm-enter-queue-form").submit(async function (e) {
  e.preventDefault();
  const data = {
    action: "time of entry",
    transaction_id: $("#confirm-enter-queue-transaction-id").val(),
    time_of_entry: $("#confirm-enter-queue-time").val(),
  };
  try {
    const response = await $.ajax({
      url: "../../api/queue.php",
      method: "POST",
      data: data,
      dataType: "json",
    });
    if (response.success) {
      refreshToEnterList();
      $("#enterQueueModal").modal("hide");
    } else {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: response.message,
        showConfirmButton: false,
        timer: 1500,
      });
    }
  } catch (error) {
    console.error("Transaction unloading failed:", error);
  }
});
$(document).ready(() => {
  refreshQueueList();
  refreshToEnterList();
});
