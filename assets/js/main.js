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

  // var chart;

  // function updateChart(period) {
  //   $.ajax({
  //     url: "./api/fetch/fetch_transaction_data.php",
  //     type: "GET",
  //     data: {
  //       period: period,
  //     },
  //     dataType: "json",
  //     success: function (data) {
  //       var ctx = document.getElementById("transactionChart").getContext("2d");

  //       var labels = data.map(function (item) {
  //         return item.label;
  //       });

  //       var counts = data.map(function (item) {
  //         return item.transaction_count;
  //       });

  //       if (chart) {
  //         chart.destroy();
  //       }

  //       chart = new Chart(ctx, {
  //         type: "line",
  //         data: {
  //           labels: labels,
  //           datasets: [
  //             {
  //               label: "Number of Transactions",
  //               backgroundColor: "#2f364a",
  //               borderColor: "#2f364a",
  //               pointBackgroundColor: "#2f364a",
  //               pointBorderColor: "#fff",
  //               pointHoverBackgroundColor: "#fff",
  //               pointHoverBorderColor: "#2f364a",
  //               data: counts,
  //               tension: 0.4,
  //             },
  //           ],
  //         },
  //         options: {
  //           animations: {
  //             tension: {
  //               duration: 1000,
  //               easing: "linear",
  //               from: 1,
  //               to: 0,
  //               loop: true,
  //             },
  //           },
  //           responsive: true,
  //           plugins: {
  //             title: {
  //               display: true,
  //               text:
  //                 "Transaction Count - " +
  //                 period.charAt(0).toUpperCase() +
  //                 period.slice(1),
  //               font: {
  //                 size: 24,
  //               },
  //             },
  //             legend: {
  //               display: false,
  //             },
  //           },
  //           scales: {
  //             x: {
  //               display: true,
  //               title: {
  //                 display: true,
  //                 text:
  //                   period === "today"
  //                     ? "Hour"
  //                     : period === "month"
  //                     ? "Day"
  //                     : "Month",
  //                 font: {
  //                   size: 18,
  //                 },
  //               },
  //               grid: {
  //                 color: "rgba(0, 0, 0, 0.1)",
  //               },
  //               ticks: {
  //                 font: {
  //                   size: 14,
  //                 },
  //               },
  //             },
  //             y: {
  //               display: true,
  //               title: {
  //                 display: true,
  //                 text: "Number of Transactions",
  //                 font: {
  //                   size: 16,
  //                 },
  //               },
  //               beginAtZero: true,

  //               ticks: {
  //                 font: {
  //                   size: 14,
  //                 },
  //               },
  //             },
  //           },
  //         },
  //       });
  //     },
  //     error: function (xhr, status, error) {
  //       console.error("Error fetching data:", error);
  //     },
  //   });
  // }

  // // Initial chart load
  // updateChart("year");

  // // Add event listener for the select dropdown
  // $("#transactionPeriodSelect").change(function () {
  //   var period = $(this).val();
  //   updateChart(period);
  // });
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
