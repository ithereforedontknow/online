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
  response.then((response) => {
    if (response.success) {
      refreshUnloadingList();
      $("#editUnloadingOffCanvas").offcanvas("hide");
    }
  });
});
function editUnloading(transaction) {
  $("#unloading-table-id").val(transaction.transaction_id);
  $("#unloading-table-time-entry").val(transaction.time_of_entry);
  $("#unloading-table-unloading-start").val(transaction.unloading_time_start);
  $("#unloading-table-unloading-end").val(transaction.unloading_time_end);
  $("#unloading-table-departure").val(transaction.time_of_departure);
  $("#editUnloadingOffCanvas").offcanvas("show");
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
  <td class="text-center">${transaction.time_of_entry}</td>
  <td class="text-center">
    ${
      transaction.unloading_time_start ||
      `<button type="button" class="btn btn-primary" onclick="setUnloadingTimeStart(${transaction.transaction_id})">Set Time</button>`
    }
  </td>
  <td class="text-center">
    ${
      (transaction.unloading_time_start && transaction.unloading_time_end) ||
      `<button type="button" class="btn btn-primary" ${
        transaction.unloading_time_start ? "" : "disabled "
      }onclick="setUnloadingTimeEnd(${
        transaction.transaction_id
      })">Set Time</button>`
    }
  </td>
  <td class="text-center">
    ${
      (transaction.unloading_time_end && transaction.time_of_departure) ||
      `<button type="button" class="btn btn-primary" ${
        transaction.unloading_time_end ? "" : "disabled "
      }onclick="setTimeOfDeparture(${
        transaction.transaction_id
      })">Set Time</button>`
    }
  </td>
  <td class="text-center">&#8369; ${transaction.demurrage}</td>
  <td class="text-center">
    <button class="btn btn-primary" onclick='editUnloading(${JSON.stringify(
      transaction
    )})'>Edit</button>
  </td>
</tr>

          `
        )
        .join("");

      list.html(rows);
      $("#unloading-table").DataTable({
        responsive: true,
        lengthChange: false,
      });
    } else {
      showError(response.message || "Unable to fetch unloading transactions.");
    }
  } catch (error) {
    console.error("Error fetching unloading transactions:", error);
  }
}
