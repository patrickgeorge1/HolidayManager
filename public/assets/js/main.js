var tog = 1;
$(document).on('click','#aiurea', function () {
    if(tog === 1) {
        $(this).attr('src','/images/monkey.jpg');
        tog = 0;
    } else{
        $(this).attr('src','/images/avatar.jpg');
        tog = 1;
    }
});