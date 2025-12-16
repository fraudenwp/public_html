$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        const username = $('#username').val();
        const password = $('#password').val();
        const remember = $('#remember').prop('checked');

        $.ajax({
            url: 'islemler/login.php',
            type: 'POST',
            data: {
                username: username,
                password: password
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (remember) {
                        localStorage.setItem('username', username);
                    } else {
                        localStorage.removeItem('username');
                    }
                    window.location.href = 'anasayfa';
                } else {
                    $('#message').text(response.message);
                }
            },
            error: function() {
                $('#message').text('Bir hata oluştu. Lütfen tekrar deneyin.');
            }
        });
    });

    // Sayfa yüklendiğinde, eğer daha önce kaydedilmiş bir kullanıcı adı varsa formu doldur
    const savedUsername = localStorage.getItem('username');
    if (savedUsername) {
        $('#username').val(savedUsername);
        $('#remember').prop('checked', true);
    }
});