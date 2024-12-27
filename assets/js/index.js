$(document).ready(function () {
  $("#login_form").on("submit", function (e) {
    e.preventDefault();
    const username = $("#username").val().trim();
    const password = $("#password").val();

    if (!username || !password) {
      alert("Please enter both username and password");
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
            alert(response.message);
          }
        } else {
          alert(response.message);
        }
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR.responseText);
        alert("Error: " + textStatus + " - " + errorThrown);
      });
  });
});
