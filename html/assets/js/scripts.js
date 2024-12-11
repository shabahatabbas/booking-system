$(document).ready(function () {
    // Handle Registration
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'x-function/register.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                $('#alertMessage').removeClass('d-none').addClass('alert-success').text(response);
                $('#registerForm')[0].reset();
            },
            error: function () {
                $('#alertMessage').removeClass('d-none').addClass('alert-danger').text('An error occurred.');
            },
        });
    });

    // Handle Login
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'x-function/login.php',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    window.location.href = response.redirect; // Redirect to dashboard
                } else {
                    $('#alertMessage')
                        .removeClass('d-none')
                        .addClass('alert-danger')
                        .text(response.message);
                }
            },
            error: function () {
                $('#alertMessage')
                    .removeClass('d-none')
                    .addClass('alert-danger')
                    .text('An error occurred.');
            },
        });
    });
});
