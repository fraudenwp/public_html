<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="template/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="template/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="template/dist/css/adminlte.min.css?v=3.2.0">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="template/index2.html" class="h1"><b>Yakut</b>Turizm</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Oturumunuzu başlatmak için giriş yapın.</p>
            <form id="loginForm">
                <div class="input-group mb-3">
                    <input type="text" id="username" class="form-control" placeholder="Kullanıcı Adı">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" id="password" class="form-control" placeholder="Şifre">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember">
                                Beni Hatırla
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
                    </div>
                </div>
            </form>
            <div id="message" class="mt-3 text-center text-danger"></div>
        </div>
    </div>
</div>
<script src="template/plugins/jquery/jquery.min.js"></script>
<script src="template/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="template/dist/js/adminlte.min.js?v=3.2.0"></script>
<script src="template/ajax/login.js"></script>
</body>
</html>