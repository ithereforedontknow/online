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
  var signatureSelect = document.getElementById("signature");
  var userSelectDiv = document.getElementById("userSelect");
  if (signatureSelect.value === "yes") {
    userSelectDiv.style.display = "block";
  } else {
    userSelectDiv.style.display = "none";
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
  const signature =
    $("#signature").val() === "no" ? $("#currentUser").val() : $("#user").val();
  console.log(signature);
  const data = {
    action: $("#reportType").val(),
    branch: $("#branch").val(),
    status: $("#status").val(),
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
