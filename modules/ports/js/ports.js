jQuery(document).ready(function($){
    $('table.datatable').DataTable({
        stateSave: true,
    });
    $('a.switch').click(function(){
        var thisSwitch = $(this);
        var protocol = 'tcp';
        var id = $(this).attr('id');
        var onOff = '';
        if (thisSwitch.hasClass('active')) {
            thisSwitch.html('<button class="btn btn-danger">OFF</button>');
            onOff = 1;
        }
        else {
            onOff = 0;
            thisSwitch.html('<button class="btn btn-success">ON</button>');
        }
        $.ajax({
            url: "/ports/checkId/" + id,
            dataType: 'html',
            success: function(response){
                var data = $.parseJSON(response);
                $.ajax({
                    url: "/ports/switchPort/"+ id,
                    data: "ip_rule=" + encodeURIComponent(data.ip_rule) + "&remote_port=" + data.remote_port + "&protocol=" + protocol,
                    success: function(response){
                        thisSwitch.toggleClass('active');
                    },
                    error: function(output){
                        $.dialog({
                            title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                            content: 'There was a problem!<br><strong>'+output.responseText+'</strong>',
                            type: 'red',
                            closeIcon: true,
                            backgroundDismiss: true,
                            onClose: function(){ location.reload(); }
                        });
                    }
                });

            },
            error: function(output){
                $.dialog({
                    title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                    content: 'There was a problem!<br><strong>'+output.responseText+'</strong>',
                    type: 'red',
                    closeIcon: true,
                    backgroundDismiss: true,
                    onClose: function(){ location.reload(); }
                });
            }
        });
    });

    $('input[type="submit"]').click(function(){
        // $(this).attr('value', "Processing, please wait...");
        $.dialog({
            title: 'Please Wait.',
            content: 'Please wait a moment while the port is added.',
            closeIcon: false,
            backgroundDismiss: false,
            escapeKey: false,
        });
    });

    $('.confirm-delete').click(function(e){
        var id = $(this).attr('id');
        $.confirm({
            title: 'Are you sure?',
            content: 'Are you sure you want to remove this port?',
            buttons: {
                confirm: function () {
                    // $.alert('Please wait a moment while we remove the port from the configuration. The service on the host will now restart.');
                    $.dialog({
                        title: 'Please Wait.',
                        content: 'Please wait a moment while the port is removed.',
                        closeIcon: false,
                        backgroundDismiss: false,
                        escapeKey: false,
                    });
                    $.ajax({
                        url: "/ports/checkId/" + id,
                        dataType: 'html',
                        success: function(response){
                            var data = $.parseJSON(response);
                            var host_id = data.host_id;
                            $.ajax({
                                url: '/hosts/okayToPush',
                                type: 'POST',
                                data: {
                                    host_id: host_id,
                                },
                            }).done(function(response) {
                                if (response == 'false') {
                                    $('.jconfirm').remove();
                                    $.dialog({
                                        title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                        content: 'There was a problem!<br><br><strong>There are unapplied changes. Please wait up to one minute for the service to restart.</strong>',
                                        type: 'red',
                                        closeIcon: true,
                                        backgroundDismiss: true,
                                    });
                                } else {
                                    $.ajax({
                                        url: '/ports/delete/'+id,
                                        success: function(){
                                            location.reload();
                                        },
                                        error: function(output){
                                            $.dialog({
                                                title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                                content: 'There was a problem!<br><strong>'+output.responseText+'</strong>',
                                                type: 'red',
                                                closeIcon: true,
                                                backgroundDismiss: true,
                                                onClose: function(){ location.reload(); }
                                            });
                                        }
                                    });
                                }
                            });
                        },
                        error: function(output){
                            $.dialog({
                                title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                content: 'There was a problem!<br><strong>'+output.responseText+'</strong>',
                                type: 'red',
                                closeIcon: true,
                                backgroundDismiss: true,
                                onClose: function(){ location.reload(); }
                            });
                        },
                    });
                },
                cancel: function () {
                }
            }
        });
    });

    $('.help').click(function(e){
        var description = $(this).attr('description');
        var title = $(this).attr('title');
        $.dialog({
            title: title,
            content: description,
            backgroundDismiss: true,
        });
    });

    $('#addPortForm input[type="submit"]').click(function(event){
        event.preventDefault();
        var server_id =  $('[name="server_id"]').val();
        var host_id   = $('[name="host_id"]').val();
        $.ajax({
            type: 'POST',
            data: {id: server_id},
            url: '/servers/serverIsUp',
            error: function(output){
                $.dialog({
                    title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                    content: 'There was a problem adding the port!<br><strong>'+output.responseText+'</strong>',
                    type: 'red',
                    closeIcon: true,
                    backgroundDismiss: true,
                    onClose: function(){ location.reload(); }
                });
            },
            success: function(){
                $.ajax({
                    url: '/hosts/okayToPush',
                    type: 'POST',
                    data: {
                        host_id: host_id,
                    },
                }).done(function(response) {
                    if (response == 'false') {
                        $('.jconfirm').remove();
                        $.dialog({
                            title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                            content: 'There was a problem!<br><br><strong>There are unapplied changes. Please wait up to one minute for the service to restart.</strong>',
                            type: 'red',
                            closeIcon: true,
                            backgroundDismiss: true,
                        });
                    } else {
                        $('form').submit();
                    }
                });
            }
        });
    });
    $('#addPort').click(function(e){
        e.preventDefault();
        $('#addPortForm').dialog({
                title: 'Create Port Forward',
                //content: 'There was a problem adding the host!<br><br><strong>'+output.responseText+'</strong>',
                type: 'red',
                closeIcon: true,
                backgroundDismiss: true,
                onClose: function(){ window.location.href = window.location.origin + '/ports'; }
        });
    });
});
