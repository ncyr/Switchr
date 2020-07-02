jQuery(document).ready(function($){

    $('.help').click(function(e){
        var description = $(this).attr('description');
        var title = $(this).attr('title');
        $.dialog({
            title: title,
            content: description,
            backgroundDismiss: true,
        });
    });

    $('#email-license').click(function(e){
        e.preventDefault();
        $('#form-email-license').dialog({
            height: 250,
            width: 250,
            modal: true,
            title: 'Email License',
            buttons: {
                Send: function() {
                    $.ajax({
                        url: '/license/emailLicense',
                        type: 'POST',
                        data : {
                            email: $('#email-address').val(),
                            license: $('#license-serial').text(),
                        },
                    });
                    $(this).dialog('close');
                },
                Cancel: function() {
                    $(this).dialog('close');
                }
            }
        });
    });

});
