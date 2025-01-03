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
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#queue-table")) {
        $("#queue-table").DataTable().destroy();
      }

      // Populate the table with transaction data
      queueList.html(
        response.data.transactions
          .map((transaction) => {
            return `<tr
                      data-priority="${transaction.priority}"
                      onclick="viewQueue(${transaction.transaction_id})"
                      style="cursor: pointer;">
                        <td class="text-center">${transaction.queue_number}</td>
                        <td class="text-center">${transaction.plate_number}</td>
                        <td class="text-center">${transaction.ordinal}</td>
                        <td class="text-center">${transaction.shift}</td>
                        <td class="text-center">${transaction.schedule}</td>
                        <td class="text-center">${transaction.transfer_in_line}</td>
                        <td class="text-center" scope="row"><i class="fa-solid fa-arrow-right"></i></td>
                      </tr>`;
          })
          .join("")
      );

      // Reinitialize the DataTable with row callback
      $("#queue-table").DataTable({
        responsive: true,
        ordering: false,
        searching: false,
        lengthChange: false,
        createdRow: function (row, data, dataIndex) {
          const priority = $(row).data("priority");
          if (priority === 1) {
            $(row)
              .css("background-color", "rgb(27, 54, 103) ")
              .css("color", "white");
          } else {
            $(row).css("background-color", "#6c757d");
          }
        },
      });
    } else {
      showError(response.message || "Unable to fetch queue transactions.");
    }
  } catch (error) {
    console.error("Error fetching queue transaction list:", error);
    showError("Failed to retrieve queue transaction list.");
  }
}

$(document).ready(() => {
  refreshQueueList();
});
