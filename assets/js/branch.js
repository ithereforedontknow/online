// Handle form submission for add user

function logout_user() {
  $.ajax({
    url: "../../api/auth.php", // Adjust the path if needed
    method: "POST",
    data: { logout: true }, // Ensure the value is a string
    dataType: "json", // Expect a JSON response
    success: function (response) {
      if (response.success) {
        // alert(response.message); // Optional: Show a logout success message
        window.location.href = "../../index.php";
      } else {
        alert("Error: " + response.message); // Handle failure cases
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX error:", status, error); // Log AJAX errors for debugging
      alert("An error occurred while logging out. Please try again.");
    },
  });
}
$("#clear").click(function () {
  $(
    "#to-reference, #guia, #hauler, #plate-number, #driver-name, #helper-name, #no-of-bales, #kilos"
  ).val("");
});
$("#edit-profile-form").submit((event) => {
  event.preventDefault(); // Prevent the default form submission

  // Reset validation feedback
  const form = event.target;
  Array.from(form.elements).forEach((input) => {
    input.classList.remove("is-invalid");
    input.classList.remove("is-valid");
  });

  const newPassword = $("#edit-newPassword").val();
  const confirmPassword = $("#edit-confirmPassword").val();

  // Check if new password and confirm password match
  if (newPassword !== confirmPassword) {
    $("#edit-confirmPassword").addClass("is-invalid");
    $("#edit-newPassword")
      .addClass("is-invalid")
      .next(".invalid-feedback")
      .text("Passwords do not match.")
      .show();
    $("#edit-confirmPassword")
      .next(".invalid-feedback")
      .text("Passwords do not match.")
      .show();
    return; // Exit the function if passwords do not match
  } else if (newPassword === "" || confirmPassword === "") {
    $("#edit-confirmPassword").addClass("is-invalid");
    $("#edit-newPassword")
      .addClass("is-invalid")
      .next(".invalid-feedback")
      .text("Please provide a password.")
      .show();
    $("#edit-confirmPassword")
      .next(".invalid-feedback")
      .text("Please provide a password for confirmation.")
      .show();
    return;
  } else {
    $("#edit-confirmPassword").removeClass("is-invalid").addClass("is-valid");
    $("#edit-newPassword").removeClass("is-invalid").addClass("is-valid");
  }
  Swal.fire({
    title: "Are you sure?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#1c3464",
    cancelButtonColor: "#6c757d",
    cancelButtonText: "No",
    confirmButtonText: "Yes",
  }).then((result) => {
    if (result.isConfirmed) {
      const data = {
        action: "update profile",
        id: $("#userId").val(), // Assuming user ID is needed for the request
        username: $("#edit-username").val(),
        fname: $("#edit-firstname").val(),
        mname: $("#edit-middlename").val(),
        lname: $("#edit-lastname").val(),
        new_password: newPassword,
      };

      $.post("../../api/user.php", data)
        .done((response) => {
          Swal.fire({
            title: "Updated!",
            icon: "success",
            showConfirmButton: false,
            timer: 1500,
            didClose: () => {
              window.location.reload(); // Reload the page to reflect changes
            },
          });
        })
        .fail((error) => {
          // Handle error response
          alert("Error updating profile.");
        });
    }
  });
  // If validation passes, submit the form via AJAX
});
// Enhanced form validation and submission handler
$("#add-branch-transaction").submit(async function (event) {
  event.preventDefault();

  // Improved datalist value getter with type checking
  const getDatalistValue = (input, datalistId) => {
    const value = $(input).val()?.trim();
    if (!value) return null;

    const option = $(`#${datalistId} option`).filter(function () {
      return this.value === value;
    });
    return option.length > 0 ? option.data("id") : null;
  };

  // Form validation
  const validateForm = () => {
    const errors = [];

    // Required field validation
    const requiredFields = {
      "TO Reference": "#add-to-reference",
      GUIA: "#add-guia",
      Hauler: "#add-hauler",
      "Plate Number": "#add-plate-number",
      "Driver Name": "#add-driver-name",
      "Helper Name": "#add-helper-name",
      "No of Bales": "#add-no-of-bales",
      Kilos: "#add-kilos",
    };

    Object.entries(requiredFields).forEach(([label, selector]) => {
      if (!$(selector).val()?.trim()) {
        errors.push(`${label} is required`);
      }
    });

    // Numeric validation
    if (!/^\d+$/.test($("#add-no-of-bales").val())) {
      errors.push("No of Bales must be a valid number");
    }

    if (!/^\d+(\.\d{1,2})?$/.test($("#add-kilos").val())) {
      errors.push("Kilos must be a valid number with up to 2 decimal places");
    }

    // Time validation

    const departureDateTime = $("#add-time-departure").val();

    function validateDateNotInPast(dateTime) {
      if (!dateTime) return false;

      const today = new Date();
      today.setHours(0, 0, 0, 0); // Set to midnight 12 am
      const inputDate = new Date(dateTime);

      // Ensure the input date is not before today
      return inputDate >= today;
    }
    if (!validateDateNotInPast(departureDateTime)) {
      $("#add-time-departure").addClass("is-invalid");
      if (!$("#add-time-departure").siblings(".invalid-feedback").length) {
        $("#add-time-departure").after(
          '<div class="invalid-feedback">Departure date must not be in the past</div>'
        );
      } else {
        $("#add-time-departure")
          .siblings(".invalid-feedback")
          .text("Departure date must not be in the past");
      }
      return;
    } else {
      $("#add-time-departure").removeClass("is-invalid");
    }

    return errors;
  };

  // Validate datalist selections
  const haulerId = getDatalistValue("#add-hauler", "add-haulers");
  const plateNumberId = getDatalistValue(
    "#add-plate-number",
    "add-plate-numbers"
  );
  const driverId = getDatalistValue("#add-driver-name", "add-driver-names");
  const helperId = getDatalistValue("#add-helper-name", "add-helper-names");

  if (!haulerId || !plateNumberId || !driverId || !helperId) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Please ensure all selection fields are chosen from the provided options.",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  // Validate form
  const validationErrors = validateForm();
  if (validationErrors.length > 0) {
    Swal.fire(validationErrors.join("\n"));
    return;
  }

  // Prepare form data
  const formData = {
    action: "branch add transaction",
    "to-reference": $("#add-to-reference").val().trim(),
    guia: $("#add-guia").val().trim(),
    "hauler-id": haulerId,
    "vehicle-id": plateNumberId,
    "driver-id": driverId,
    "helper-id": helperId,
    "project-id": $("#add-project").val(),
    "no-of-bales": $("#add-no-of-bales").val(),
    kilos: $("#add-kilos").val(),
    "origin-id": $("#add-origin_id").val(),
    "time-departure": $("#add-time-departure").val(),
    created_by: $("#add-created_by").val(),
  };

  try {
    const response = await $.ajax({
      url: "../../api/transaction.php",
      method: "POST",
      data: formData,
    });

    if (response.success) {
      Swal.fire({
        icon: "success",
        title: "Transaction submitted successfully!",
        text: "Form has been reset.",
        showConfirmButton: false,
        timer: 1500,
      }).then(() => {
        $("#add-branch-transaction")[0].reset();
      });
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
    console.error("Error submitting transaction:", error);
    alert("A network error occurred. Please try again later.");
  }
});

// Clear form handler
$("#add-clear").click(function () {
  if (confirm("Are you sure you want to clear all fields?")) {
    $("#add-branch-transaction")[0].reset();
    $(".is-invalid").removeClass("is-invalid");
  }
});

// Real-time numeric validation
$("#add-no-of-bales, #add-kilos").on("input", function () {
  const value = this.value;
  const isKilos = this.id === "add-kilos";
  const pattern = isKilos ? /^\d*\.?\d{0,2}$/ : /^\d*$/;

  if (!pattern.test(value)) {
    this.value = value.slice(0, -1);
  }
});

$("#to-reference, #no-of-bales, #kilos").on("input", function (e) {
  let value = e.target.value;
  value = value.replace(/\D/g, "");
  e.target.value = value;
});

$(document).ready(function () {
  getNotificationCount();
  fetchNotifications();
});

// Add event listener to the "Clear Notifications" button
const clearNotificationsButton = document.getElementById(
  "clearNotificationsButton"
);
if (clearNotificationsButton) {
  clearNotificationsButton.addEventListener("click", () => {
    const notificationBadge = document.getElementById("notificationBadge");
    if (notificationBadge) {
      notificationBadge.innerText = "0"; // Set the badge text to 0
      notificationBadge.style.display = "none"; // Optionally hide the badge
    }
  });
}
clearNotificationsButton.addEventListener("click", async () => {
  try {
    const response = await $.ajax({
      url: "../../api/main.php",
      method: "POST",
      data: { action: "clear-notifications" },
      dataType: "json",
    });
    if (response.success) {
      const notificationBadge = document.getElementById("notificationBadge");
      if (notificationBadge) {
        notificationBadge.innerText = "0"; // Set the badge text to 0
        notificationBadge.style.display = "none"; // Optionally hide the badge
      }
    }
  } catch (error) {
    console.error("Failed to clear notifications:", error);
  }
});
async function getNotificationCount() {
  try {
    const response = await $.ajax({
      url: "../../api/main.php",
      method: "POST",
      data: { action: "get-notification-count" },
      dataType: "json",
    });

    // Update the badge with the notification count
    const notificationBadge = document.getElementById("notificationBadge");

    if (response.success && response.data.notification_count > 0) {
      notificationBadge.innerText = response.data.notification_count;
      notificationBadge.style.display = "inline-block"; // Show badge if count > 0
    } else {
      notificationBadge.style.display = "none"; // Hide badge if count is 0
    }
  } catch (error) {
    console.error("Failed to get notification count:", error);
    // Optionally hide the badge on error
    const notificationBadge = document.getElementById("notificationBadge");
    notificationBadge.style.display = "none";
  }
}
// Set timer to auto-update notification count every 10 seconds
let currentOffset = 0;
let currentLimit = 15;
let currentSearchTerm = "";

async function fetchNotifications(limit = 15, offset = 0, searchTerm = "") {
  try {
    const notificationList = document.getElementById("notificationList");
    // Remove existing View More button if present
    const existingViewMoreBtn = document.getElementById("viewMoreBtn");
    if (existingViewMoreBtn) {
      existingViewMoreBtn.remove();
    }
    // Reset list if it's a new search
    if (offset === 0) {
      notificationList.innerHTML = "";
    }
    const response = await $.ajax({
      url: "../../api/main.php",
      method: "POST",
      data: {
        action: "get-notifications",
        limit: limit,
        offset: offset,
        search: searchTerm,
      },
      dataType: "json",
    });
    if (response.success && response.data.notifications.length > 0) {
      // Update current search parameters
      currentOffset = offset;
      currentLimit = limit;
      currentSearchTerm = searchTerm;
      // Render total results for search
      if (offset === 0 && searchTerm) {
        const searchResultsInfo = document.createElement("div");
        searchResultsInfo.className = "alert alert-info mb-3";
        searchResultsInfo.textContent = `Found ${response.data.total} notifications matching "${searchTerm}"`;
        notificationList.appendChild(searchResultsInfo);
      }
      response.data.notifications.forEach((notification) => {
        const notificationItem = document.createElement("div");
        notificationItem.className = `notification-item mb-3 cursor-pointer ${
          notification.is_read ? "read-notification" : "unread-notification"
        }`;
        notificationItem.style.cursor = "pointer";
        notificationItem.setAttribute("data-notification-id", notification.id);
        notificationItem.setAttribute("data-bs-toggle", "modal");
        notificationItem.setAttribute("data-bs-target", "#notificationModal");
        notificationItem.innerHTML = `
          <h5 style="font-size: 16px;">
            <i class="fas fa-bell notification-icon"></i>
            ${notification.details}
          </h5>
          <small class="text-muted" style="font-size: 0.7rem;">
            Transaction ID: ${notification.transaction_id}
          </small>
          <small class="text-muted float-end" style="font-size: 0.7rem;">
            ${new Date(notification.created_at).toLocaleString()}
          </small>
        `;
        // Store the notification data as a data attribute for use when clicked
        notificationItem.addEventListener("click", function () {
          showNotificationDetails(notification);
          markNotificationAsRead(notification.log_id); // Mark as read when clicked
          console.log(notification);
        });
        notificationList.appendChild(notificationItem);
      });
      // Add View More button if more notifications exist
      if (response.data.hasMore) {
        const viewMoreBtn = document.createElement("button");
        viewMoreBtn.id = "viewMoreBtn";
        viewMoreBtn.className = "btn btn-secondary w-100 mt-3";
        viewMoreBtn.textContent = "View More";
        viewMoreBtn.onclick = () =>
          fetchNotifications(
            currentLimit,
            currentOffset + currentLimit,
            currentSearchTerm
          );
        notificationList.appendChild(viewMoreBtn);
      }
    } else if (offset === 0) {
      notificationList.innerHTML = `
        <div class="alert alert-info" role="alert" style="font-size: 0.8rem;">
          <i class="fas fa-info-circle me-2"></i>
          ${
            searchTerm
              ? `No notifications found for "${searchTerm}"`
              : "No notifications at this time"
          }
        </div>
      `;
    }
  } catch (error) {
    console.error("Failed to fetch notifications:", error);
    notificationList.innerHTML = `
      <div class="alert alert-danger" role="alert" style="font-size: 0.8rem;">
        <i class="fas fa-exclamation-triangle me-2"></i> Failed to load notifications. Please try again later.
      </div>
    `;
  }
}
function showNotificationDetails(notification) {
  const modalTitle = document.getElementById("notificationModalLabel");
  const modalBody = document.getElementById("notificationModalBody");

  modalTitle.textContent = "Notification Details";

  // Format the notification date
  const notificationDate = new Date(notification.created_at).toLocaleString();

  // Populate the modal with notification details
  modalBody.innerHTML = `
    <div class="notification-detail">
      <div class="mb-3">
        <strong>Message:</strong>
        <p>${notification.details}</p>
      </div>
      <div class="mb-3">
        <strong>Transaction ID:</strong>
        <p>${notification.transaction_id}</p>
      </div>
      <div class="mb-3">
        <strong>Date:</strong>
        <p>${notificationDate}</p>
      </div>
      ${
        notification.additional_info
          ? `
        <div class="mb-3">
          <strong>Additional Information:</strong>
          <p>${notification.additional_info}</p>
        </div>
      `
          : ""
      }
      ${
        notification.status
          ? `
        <div class="mb-3">
          <strong>Status:</strong>
          <p><span class="badge bg-${getStatusBadgeColor(
            notification.status
          )}">${notification.status}</span></p>
        </div>
      `
          : ""
      }
    </div>
  `;
}

// Helper function to determine badge color based on status
function getStatusBadgeColor(status) {
  switch (status.toLowerCase()) {
    case "success":
    case "completed":
      return "success";
    case "pending":
    case "in progress":
      return "warning";
    case "failed":
    case "error":
      return "danger";
    default:
      return "secondary";
  }
}

// Add this HTML to your page for the modal
// You can place this right before the closing </body> tag
function addNotificationModal() {
  const modalHTML = `
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="notificationModalLabel">Notification Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="notificationModalBody">
            <!-- Notification details will be loaded here -->
          </div>
          
        </div>
      </div>
    </div>
  `;

  // Append modal to body if it doesn't exist
  if (!document.getElementById("notificationModal")) {
    document.body.insertAdjacentHTML("beforeend", modalHTML);

    // Add event listener for Mark as Read button
    document
      .getElementById("markAsReadBtn")
      .addEventListener("click", function () {
        const modal = document.getElementById("notificationModal");
        const notificationId = modal.getAttribute("data-notification-id");
        if (notificationId) {
          markNotificationAsRead(notificationId);
        }
      });
  }
}

// Function to mark notification as read (you would implement the API call here)
function markNotificationAsRead(notificationId) {
  // Implement your API call to mark notification as read
  $.ajax({
    url: "../../api/main.php",
    method: "POST",
    data: {
      action: "mark-notification-read",
      notification_id: notificationId,
    },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        // You could update the UI to show it's been read
        // For example, change the background color or add a "read" indicator
        const notificationElement = document.querySelector(
          `[data-notification-id="${notificationId}"]`
        );
        if (notificationElement) {
          notificationElement.classList.add("notification-read");
          // Optionally, update the notification count
          updateNotificationCount();
        }
        // Close the modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("notificationModal")
        );
        modal.hide();
      } else {
        alert("Could not mark notification as read. Please try again.");
      }
    },
    error: function () {
      alert("Error marking notification as read. Please try again later.");
    },
  });
}

// Function to update notification count (optional)
function updateNotificationCount() {
  // Implement if you have a notification count indicator
  // This is just a placeholder implementation
  $.ajax({
    url: "../../api/main.php",
    method: "POST",
    data: {
      action: "get-unread-notification-count",
    },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        const countElement = document.getElementById("notificationCount");
        if (countElement) {
          countElement.textContent = response.data.count;
          if (response.data.count === 0) {
            countElement.style.display = "none";
          } else {
            countElement.style.display = "inline-block";
          }
        }
      }
    },
  });
}

// Call this function when your page loads
document.addEventListener("DOMContentLoaded", function () {
  addNotificationModal();
  // Fetch initial notifications
  fetchNotifications();
});

// Add search functionality
document
  .getElementById("notificationSearchForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();
    const searchInput = document.getElementById("notificationSearchInput");
    fetchNotifications(15, 0, searchInput.value.trim());
  });
