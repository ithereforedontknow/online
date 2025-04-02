$(document).ready(() => {
  refreshUnloadingList();
});
const unloadingManager = {
  apiBase: "../../api/unloading.php",
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
      throw error;
    }
  },
};
function setUnloadingTimeStart(transaction_id) {
  const response = unloadingManager.request("set unloading time start", {
    transaction_id,
  });
  response.then((response) => {
    if (response.success) {
      refreshUnloadingList();
    }
  });
}
function setUnloadingTimeEnd(transaction_id) {
  const response = unloadingManager.request("set unloading time end", {
    transaction_id,
  });
  response.then((response) => {
    if (response.success) {
      refreshUnloadingList();
    }
  });
}
function setTimeOfDeparture(transaction_id) {
  const response = unloadingManager.request("set time of departure", {
    transaction_id,
  });
  response.then((response) => {
    if (response.success) {
      refreshUnloadingList();
    }
  });
}
function setDone(transaction_id) {
  Swal.fire({
    title: "Are you sure?",
    text: "Confirm marking this transaction as done.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#1f3a69",
    cancelButtonColor: "#5c636a",
    confirmButtonText: "Yes, mark it!",
  }).then((result) => {
    if (result.isConfirmed) {
      const response = unloadingManager.request("set done", {
        transaction_id,
      });
      response.then((response) => {
        if (response.success) {
          Swal.fire({
            title: "Marked!",
            text: "The transaction has been marked as done.",
            icon: "success",
            showConfirmButton: false,
            timer: 1500,
          });
          refreshUnloadingList();
        }
      });
    }
  });
}
$("#edit-unloading-table-form").submit(async function (e) {
  e.preventDefault();
  const transaction_id = $("#unloading-table-id").val();
  const time_of_entry = $("#unloading-table-time-entry").val();
  const unloading_time_start = $("#unloading-table-unloading-start").val();
  const unloading_time_end = $("#unloading-table-unloading-end").val();
  const time_of_departure = $("#unloading-table-departure").val();
  const response = unloadingManager.request("update unloading", {
    transaction_id,
    time_of_entry,
    unloading_time_start,
    unloading_time_end,
    time_of_departure,
  });
  response
    .then((response) => {
      if (response.success) {
        refreshUnloadingList();
        $("#editUnloadingModal").modal("hide");
      }
    })
    .catch((error) => {
      Swal.fire({
        title: "Error",
        text: error,
        icon: "error",
        showConfirmButton: false,
        timer: 1500,
      });
    });
});
function editUnloading(transaction) {
  $("#unloading-table-id").val(transaction.transaction_id);
  $("#unloading-table-time-entry").val(transaction.time_of_entry.slice(0, -3));
  if (
    !transaction.unloading_time_start ||
    !transaction.unloading_time_end ||
    !transaction.time_of_departure
  ) {
    Swal.fire({
      title: "Error",
      text: "Some required data is missing.",
      icon: "error",
      confirmButtonColor: "#1f3a69",
      confirmButtonText: "OK",
    });
    return;
  }
  $("#unloading-table-unloading-start").val(
    transaction.unloading_time_start.slice(0, -3)
  );
  $("#unloading-table-unloading-end").val(
    transaction.unloading_time_end.slice(0, -3)
  );
  $("#unloading-table-departure").val(
    transaction.time_of_departure.slice(0, -3)
  );
  $("#editUnloadingModal").modal("show");
}
async function refreshUnloadingList() {
  try {
    const response = await $.ajax({
      url: "../../api/unloading.php",
      method: "POST",
      data: { action: "list unloading" },
      dataType: "json",
    });

    if (response.success) {
      const list = $("#unloading-list");

      if ($.fn.DataTable.isDataTable("#unloading-table")) {
        $("#unloading-table").DataTable().destroy();
      }

      const rows = response.data.transactions
        .map(
          (transaction) => `
            <tr>
  <td class="text-center">${transaction.to_reference}</td>
  <td class="text-center">${transaction.plate_number}</td>
  <td class="text-center">${new Date(transaction.time_of_entry).toLocaleString(
    "en-US",
    {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    }
  )}</td>
  <td class="text-center">
    ${
      transaction.unloading_time_start
        ? new Date(transaction.unloading_time_start).toLocaleString("en-US", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
          })
        : `<button type="button" class="btn btn-primary" onclick="setUnloadingTimeStart(${transaction.transaction_id})">Set Time</button>`
    }
  </td>
  <td class="text-center">
    ${
      transaction.unloading_time_start && transaction.unloading_time_end
        ? `${new Date(transaction.unloading_time_end).toLocaleString("en-US", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
          })}`
        : `<button type="button" class="btn btn-primary ${
            transaction.unloading_time_start ? "" : "btn-secondary disabled"
          }" onclick="setUnloadingTimeEnd(${
            transaction.transaction_id
          })">Set Time</button>`
    }
  </td>
  <td class="text-center">
    ${
      transaction.unloading_time_end && transaction.time_of_departure
        ? `${new Date(transaction.time_of_departure).toLocaleString("en-US", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
          })}`
        : `<button type="button" class="btn btn-primary ${
            transaction.unloading_time_end ? "" : "btn-secondary disabled"
          }" onclick="setTimeOfDeparture(${
            transaction.transaction_id
          })">Set Time</button>`
    }
  </td>
  
  <td class="text-center">
    <button class="btn btn-primary me-2" onclick='editUnloading(${JSON.stringify(
      transaction
    )})'>Edit</button>
    <button type="button" class="btn btn-primary ${
      transaction.unloading_time_start &&
      transaction.unloading_time_end &&
      transaction.time_of_departure
        ? ""
        : "btn-secondary disabled"
    }" onclick='setDone(${transaction.transaction_id})'>Done</button>
  </td>
</tr>

          `
        )
        .join("");

      list.html(rows);
      $("#unloading-table").DataTable({
        responsive: true,
        lengthChange: false,
        ordering: false,
      });
    } else {
      showError(response.message || "Unable to fetch unloading transactions.");
    }
  } catch (error) {
    console.error("Error fetching unloading transactions:", error);
  }
}
