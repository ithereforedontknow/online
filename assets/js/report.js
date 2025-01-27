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

function toggleUserSelect() {
  const signature = $("#signature").val();
  if (signature === "yes") {
    $("#userSelect").show();
  } else {
    $("#userSelect").hide();
  }
}

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
  // Signature logic
  const signature =
    $("#signature").val() === "no"
      ? $("#currentUser").val()
      : $("#user").val() || null;

  if ($("#signature").val() === "yes" && !$("#user").val()) {
    alert("Please select a user for the signature.");
    return;
  }
  console.log(signature);
  const data = {
    action: $("#reportType").val(),
    branch: $("#branch").val(),
    dateFrom: $("#dateFrom").val(),
    dateTo: $("#dateTo").val(),
    signature: signature,
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
  console.log("Date From:", $("#dateFrom").val());
  console.log("Date To:", $("#dateTo").val());
  console.log("Branch:", $("#all-reports-branch").val());
  console.log("Status:", $("#all-reports-status").val());
  e.preventDefault();
  const dateTo = $("#dateTo").val();
  const dateFrom = $("#dateFrom").val();
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
  // Signature logic
  const signature =
    $("#all-reports-signature").val() === "no"
      ? $("#currentUser").val()
      : $("#user").val() || null;

  if ($("#all-reports-signature").val() === "yes" && !$("#user").val()) {
    alert("Please select a user for the signature.");
    return;
  }
  console.log(signature);
  const data = {
    action: "all-reports",
    status: $("#all-reports-status").val(),
    branch: $("#all-reports-branch").val(),
    dateFrom: $("#dateFrom").val(),
    dateTo: $("#dateTo").val(),
    signature: signature,
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
  // Signature logic
  const signature =
    $("#all-reports-signature").val() === "no"
      ? $("#currentUser").val()
      : $("#user").val() || null;

  if ($("#all-reports-signature").val() === "yes" && !$("#user").val()) {
    alert("Please select a user for the signature.");
    return;
  }
  const data = {
    action: $("#logReportType").val(),
    reportFormat: $("#logReportFormat").val(),
    signature: signature,
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
