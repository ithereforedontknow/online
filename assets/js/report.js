const reportManager = {
  apiBase: "../../api/report.php",
  async request(data = {}) {
    try {
      const response = await $.ajax({
        url: this.apiBase,
        type: "POST",
        data: { ...data },
        dataType: "json",
      });
      console.log("Response:", response); // Debugging the server response
      if (!response.success) {
        throw new Error(response.message || "Unknown server error");
      }
      return response;
    } catch (error) {
      console.error("Full error:", error);
      console.error("Error message:", error.message || "Unknown client error");
      throw error;
    }
  },
};

function reportModal(type) {
  const formId = "reportForm"; // Use a consistent form ID
  const submitButton = $("#generateReport");

  // Set a custom attribute to track the report type
  $(`#${formId}`).data("reportType", type);

  // Reset the form contents
  $(`#${formId}`)[0].reset();

  // Update the submit button to include the type (optional, depending on your use case)
  submitButton.attr("data-report-type", type);
  $("#reportType").val(type);
  // Show the modal
  $("#reportModal").modal("show");
}
function logReports(reportType) {
  $("#logReportType").val(reportType);
  $("#logsReportModal").modal("show");
}
$("#reportForm").submit(function (e) {
  e.preventDefault();

  const dateTo = $("#dateTo").val();
  const dateFrom = $("#dateFrom").val();
  if (!dateTo || !dateFrom) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Please select a valid date range for the report",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }
  if (dateTo < dateFrom) {
    $("#dateTo").addClass("is-invalid");
    return;
  } else {
    $("#dateTo").removeClass("is-invalid");
  }

  if (dateFrom > dateTo) {
    $("#dateFrom").addClass("is-invalid");
    return;
  } else {
    $("#dateFrom").removeClass("is-invalid");
  }

  const data = {
    action: $("#reportType").val(),
    branch: $("#branch").val(),
    dateFrom: $("#dateFrom").val(),
    dateTo: $("#dateTo").val(),
    signature: $("#signature").val(),
    reportFormat: $("#reportFormat").val(),
  };

  // Create an invisible form or link element to trigger the download
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "../../api/report.php"; // Change to the URL that handles the export
  form.target = "_blank";

  // Append the necessary form data to the form
  Object.keys(data).forEach((key) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = key;
    input.value = data[key];
    form.appendChild(input);
  });

  // Append the form to the body, submit it, and remove it
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
});
$("#allReportsForm").submit(function (e) {
  // Debug output
  e.preventDefault();
  const dateTo = $("#dateTo").val();
  const dateFrom = $("#dateFrom").val();
  if (!dateTo || !dateFrom) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Please select a valid date range for the report",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }
  if (dateTo < dateFrom) {
    $("#dateTo").addClass("is-invalid");
    return;
  } else {
    $("#dateTo").removeClass("is-invalid");
  }
  if (dateFrom > dateTo) {
    $("#dateFrom").addClass("is-invalid");
    return;
  } else {
    $("#dateFrom").removeClass("is-invalid");
  }
  const data = {
    action: "all-reports",
    status: $("#all-reports-status").val(),
    branch: $("#all-reports-branch").val(),
    dateFrom: $("#dateFrom").val(),
    dateTo: $("#dateTo").val(),
    signature: $("#all-reports-signature").val(),
    reportFormat: $("#all-reports-reportFormat").val(),
  };

  // Create an invisible form or link element to trigger the download
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "../../api/report.php"; // Change to the URL that handles the export
  form.target = "_blank";

  // Append the necessary form data to the form
  Object.keys(data).forEach((key) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = key;
    input.value = data[key];
    form.appendChild(input);
  });
  // Append the form to the body, submit it, and remove it
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
});
$("#logsReportForm").submit(function (e) {
  e.preventDefault();
  const dateTo = $("#dateTo").val();
  const dateFrom = $("#dateFrom").val();
  if (!dateTo || !dateFrom) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Please select a valid date range for the report",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }
  if (dateTo < dateFrom) {
    $("#dateTo").addClass("is-invalid");
    return;
  } else {
    $("#dateTo").removeClass("is-invalid");
  }
  if (dateFrom > dateTo) {
    $("#dateFrom").addClass("is-invalid");
    return;
  } else {
    $("#dateFrom").removeClass("is-invalid");
  }
  const data = {
    action: $("#logReportType").val(),
    reportFormat: $("#logReportFormat").val(),
    dateFrom: $("#dateFrom").val(),
    dateTo: $("#dateTo").val(),
    signature: $("#logSignature").val(),
  };
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "../../api/report.php"; // Change to the URL that handles the export
  form.target = "_blank";

  // Append the necessary form data to the form
  Object.keys(data).forEach((key) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = key;
    input.value = data[key];
    form.appendChild(input);
  });
  // Append the form to the body, submit it, and remove it
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
});
$("#userLogsReportForm").submit(function (e) {
  e.preventDefault();
  const dateTo = $("#dateTo").val();
  const dateFrom = $("#dateFrom").val();
  if (!dateTo || !dateFrom) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Please select a valid date range for the report",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }
  if (dateTo < dateFrom) {
    $("#dateTo").addClass("is-invalid");
    return;
  } else {
    $("#dateTo").removeClass("is-invalid");
  }
  if (dateFrom > dateTo) {
    $("#dateFrom").addClass("is-invalid");
    return;
  } else {
    $("#dateFrom").removeClass("is-invalid");
  }
  const data = {
    action: "user",
    reportFormat: $("#userlogReportFormat").val(),
    dateFrom: $("#dateFrom").val(),
    dateTo: $("#dateTo").val(),
    user: $("#userLogReportUser").val(),
    signature: $("#userLogSignature").val(),
  };
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "../../api/report.php"; // Change to the URL that handles the export
  form.target = "_blank";

  // Append the necessary form data to the form
  Object.keys(data).forEach((key) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = key;
    input.value = data[key];
    form.appendChild(input);
  });
  // Append the form to the body, submit it, and remove it
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
});
