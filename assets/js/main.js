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
setInterval(getNotificationCount, 5000);
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
        notificationItem.className = "notification-item mb-3";
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
setInterval(fetchNotifications, 5000);

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
