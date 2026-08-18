<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بازیابی رمز عبور</title>

    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <link rel="stylesheet" href="styles/forgot.css">

    <script src="js/jquery-1.10.2.min.js"></script>
    <script src="js/sweetalert2.min.js"></script>
</head>

<body>

    <div id="forgot-card" class="step-box">

        <div id="step-1">
            <h3>بازیابی رمز عبور</h3>
            <div>
                <label for="user_type">نقش کاربر:</label>
                <select id="user_type">
                    <option value="admin">مدیر</option>
                    <option value="teacher">معلم</option>
                </select>
            </div>
            <br>
            <div>
                <label for="national_code">کد ملی (نام کاربری):</label>
                <input type="text" id="national_code" placeholder="کد ملی ۱۰ رقمی" maxlength="10">
            </div>
            <br>
            <button type="button" id="btn-check-user">تایید و ادامه</button>
        </div>

        <div id="step-2" class="hidden">
            <h3>تایید شماره تلفن</h3>
            <p>کد تایید به شماره زیر ارسال خواهد شد:</p>
            <p id="masked-phone"
                style="font-weight: bold; letter-spacing: 2px; direction: ltr; display: inline-block; unicode-bidi: embed;">
            </p>

            <br><br>
            <button type="button" id="btn-send-sms">ارسال کد تایید</button>
            <span id="timer-text" class="hidden">امکان ارسال مجدد تا <span id="countdown">120</span> ثانیه دیگر</span>

            <div id="otp-section" class="hidden">
                <p style="margin-top: 15px;">کد تایید ۶ رقمی را وارد کنید:</p>
                <div class="otp-container">
                    <input type="text" class="otp-input" maxlength="1" autofocus>
                    <input type="text" class="otp-input" maxlength="1">
                    <input type="text" class="otp-input" maxlength="1">
                    <input type="text" class="otp-input" maxlength="1">
                    <input type="text" class="otp-input" maxlength="1">
                    <input type="text" class="otp-input" maxlength="1">
                </div>
            </div>
        </div>

        <div id="step-3" class="hidden">
            <h3>تعیین رمز عبور جدید</h3>
            <div>
                <label for="new_password">رمز عبور جدید:</label>
                <input type="password" id="new_password">
            </div>
            <br>
            <button type="button" id="btn-change-password">ثبت رمز جدید</button>
        </div>

    </div>

    <script>
        $(document).ready(function () {
            let globalNationalCode = '';
            let globalUserType = 'admin';
            let countdownInterval;

            $('.otp-input').on('input keyup', function (e) {
                let $this = $(this);

                if (e.type === 'keyup' && e.keyCode === 8) {
                    if ($this.val().length === 0) {
                        $this.prev('.otp-input').focus();
                    }
                    return;
                }

                if ($this.val().length === 1) {
                    $this.next('.otp-input').focus();
                }

                let enteredCode = '';
                $('.otp-input').each(function () {
                    enteredCode += $.trim($(this).val());
                });

                if (enteredCode.length === 6) {
                    autoVerifyOTP(enteredCode);
                }
            });

            function autoVerifyOTP(code) {
                $('.otp-input').prop('disabled', true);

                Swal.fire({
                    title: 'در حال بررسی کد...',
                    text: 'لطفاً چند لحظه شکیبا باشید',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'Forgot_back.php',
                    type: 'POST',
                    data: {
                        action: 'verify_code',
                        code: code
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'کد تایید شد!',
                                text: 'در حال انتقال به مرحله بعدی...',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function () {
                                $('#step-2').fadeOut(400, function () {
                                    $('#step-3').removeClass('hidden').hide().fadeIn(400);
                                });
                            });
                        } else {
                            Swal.fire({
                                title: 'خطا',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'تلاش مجدد'
                            }).then(() => {
                                $('.otp-input').val('').prop('disabled', false);
                                $('.otp-input').first().focus();
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: 'خطای ارتباط',
                            text: 'مشکلی در ارتباط با سرور پیش آمد.',
                            icon: 'error',
                            confirmButtonText: 'تلاش مجدد'
                        }).then(() => {
                            $('.otp-input').prop('disabled', false);
                        });
                    }
                });
            }

            $('#btn-check-user').click(function () {
                let nationalCode = $.trim($('#national_code').val());
                let userType = $('#user_type').val();

                if (nationalCode === '') {
                    Swal.fire('خطا', 'لطفاً کد ملی را وارد کنید', 'error');
                    return;
                }

                $.ajax({
                    url: 'Forgot_back.php',
                    type: 'POST',
                    data: {
                        action: 'check_user',
                        national_code: nationalCode,
                        user_type: userType
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            globalNationalCode = nationalCode;
                            globalUserType = userType;
                            $('#masked-phone').text(response.masked_phone);

                            $('#step-1').fadeOut(400, function () {
                                $('#step-2').removeClass('hidden').hide().fadeIn(400);
                            });
                        } else {
                            Swal.fire('خطا', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('خطا', 'خطا در ارتباط با سرور. لطفاً مجدداً تلاش کنید.', 'error');
                    }
                });
            });

            $('#btn-send-sms').click(function () {
                let $btn = $(this);
                $btn.prop('disabled', true);

                $.ajax({
                    url: 'Forgot_back.php',
                    type: 'POST',
                    data: {
                        action: 'send_sms',
                        national_code: globalNationalCode,
                        user_type: globalUserType
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire('موفق', 'کد تایید ارسال شد', 'success');
                            $('#otp-section').removeClass('hidden').hide().slideDown(400, function () {
                                $('.otp-input').first().focus();
                            });

                            $btn.hide();
                            $('#timer-text').removeClass('hidden');
                            let timeLeft = 120;

                            countdownInterval = setInterval(function () {
                                timeLeft--;
                                $('#countdown').text(timeLeft);
                                if (timeLeft <= 0) {
                                    clearInterval(countdownInterval);
                                    $('#timer-text').addClass('hidden');
                                    $btn.show().prop('disabled', false);
                                    $('#countdown').text(120);
                                }
                            }, 1000);

                        } else {
                            Swal.fire('خطا', response.message, 'error');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function () {
                        Swal.fire('خطا', 'مشکلی در ارسال پیامک پیش آمد.', 'error');
                        $btn.prop('disabled', false);
                    }
                });
            });

            $('#btn-change-password').click(function () {
                let newPassword = $.trim($('#new_password').val());

                if (newPassword === '') {
                    Swal.fire('خطا', 'لطفاً رمز عبور جدید را وارد کنید', 'error');
                    return;
                }

                $.ajax({
                    url: 'Forgot_back.php',
                    type: 'POST',
                    data: {
                        action: 'change_password',
                        new_password: newPassword,
                        national_code: globalNationalCode,
                        user_type: globalUserType
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'موفقیت‌آمیز',
                                text: 'رمز عبور با موفقیت تغییر یافت.',
                                icon: 'success',
                                confirmButtonText: 'ورود به سیستم'
                            }).then(function (result) {
                                if (result.value || result.isConfirmed) {
                                    window.location.href = 'index.php';
                                }
                            });
                        } else {
                            Swal.fire('خطا', response.message, 'error');
                        }
                    }
                });
            });
        });
    </script>

</body>

</html>