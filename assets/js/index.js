$(document).ready(function () {
  $("#login_form").on("submit", function (e) {
    e.preventDefault();
    const username = $("#username").val().trim();
    const password = $("#password").val();

    if (!username || !password) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Please enter both username and password",
      });
      return;
    }

    $.ajax({
      url: "./api/auth.php",
      method: "POST",
      data: {
        login: true,
        username: username,
        password: password,
      },
      dataType: "json", // Expecting JSON response
    })
      .done(function (response) {
        if (response.success) {
          if (response.redirect) {
            window.location.href = response.redirect;
          } else {
            Swal.fire({
              icon: "success",
              title: "Success",
              text: response.message,
              showConfirmButton: false,
              timer: 1500,
            });
          }
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.message,
            showConfirmButton: false,
            timer: 1500,
          });
        }
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR.responseText);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error: " + textStatus + " - " + errorThrown,
          showConfirmButton: false,
          timer: 1500,
        });
      });
  });
});
