$(document).ready(function () {
    $("#changePass").click(function () {
        var firstInput = $("#input1").val();
        var secondInput = $("#input2").val();
        var token = window.location.pathname;
        token = token.replace('/formRecovery/', '');
        if (firstInput === secondInput) {
            $.ajax({
                type: "POST",
                url: "/performRecovery/"+token,
                data: {'newPassword':firstInput},
                success: function (firstInput) {
                    window.location.href = '{{ base_href }}'+'login';
                }
            })
        }
        else alert("Passwords don't match! Please retype them !");
    });
});