$(document).ready(function () {
  $("#sidebarToggle").on("click", function () {
    $("#sidebar").toggleClass("hidden");
    $("#content").toggleClass("full-width");
  });

  const currentPath = window.location.pathname.split("/").pop().toLowerCase(); // Get the current page name (case-insensitive)
  const navLinks = document.querySelectorAll(".nav-link");

  navLinks.forEach((link) => {
    const href = link.getAttribute("href").toLowerCase(); // Normalize href for comparison

    // Check if href matches the currentPath
    if (href === currentPath) {
      link.classList.add("active");
      link.setAttribute("aria-current", "page");
    } else {
      link.classList.remove("active");
      link.removeAttribute("aria-current");
    }
  });

  fetchNotifications();
  getNotificationCount();
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
// async function markNotificationAsRead(notificationId) {
//   try {
//     const response = await $.ajax({
//       url: "../../api/main.php",
//       method: "POST",
//       data: {
//         action: "mark-notification-as-read",
//         notification_id: notificationId,
//       },
//       dataType: "json",
//     });
//     if (response.success) {
//       // Update the notification item's style
//       const notificationItem = document.querySelector(
//         `[data-notification-id="${notificationId}"]`
//       );
//       if (notificationItem) {
//         notificationItem.classList.remove("unread-notification");
//         notificationItem.classList.add("read-notification");
//       }
//     }
//   } catch (error) {
//     console.error("Failed to mark notification as read:", error);
//   }
// }
// Function to show notification details in modal
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
