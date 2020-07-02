jQuery(document).ready(function($){
    $(".changepw").click(function(e){
        e.preventDefault();
        $('.input-error').hide();
        var pw1 = $("[name='password']").val('');
        var pw2 = $("[name='password-confirm']").val('');
        $(".dialog").dialog({
            title: 'Change a Users Password',
            //content: '',
            type: 'red',
            closeIcon: true,
            backgroundDismiss: true,
        });
    });
    $("#changePasswordForm form").submit(function(e){
        e.preventDefault();
        $('.input-error').hide();

        var action = $(this).attr('action');
        var pw1 = $("[name='password']").val();
        var pw2 = $("[name='password-confirm']").val();
        if(pw1 == pw2 && pw1 != '' && pw2 != ''){
            $("[name='submitBtn']").attr('disabled', 'disabled');
            $("[name='submitBtn']").attr('value', 'Please Wait...');
            $.ajax({
                url: action,
                type: 'POST',
                data: $(this).serialize(),
                datatype: 'text',
                success: function(data){
                    $('.input-error p').html(data);
                    $('.input-error').slideDown();
                    $("[name='submitBtn']").attr('value', 'Submit').removeAttr('disabled');
                },
                error: function(data){
                    $('.input-error p').html('There was a problem, please contact support.');
                    $('.input-error').slideDown();
                }
            });
        }
        else{
            $('.input-error p').html('Passwords do not match, or are blank.').attr('color', 'red');
            $('.input-error').slideDown();
        }

    });
});
