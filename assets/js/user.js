const userManager = {
  apiBase: "../../api/user.php",
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
      switch (error.message) {
        case "Username already exists!":
          $("#add-username")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Username already exists! Please choose a different username.":
          $("#edit-username")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "User already exists!":
          $("#add-fname").addClass("is-invalid").siblings(".invalid-feedback");
          $("#add-mname").addClass("is-invalid").siblings(".invalid-feedback");
          $("#add-lname").addClass("is-invalid").siblings(".invalid-feedback");
          break;
        case "User already exists! Please choose a different name.":
          $("#edit-fname").addClass("is-invalid").siblings(".invalid-feedback");
          $("#edit-mname").addClass("is-invalid").siblings(".invalid-feedback");
          $("#edit-lname").addClass("is-invalid").siblings(".invalid-feedback");
          break;
        case "Email already exists!":
          $("#add-email").addClass("is-invalid").siblings(".invalid-feedback");
          break;
        case "Email already exists! Please choose a different email.":
          $("#edit-email").addClass("is-invalid").siblings(".invalid-feedback");
          break;
        default:
          console.error("Error:", error.message);
          showError(error.message || "An unexpected error occurred.");
          throw error;
      }
    }
  },

  async createUser(userData) {
    return this.request("create", userData);
  },

  async updateUser(userData) {
    return this.request("update", userData);
  },

  async toggleUserStatus(userId, activate) {
    return this.request(activate ? "activate" : "deactivate", { id: userId });
  },
};
function showError(message) {
  // Implement error display logic (e.g., alert or a designated error div)
  alert(message);
}

function openEditUserOffcanvas(user) {
  // Populate the form fields with the user data
  document.querySelector("#edit-user-id").value = user.id || "";
  document.querySelector("#edit-username").value = user.username || "";
  document.querySelector("#edit-userlevel").value = user.userlevel || "admin";
  document.querySelector("#edit-fname").value = user.fname || "";
  document.querySelector("#edit-mname").value = user.mname || "";
  document.querySelector("#edit-lname").value = user.lname || "";
  document.querySelector("#edit-password").value = user.password || ""; // Pre-fill only if it's acceptable
  document.querySelector("#edit-email").value = user.email || "";
  // Open the offcanvas
  const editBranchContainer = $("#edit-branch-container");
  editBranchContainer.toggle(user.userlevel === "traffic(branch)");
  document.querySelector("#edit-branch").value = user.branch || "";
  openOffCanvas("#editUserOffcanvas");
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
function closeOffCanvas(offcanvasId) {
  const offcanvasElement = document.querySelector(offcanvasId);
  if (offcanvasElement) {
    const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
    if (offcanvas) {
      offcanvas.hide();
    } else {
      console.error(`Offcanvas with ID ${offcanvasId} is not initialized.`);
    }
  } else {
    console.error(`Offcanvas with ID ${offcanvasId} not found.`);
  }
}
function openDeleteModal(userId) {
  // Implement delete user modal logic
  console.log("Delete user ID:", userId);
}
async function refreshUserList() {
  try {
    const response = await $.ajax({
      url: "../../api/user.php",
      method: "POST",
      data: { action: "list" },
      dataType: "json",
    });

    if (response.success) {
      const userList = $("#user-list");

      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#users-table")) {
        $("#users-table").DataTable().destroy();
      }

      // Populate the table with user data
      userList.html(
        response.users.length > 0
          ? response.users
              .map(
                (user) => `<tr>
                              <td>${user.id}</td>
                              <td>${user.fname} ${user.lname}</td>
                              <td>${user.username}</td>
                              <td>${user.userlevel || "N/A"}</td>
                              <td>
                                <button id='${
                                  user.status === 1
                                    ? `deactivate-btn-${user.id}`
                                    : `activate-btn-${user.id}`
                                }' onclick='${
                  user.status === 1
                    ? `toggleUserStatus(${user.id}, false)`
                    : `toggleUserStatus(${user.id}, true)`
                }' class="btn ${
                  user.status === 1 ? "btn-secondary" : "btn-primary"
                }">
                                  ${
                                    user.status === 1
                                      ? "Deactivate"
                                      : "Activate"
                                  }
                                </button>
                              </td>
                              <td>
                                  <button onclick='openEditUserOffcanvas(${JSON.stringify(
                                    user
                                  )})' class="btn btn-primary">Edit</button>
                              </td>
                          </tr>`
              )
              .join("")
          : `<tr><td colspan="6">No users found</td></tr>`
      );

      // Reinitialize the DataTable
      $("#users-table").DataTable({
        responsive: true,
      });
    } else {
      showError(response.message || "Unable to fetch users.");
    }
  } catch (error) {
    console.error("Error fetching user list:", error);
    showError("Failed to retrieve user list.");
  }
}

async function toggleUserStatus(userId, activate) {
  try {
    const response = await $.ajax({
      url: "../../api/user.php",
      method: "POST",
      data: { action: activate ? "activate" : "deactivate", id: userId },
      dataType: "json",
    });

    if (response.success) {
      refreshUserList();
    } else {
      showError(response.message);
    }
  } catch (error) {
    console.error(`Error toggling user status:`, error);
    showError("Failed to toggle user status");
  }
}
$("#edit-user-form, #add-user-form").submit(async function (event) {
  event.preventDefault();
  const $form = $(this);
  const isEditForm = $form.attr("id") === "edit-user-form";

  // Collect form data
  const userData = {
    username: $form.find('input[name="username"]').val().trim(),
    userlevel: $form.find('select[name="userlevel"]').val(),
    branch: $form.find('select[name="branch"]').val(),
    fname: $form.find('input[name="fname"]').val().trim(),
    mname: $form.find('input[name="mname"]').val().trim(),
    lname: $form.find('input[name="lname"]').val().trim(),
    email: $form.find('input[name="email"]').val().trim(),
    password: $form.find('input[name="password"]').val(),
  };

  // Add ID for edit form
  if (isEditForm) {
    userData.id = $("#edit-user-id").val();
  }
  if (isEditForm && !userData.password) {
    delete userData.password;
  }

  try {
    // Validate form data
    if (!validateUserData(userData, isEditForm ? "update" : "create")) {
      return;
    }

    const action = isEditForm ? "update" : "create";
    const response = await userManager[action + "User"](userData);

    if (response.success) {
      // Refresh user list
      refreshUserList();

      // Close offcanvas
      $(`#${isEditForm ? "editUserOffcanvas" : "addUserOffcanvas"}`).offcanvas(
        "hide"
      );
      $form.find(".is-invalid").removeClass("is-invalid");
      $form.find(".invalid-feedback").hide();

      // Reset form
      $form[0].reset();
    } else {
      // Show error message
      showError(response.message || `Failed to  user`);
    }
  } catch (error) {
    console.error(`Error creating user:`, error);
    showError(`Failed to user`);
  }
});

function validateUserData(userData, action) {
  // Basic validation
  const requiredFields = [
    "username",
    "userlevel",
    "fname",
    "mname",
    "lname",
    "email",
    "password",
  ];

  // Check for empty required fields
  for (const field of requiredFields) {
    if (!userData[field]) {
      showError(
        `${field.charAt(0).toUpperCase() + field.slice(1)} is required`
      );
      return false;
    }
  }

  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(userData.email)) {
    showError("Invalid email format");
    return false;
  }

  // Password strength (optional, adjust as needed)
  if (userData.password.length < 8) {
    $("#add-password").addClass("is-invalid").siblings(".invalid-feedback");
    showError("Password must be at least 8 characters long");
    return false;
  }

  // Specific update validation
  if (action === "update" && !userData.id) {
    showError("User ID is required for update");
    return false;
  }

  return true;
}

// Error display function (you might want to customize this)
function showError(message) {
  // Example using Bootstrap toast or alert
  const errorToast = `
    <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;

  // Append and show toast
  const toastContainer = $("#toast-container");
  if (toastContainer.length === 0) {
    $("body").append(
      '<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>'
    );
  }

  const $toast = $(errorToast).appendTo("#toast-container");
  const toast = new bootstrap.Toast($toast);
  toast.show();
}
$(document).ready(function () {
  $("#users-table").DataTable({
    ordering: false,
  });
  refreshUserList();
});
$("#add-userlevel, #edit-userlevel").on("change", function () {
  const $addBranchContainer = $("#add-branch-container");
  const $editBranchContainer = $("#edit-branch-container");

  const showBranch = $(this).val() === "traffic(branch)";
  $addBranchContainer.toggle(showBranch);
  $editBranchContainer.toggle(showBranch);
});
