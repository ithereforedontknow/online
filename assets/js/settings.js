const settingsManager = {
  apiBase: "../../api/settings.php",
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
        case "Hauler already exists!":
          $("#add-hauler-name")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Hauler already exists! Please choose a different name.":
          $("#edit-hauler-name")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Plate number already exists! Please choose a different plate number.":
          $("#edit-plate-no")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Plate number already exists!":
          $("#add-plate-no")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Driver already exist!":
          $("#add-driver-fname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          $("#add-driver-mname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          $("#add-driver-lname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Phone number already exist!":
          $("#add-driver-phone")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Driver already exist! Please try again.":
          $("#edit-driver-fname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          $("#edit-driver-lname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          $("#edit-driver-mname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Phone number already exist! Please try again.":
          $("#edit-driver-phone")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Helper already exist!":
          $("#add-helper-fname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          $("#add-helper-mname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          $("#add-helper-lname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Helper Phone number already exist!":
          $("#add-helper-phone")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Helper already exist! Please try again.":
          $("#edit-helper-fname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          $("#edit-helper-lname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          $("#edit-helper-mname")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Helper Phone number already exist! Please try again.":
          $("#edit-helper-phone")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Project already exist!":
          $("#add-project-name")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        case "Project already exist! Please try again.":
          $("#edit-project-name")
            .addClass("is-invalid")
            .siblings(".invalid-feedback");
          break;
        default:
          console.error("Error:", error.message);
          showError(error.message || "An unexpected error occurred.");
          throw error;
      }
    }
  },
};
function showError(message) {
  console.error(message);
}
function openModal(modalId) {
  const modalElement = document.querySelector(modalId);
  if (modalElement) {
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
  } else {
    console.error(`Modal with ID ${modalId} not found.`);
  }
}

function editHauler(hauler) {
  $("#edit-hauler-id").val(hauler.hauler_id);
  $("#edit-hauler-name").val(hauler.hauler_name);
  $("#edit-hauler-branch").val(hauler.branch);
  $("#edit-hauler-address").val(hauler.hauler_address);
  openModal("#editHaulerModal");
}
function editVehicle(vehicle) {
  $("#edit-vehicle-id").val(vehicle.vehicle_id);
  $("#edit-plate-no").val(vehicle.plate_number);
  $("#edit-hauler").val(vehicle.hauler_id);
  $("#edit-truck-type").val(vehicle.truck_type);
  openModal("#editVehicleModal");
}
function editDriver(driver) {
  $("#edit-driver-id").val(driver.driver_id);
  $("#edit-hauler-driver").val(driver.hauler_id);
  $("#edit-driver-fname").val(driver.driver_fname);
  $("#edit-driver-mname").val(driver.driver_mname);
  $("#edit-driver-lname").val(driver.driver_lname);
  $("#edit-driver-phone").val(driver.driver_phone);

  openModal("#editDriverModal");
}
function editHelper(helper) {
  $("#edit-helper-id").val(helper.helper_id);
  $("#edit-hauler-helper").val(helper.hauler_id);
  $("#edit-helper-fname").val(helper.helper_fname);
  $("#edit-helper-mname").val(helper.helper_mname);
  $("#edit-helper-lname").val(helper.helper_lname);
  $("#edit-helper-phone").val(helper.helper_phone);
  openModal("#editHelperModal");
}
function editProject(project) {
  $("#edit-project-id").val(project.project_id);
  $("#edit-project-name").val(project.project_name);
  $("#edit-description").val(project.project_description);
  openModal("#editProjectModal");
}
function editOrigin(origin) {
  $("#edit-origin-id").val(origin.origin_id);
  $("#edit-origin-name").val(origin.origin_name);
  $("#edit-origin-code").val(origin.origin_code);
  openModal("#editOriginModal");
}
$("#add-hauler").submit(async function (e) {
  e.preventDefault();

  try {
    const response = await settingsManager.request("create hauler", {
      hauler_name: $("#add-hauler-name").val(),
      branch: $("#add-hauler-branch").val(),
      hauler_address: $("#add-hauler-address").val(),
    });

    if (response.success) {
      refreshHaulersList();
      $("#addHaulerModal").modal("hide");
    }
  } catch (error) {
    console.error("Error creating hauler:", error);
    showError("An error occurred while creating the hauler.");
  }
});

$("#edit-hauler").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("update hauler", {
      hauler_id: $("#edit-hauler-id").val(),
      hauler_name: $("#edit-hauler-name").val(),
      branch: $("#edit-hauler-branch").val(),
      hauler_address: $("#edit-hauler-address").val(),
    });
    if (response.success) {
      refreshHaulersList();
      $("#editHaulerModal").modal("hide");
    } else {
      showError(response.message);
    }
  } catch (error) {
    console.error("Error updating hauler:", error);
    showError("An error occurred while updating the hauler.");
  }
});
async function toggleHaulerStatus(haulerId, status) {
  try {
    await settingsManager.request("toggle hauler status", {
      hauler_id: haulerId,
      status,
    });
    refreshHaulersList();
  } catch (error) {
    console.error("Error toggling hauler status:", error);
    showError("An error occurred while toggling the hauler status.");
  }
}
$("#add-vehicle").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("create vehicle", {
      plate_number: $("#add-plate-no").val(),
      hauler_id: $("#add-hauler").val(),
      truck_type: $("#add-truck-type").val(),
    });
    if (response.success) {
      refreshVehicleList();
      $("#addVehicleModal").modal("hide");
    }
  } catch (error) {
    console.error("Error creating vehicle:", error);
    showError("An error occurred while creating the vehicle.");
  }
});
$("#edit-vehicle").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("update vehicle", {
      vehicle_id: $("#edit-vehicle-id").val(),
      plate_number: $("#edit-plate-no").val(),
      hauler_id: $("#edit-hauler").val(),
      truck_type: $("#edit-truck-type").val(),
    });
    if (response.success) {
      refreshVehicleList();
      $("#editVehicleModal").modal("hide");
    }
  } catch (error) {
    console.error("Error updating vehicle:", error);
    showError("An error occurred while updating the vehicle.");
  }
});
$("#add-driver").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("create driver", {
      hauler_id: $("#add-hauler-driver").val(),
      driver_fname: $("#add-driver-fname").val(),
      driver_mname: $("#add-driver-mname").val(),
      driver_lname: $("#add-driver-lname").val(),
      driver_phone: $("#add-driver-phone").val(),
    });
    if (response.success) {
      refreshDriversList();
      $("#addDriverModal").modal("hide");
    }
  } catch (error) {
    console.error("Error creating driver:", error);
    showError("An error occurred while creating the driver.");
  }
});
$("#edit-driver").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("update driver", {
      driver_id: $("#edit-driver-id").val(),
      hauler_id: $("#edit-hauler-driver").val(),
      driver_fname: $("#edit-driver-fname").val(),
      driver_mname: $("#edit-driver-mname").val(),
      driver_lname: $("#edit-driver-lname").val(),
      driver_phone: $("#edit-driver-phone").val(),
    });
    if (response.success) {
      refreshDriversList();
      $("#editDriverModal").modal("hide");
    }
  } catch (error) {
    console.error("Error updating driver:", error);
    showError("An error occurred while updating the driver.");
  }
});
$("#add-helper").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("create helper", {
      hauler_id: $("#add-hauler-helper").val(),
      helper_fname: $("#add-helper-fname").val(),
      helper_mname: $("#add-helper-mname").val(),
      helper_lname: $("#add-helper-lname").val(),
      helper_phone: $("#add-helper-phone").val(),
    });
    if (response.success) {
      refreshHelpersList();
      $("#addHelperModal").modal("hide");
    }
  } catch (error) {
    console.error("Error creating helper:", error);
    showError("An error occurred while creating the helper.");
  }
});
$("#edit-helper").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("update helper", {
      helper_id: $("#edit-helper-id").val(),
      hauler_id: $("#edit-hauler-helper").val(),
      helper_fname: $("#edit-helper-fname").val(),
      helper_mname: $("#edit-helper-mname").val(),
      helper_lname: $("#edit-helper-lname").val(),
      helper_phone: $("#edit-helper-phone").val(),
    });
    if (response.success) {
      refreshHelpersList();
      $("#editHelperModal").modal("hide");
    }
  } catch (error) {
    console.error("Error updating helper:", error);
    showError("An error occurred while updating the helper.");
  }
});
$("#add-project").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("create project", {
      project_name: $("#add-project-name").val(),
      project_description: $("#add-description").val(),
    });
    if (response.success) {
      refreshProjectsList();
      $("#addProjectModal").modal("hide");
    }
  } catch (error) {
    console.error("Error creating project:", error);
    showError("An error occurred while creating the project.");
  }
});
$("#edit-project").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("update project", {
      project_id: $("#edit-project-id").val(),
      project_name: $("#edit-project-name").val(),
      project_description: $("#edit-description").val(),
    });
    if (response.success) {
      refreshProjectsList();
      $("#editProjectModal").modal("hide");
    }
  } catch (error) {
    console.error("Error updating project:", error);
    showError("An error occurred while updating the project.");
  }
});
$("#add-origin").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("create origin", {
      origin_name: $("#add-origin-name").val(),
      origin_code: $("#add-origin-code").val(),
    });
    if (response.success) {
      refreshOriginList();
      $("#addOriginModal").modal("hide");
    }
  } catch (error) {
    console.error("Error creating origin:", error);
    showError("An error occurred while creating the origin.");
  }
});
$("#edit-origin").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("update origin", {
      origin_id: $("#edit-origin-id").val(),
      origin_name: $("#edit-origin-name").val(),
      origin_code: $("#edit-origin-code").val(),
    });
    if (response.success) {
      refreshOriginList();
      $("#editOriginModal").modal("hide");
    }
  } catch (error) {
    console.error("Error updating origin:", error);
    showError("An error occurred while updating the origin.");
  }
});
$("#edit-demurrage").submit(async function (e) {
  e.preventDefault();
  try {
    const response = await settingsManager.request("update demurrage", {
      demurrage: $("#edit-demurrage-value").val(),
    });
    if (response.success) {
      refreshDemurrage(); // Call the function to refresh the demurrage list
      $("#settingsDemurrageModal").modal("hide"); // Close the modal
    }
  } catch (error) {
    console.error("Error updating demurrage:", error);
    showError("An error occurred while updating the demurrage.");
  }
});
async function refreshHaulersList() {
  try {
    const response = await $.ajax({
      url: "../../api/settings.php",
      method: "POST",
      data: { action: "list haulers" },
      dataType: "json",
    });
    if (response.success) {
      const haulerList = $("#hauler-list");
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#hauler-table")) {
        $("#hauler-table").DataTable().destroy();
      }
      // Populate the table with transaction data
      haulerList.html(
        response.data
          .map((hauler) => {
            return `<tr>
                          <td class="text-center">${hauler.hauler_name}</td>
                          <td class="text-center">${hauler.hauler_address}</td>
                          <td class="text-center">${hauler.origin_name}</td>
                          <td class="text-center">
                            <button class="btn ${
                              hauler.status === 1
                                ? "btn-secondary"
                                : "btn-primary"
                            }" onclick="toggleHaulerStatus(${
              hauler.hauler_id
            }, ${hauler.status === 1 ? "false" : "true"})">
                              ${hauler.status === 1 ? "Deactivate" : "Activate"}
                            </button>
                          </td>
                          <td class="text-center" scope="row"><button class="btn btn-primary" onclick='editHauler(${JSON.stringify(
                            hauler
                          )})'>Edit</button></td>
                        </tr>`;
          })
          .join("")
      );

      // Reinitialize the DataTable with row callback
      $("#hauler-table").DataTable({
        responsive: true,
        ordering: false,
        lengthChange: false,
      });
    } else {
      showError(response.message || "Unable to fetch queue transactions.");
    }
  } catch (error) {
    console.error("Error fetching hauler transaction list:", error);
    showError("Failed to retrieve hauler transaction list.");
  }
}
async function refreshVehicleList() {
  try {
    const response = await $.ajax({
      url: "../../api/settings.php",
      method: "POST",
      data: { action: "list vehicles" },
      dataType: "json",
    });
    if (response.success) {
      const vehicleList = $("#vehicle-list");
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#vehicle-table")) {
        $("#vehicle-table").DataTable().destroy();
      }
      // Populate the table with transaction data
      vehicleList.html(
        response.data
          .map((vehicle) => {
            return `<tr onclick='editVehicle(${JSON.stringify(
              vehicle
            )})' style="cursor: pointer;">
                          <td class="text-center">${vehicle.hauler_name}</td>
                          <td class="text-center">${vehicle.plate_number}</td>
                          <td class="text-center">${vehicle.truck_type}</td>
                          <td class="text-center" scope="row"><i class="fa-solid fa-arrow-right"></i></td>
                        </tr>`;
          })
          .join("")
      );

      // Reinitialize the DataTable with row callback
      $("#vehicle-table").DataTable({
        responsive: true,
        ordering: false,
        lengthChange: false,
      });
    } else {
      showError(response.message);
    }
  } catch (error) {
    console.error("Error fetching vehicle transaction list:", error);
    showError("Failed to retrieve vehicle transaction list.");
  }
}
async function refreshDriversList() {
  try {
    const response = await $.ajax({
      url: "../../api/settings.php",
      method: "POST",
      data: { action: "list drivers" },
      dataType: "json",
    });
    if (response.success) {
      const driverList = $("#driver-list");
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#driver-table")) {
        $("#driver-table").DataTable().destroy();
      }
      // Populate the table with transaction data
      driverList.html(
        response.data
          .map((driver) => {
            return `<tr onclick='editDriver(${JSON.stringify(
              driver
            )})' style="cursor: pointer;">
                          <td class="text-center">${driver.driver_fname} ${
              driver.driver_lname
            }</td>
                          <td class="text-center">${driver.driver_phone}</td>
                          <td class="text-center">${driver.origin_name}</td>
                          <td class="text-center">${driver.hauler_name}</td>
                          <td class="text-center"><i class="fa-solid fa-arrow-right"></i></td>
                          </tr>`;
          })
          .join("")
      );

      // Reinitialize the DataTable with row callback
      $("#driver-table").DataTable({
        responsive: true,
        ordering: false,
        lengthChange: false,
      });
    } else {
      showError(response.message);
    }
  } catch (error) {
    console.error("Error fetching driver transaction list:", error);
    showError("Failed to retrieve driver transaction list.");
  }
}

async function refreshHelpersList() {
  try {
    const response = await $.ajax({
      url: "../../api/settings.php",
      method: "POST",
      data: { action: "list helpers" },
      dataType: "json",
    });
    if (response.success) {
      const helperList = $("#helper-list");
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#helper-table")) {
        $("#helper-table").DataTable().destroy();
      }
      // Populate the table with transaction data
      helperList.html(
        response.data
          .map((helper) => {
            return `<tr onclick='editHelper(${JSON.stringify(
              helper
            )})' style="cursor: pointer;">
                          <td class="text-center">${helper.helper_fname} ${
              helper.helper_lname
            }</td>
                          <td class="text-center">${
                            helper.helper_phone
                          }</td>    
                          <td class="text-center">${helper.origin_name}</td>
                          <td class="text-center">${helper.hauler_name}</td>
                          <td class="text-center"><i class="fa-solid fa-arrow-right"></i></td>
                          </tr>`;
          })
          .join("")
      );

      // Reinitialize the DataTable with row callback
      $("#helper-table").DataTable({
        responsive: true,
        ordering: false,
        lengthChange: false,
      });
    } else {
      showError(response.message);
    }
  } catch (error) {
    console.error("Error fetching helper transaction list:", error);
    showError("Failed to retrieve helper transaction list.");
  }
}
async function refreshProjectsList() {
  try {
    const response = await $.ajax({
      url: "../../api/settings.php",
      method: "POST",
      data: { action: "list projects" },
      dataType: "json",
    });
    if (response.success) {
      const projectList = $("#project-list");
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#project-table")) {
        $("#project-table").DataTable().destroy();
      }
      // Populate the table with transaction data
      projectList.html(
        response.data
          .map((project) => {
            return `<tr onclick='editProject(${JSON.stringify(
              project
            )})' style="cursor: pointer;">
                          <td class="text-center">${project.project_name}</td>
                          <td class="text-center">${
                            project.project_description
                          }</td>    
                        
                          <td class="text-center"><i class="fa-solid fa-arrow-right"></i></td>
                          </tr>`;
          })
          .join("")
      );

      // Reinitialize the DataTable with row callback
      $("#project-table").DataTable({
        responsive: true,
        ordering: false,
        lengthChange: false,
      });
    } else {
      showError(response.message);
    }
  } catch (error) {
    console.error("Error fetching project transaction list:", error);
    showError("Failed to retrieve project transaction list.");
  }
}
async function refreshOriginList() {
  try {
    const response = await $.ajax({
      url: "../../api/settings.php",
      method: "POST",
      data: { action: "list origins" },
      dataType: "json",
    });
    if (response.success) {
      const originList = $("#origin-list");
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#origin-table")) {
        $("#origin-table").DataTable().destroy();
      }
      // Populate the table with transaction data
      originList.html(
        response.data
          .map((origin) => {
            return `<tr onclick='editOrigin(${JSON.stringify(
              origin
            )})' style="cursor: pointer;">
                          <td class="text-center">${origin.origin_name}</td>    
                          <td class="text-center">${origin.origin_code}</td>
                          <td class="text-center"><i class="fa-solid fa-arrow-right"></i></td>
                          </tr>`;
          })
          .join("")
      );

      // Reinitialize the DataTable with row callback
      $("#origin-table").DataTable({
        responsive: true,
        ordering: false,
        lengthChange: false,
      });
    } else {
      showError(response.message);
    }
  } catch (error) {
    console.error("Error fetching origin transaction list:", error);
    showError("Failed to retrieve origin transaction list.");
  }
}
async function refreshDemurrage() {
  try {
    const response = await $.ajax({
      url: "../../api/settings.php",
      method: "POST",
      data: { action: "list demurrage" },
      dataType: "json",
    });
    if (response.success) {
      const demurrageList = $("#demurrage-list");
      // Destroy the existing DataTable instance if it exists
      if ($.fn.DataTable.isDataTable("#demurrage-table")) {
        $("#demurrage-table").DataTable().destroy();
      }
      // Populate the table with transaction data
      demurrageList.html(
        response.data
          .map((demurrage) => {
            return `<tr>
                          <td class="text-center">${demurrage.demurrage}</td>    
                          <td class="text-center">${demurrage.updated_at}</td>
                          </tr>`;
          })
          .join("")
      );

      // Reinitialize the DataTable with row callback
      $("#demurrage-table").DataTable({
        responsive: true,
        ordering: false,
        lengthChange: false,
      });
    } else {
      showError(response.message);
    }
  } catch (error) {
    console.error("Error fetching demurrage transaction list:", error);
    showError("Failed to retrieve demurrage transaction list.");
  }
}
$(document).ready(() => {
  refreshVehicleList();
  refreshHaulersList();
  refreshDriversList();
  refreshHelpersList();
  refreshProjectsList();
  refreshOriginList();
  refreshDemurrage();
});
