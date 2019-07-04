$(document).ready(function () {
    function validateEmail($email) {
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        return emailReg.test( $email );
    }
    var tog = 1;
    $("#recoverButton").click(function () {
        var recoverEmail = $("#recoveryEmailID").val();
        if (validateEmail(recoverEmail) && (tog === 1 || tog === 0) && !(recoverEmail === null) && !(recoverEmail === '')) {


            if (tog === 0) $("#recoveryAlert").empty();

           $("#recoveryAlert").append("      <div class=\"alert alert-success\" role=\"alert\">\n" +
               "                            <div class=\"alert-text\">Email recovery was sent to  " + String(recoverEmail) +"</div>\n" +
               "                        </div>");
            tog = 2;

            // make request
            $.ajax({
                type: "POST",
                url: "/recoveryRequest",
                data: {'recoveryEmail':recoverEmail},
                success: setTimeout(function () {
                    window.location.href = "https://www.google.com/search?ei=YBIeXa6NNNGQ8gLYkYaQCQ&q=email+inbox&oq=email+inbo&gs_l=psy-ab.3.0.0l2j0i203l8.13808.18759..19858...6.0..1.148.1065.0j9......0....1..gws-wiz.......0i71j0i67j0i131.wJPD5PkLmZg";
                }, 2400)
                })

        }
        else {
            if (tog !== 0 && tog !== 2)
            {
                $("#recoveryAlert").append("<div class=\"alert alert-danger\" id=\"toBeDeleted\" role=\"alert\">\n" +
                    "                            <div class=\"alert-text\">Invalid email, you shoud type again!</div>\n" +
                    "                        </div>");

                tog = 0;
            }
        }
    });

});


// $(document).ready(function () {
//     $("#changePass").click(function () {
//         var firstInput = $("#input1").val();
//         var secondInput = $("#input2").val();
//         var token = window.location.pathname;
//         token = token.replace('/formRecovery/', '');
//         if (firstInput === secondInput) {
//             $.ajax({
//                 type: "POST",
//                 url: "/performRecovery/"+token,
//                 data: {'newPassword':firstInput},
//                 success: function (firstInput) {
//                     window.location.href = '{{ base_href }}'+'login';
//                 }
//             })
//         }
//         else alert("Passwords don't match! Please retype them !");
//     });
// });