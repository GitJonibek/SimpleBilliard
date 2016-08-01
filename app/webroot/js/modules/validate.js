define(function () {

    var validation = function (url) {
        var bvCallbackAvailableEmailTimer = null;
        var bvCallbackAvailableEmailResults = {};
        return function (email, validator, $field) {
            var field = $field.attr('name');

            //notemptyのチェックはbootstrapValidatorの別のルールを使う。ここではスルーする。
            if (email.length == 0) {
                return true;
            }

            // 簡易チェックをして通ったものだけサーバ側で確認する
            var parts = email.split('@');
            if (!(parts.length >= 2 && parts[parts.length - 1].indexOf('.') != -1)) {
                validator.updateMessage(field, "callback", cake.message.validate.invalid_email);
                return false;
            }

            // 既にサーバ側でチェック済の場合
            if (bvCallbackAvailableEmailResults[email] !== undefined) {
                validator.updateMessage(field, "callback", bvCallbackAvailableEmailResults[email]["message"]);
                return bvCallbackAvailableEmailResults[email]["status"] == validator.STATUS_VALID;
            }

            // キー連打考慮して時間差実行
            clearTimeout(bvCallbackAvailableEmailTimer);
            bvCallbackAvailableEmailTimer = setTimeout(function () {
                // 読込み中のメッセージ表示
                validator.updateMessage(field, "callback",
                    '<i class="fa fa-refresh fa-spin mr_8px"></i>' + cake.message.validate.checking_email);
                validator.updateStatus(field, validator.STATUS_INVALID, "callback");

                $.ajax({
                    type: 'GET',
                    url: url,
                    data: {
                        email: email
                    }
                })
                    .done(function (res) {
                        if (res.valid) {
                            bvCallbackAvailableEmailResults[email] = {
                                status: validator.STATUS_VALID,
                                message: " "
                            };
                        }
                        else {
                            bvCallbackAvailableEmailResults[email] = {
                                status: validator.STATUS_INVALID,
                                message: res.message
                            };
                        }
                    })
                    .fail(function () {
                        bvCallbackAvailableEmailResults[email] = {
                            status: validator.STATUS_INVALID,
                            message: cake.message.notice.d
                        };
                    })
                    .always(function () {
                        validator.updateMessage(field, "callback", bvCallbackAvailableEmailResults[email]["message"]);
                        validator.updateStatus(field, bvCallbackAvailableEmailResults[email]["status"], "callback");
                    });
            }, 300);
            return false;
        };
    };
    // bootstrapValidator callback 用
    // 登録可能な email の validation
    var bvCallbackAvailableEmail = (function () {
        return validation(cake.url.validate_email);
    })();
    var bvCallbackAvailableEmailNotVerified = (function () {
        return validation(cake.url.signup_ajax_validate_email);
    })();
    var bvCallbackAvailableEmailCanInvite = (function () {
        return validation(cake.url.team_ajax_validate_email_can_invite);
    })();

    return {
        bvCallbackAvailableEmail: bvCallbackAvailableEmail,
        bvCallbackAvailableEmailNotVerified: bvCallbackAvailableEmailNotVerified,
        bvCallbackAvailableEmailCanInvite: bvCallbackAvailableEmailCanInvite
    };
});

