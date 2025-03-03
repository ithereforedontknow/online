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
$("#forgot-password").submit(function (e) {
  e.preventDefault();
  const email = $("#forgot-email").val().trim();
  if (!email) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Please enter your email",
    });
    return;
  }
  $.ajax({
    url: "./api/auth.php",
    method: "POST",
    data: {
      forgot_password: true,
      email: email,
    },
    dataType: "json", // Expecting JSON response
  })
    .done(function (response) {
      if (response.success) {
        Swal.fire({
          icon: "success",
          title: "Success",
          text: "Email found! Requesting reset password to admin",
          showConfirmButton: false,
          timer: 1500,
          didClose: () => {
            emailjs.init({
              publicKey: "UUhgj3sBYz18fvFsr",
            });
            emailjs
              .send("service_vl1w89m", "template_s39k907", {
                to_email: response.redirect,
                subject: "Reset Password",
                to_name: "Admin",
                from_name: "Support",
                message:
                  "Requesting reset password to this email address " + email,
              })
              .then(
                function (response) {
                  console.log("SUCCESS!", response);
                },
                function (error) {
                  console.log("FAILED...", error);
                }
              );
          },
        });
        console.log(response);
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
