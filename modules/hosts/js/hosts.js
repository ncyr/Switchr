jQuery(document).ready(function($){
    $('.confirm-settlebatch').click(function(e){
        e.preventDefault();
        var id = $(this).attr('id');
        var link = $(this).attr('href');
        $.confirm({
            title: 'Confirm Settle Batch',
            content: "Please make sure your employees have already entered their tips. Are you sure you want to settle the batch?<br> Last settlement was: <span id='settled'>Please Wait...</span><script>$('#settled').load('/reports/getLastSettled/"+ id +"')</script>",
            buttons: {
                agree: function () {
                    window.location.href = link;
                },
                cancel: function () {
                }
            }
        });
    });
    $('.table').on('draw.dt', function(){
        $('.confirm-delete').click(function(e){
            // TODO: The id attribute should not be abused like it is here.
            // If HTML5 is being used, then change the views and JS to use
            // the attribute 'data-host' from the HTML5 data-* attribute spec.
            var id = $(this).attr('id');
            $.confirm({
                title: 'Just making sure...',
                content: 'Are you sure you want to remove this host?<br><b>All associated data will be removed, and the host will be disconnected</b>.<br><br><i>This action cannot be un-done.</i>',
                buttons: {
                    confirm: function () {
                        $.dialog({
                            title: 'Please Wait',
                            content: 'Please wait a moment while we remove the host.',
                            closeIcon: false,
                            backgroundDismiss: false,
                            escapeKey: false,
                        });
                        $.ajax({
                            url: '/backups/getS3',
                            type: 'POST',
                            data: {
                                host_id: id,
                            },
                        }).done(function(backup_id) {
                            if (backup_id != 'false') {
                                $('.jconfirm').remove();
                                $.confirm({
                                    title: 'Delete S3 bucket?',
                                    content: 'Do you also want to delete the S3 bucket associated with this host?',
                                    buttons: {
                                        Yes: function () {
                                            $.dialog({
                                                title: 'Please Wait.',
                                                content: 'Please wait a moment while the host is removed.',
                                                closeIcon: false,
                                                backgroundDismiss: false,
                                                escapeKey: false,
                                            });
                                            $.ajax({
                                                url: '/backups/delete/'+backup_id+'/false',
                                                success: function(){
                                                    $.ajax({
                                                        url: '/hosts/okayToPush',
                                                        type: 'POST',
                                                        data: {
                                                            host_id:id,
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
                                                            // Delete host.
                                                            $.ajax({
                                                                url: '/hosts/delete/'+id,
                                                                success: function(){
                                                                    location.reload();
                                                                },
                                                                error: function(output){
                                                                    $('.jconfirm').remove();
                                                                    $.dialog({
                                                                        title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                                                        content: 'There was a problem removing the host!<br><br><strong>'+output.responseText+'</strong>',
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
                                                    $('.jconfirm').remove();
                                                    $.dialog({
                                                        title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                                        content: 'There was a problem removing the S3 bucket!<br><br><strong>'+output.responseText+'</strong>',
                                                        type: 'red',
                                                        closeIcon: true,
                                                        backgroundDismiss: true,
                                                        onClose: function(){ location.reload(); }
                                                    });
                                                }
                                            });
                                        },
                                        No: function () {
                                            $.dialog({
                                                title: 'Please Wait.',
                                                content: 'Please wait a moment while the host is removed.',
                                                closeIcon: false,
                                                backgroundDismiss: false,
                                                escapeKey: false,
                                            });
                                            $.ajax({
                                                url: '/hosts/okayToPush',
                                                type: 'POST',
                                                data: {
                                                    host_id:id,
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
                                                    // Delete host.
                                                    $.ajax({
                                                        url: '/hosts/delete/'+id,
                                                        success: function(){
                                                            location.reload();
                                                        },
                                                        error: function(output){
                                                            $('.jconfirm').remove();
                                                            $.dialog({
                                                                title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                                                content: 'There was a problem removing the host!<br><br><strong>'+output.responseText+'</strong>',
                                                                type: 'red',
                                                                closeIcon: true,
                                                                backgroundDismiss: true,
                                                                onClose: function(){ location.reload(); }
                                                            });
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    },
                                });
                            } else {
                                $.ajax({
                                    url: '/hosts/okayToPush',
                                    type: 'POST',
                                    data: {
                                        host_id:id,
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
                                        // Delete host.
                                        $.ajax({
                                            url: '/hosts/delete/'+id,
                                            success: function(){
                                                location.reload();
                                            },
                                            error: function(output){
                                                $('.jconfirm').remove();
                                                $.dialog({
                                                    title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                                    content: 'There was a problem removing the host!<br><br><strong>'+output.responseText+'</strong>',
                                                    type: 'red',
                                                    closeIcon: true,
                                                    backgroundDismiss: true,
                                                    onClose: function(){ location.reload(); }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    },
                    cancel: function () {
                    }
                }
            });
        });

        $('.confirm-push-host').click(function(e){
            var id = $(this).attr('id');
            $.confirm({
                title: 'Confirm Push Config',
                content: 'If you think new updates to your configuration are not present at your host, try this feature. This can happen if your host loses signal while attempting to send configuration updates to the host. Pushing the configuration requires a resest of the host connection, but it will reconnect.',
                buttons: {
                    confirm: function () {
                        // $.alert('Please wait a moment while we reset the hosts connection to the server.');
                        $.dialog({
                            title: 'Please Wait.',
                            content: 'Please wait a moment while the configuration is pushed.',
                            closeIcon: false,
                            backgroundDismiss: false,
                            escapeKey: false,
                        });
                        $.ajax({
                            url: '/hosts/okayToPush',
                            type: 'POST',
                            data: {
                                host_id:id,
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
                                    url: '/hosts/pushConfig/'+id,
                                    success: function(/*data*/){
                                        $('.jconfirm').remove();
                                        $.dialog({
                                            title: 'Okay',
                                            content: 'We pushed the configuration over. Please wait 60 seconds before attempting to change anything. The host service is restarting.',
                                            closeIcon: true,
                                            backgroundDismiss: true,
                                            close: function() {
                                                location.reload();
                                            }
                                        });
                                    },
                                    error: function(output){
                                        $.dialog({
                                            title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                            content: 'There was a problem pushing to the host!<br><br><strong>'+output.responseText+'</strong>',
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
                    cancel: function () {
                    }
                }
            });
        });

        $('.confirm-reset-host').click(function(e){
            var id = $(this).attr('id');
            $.confirm({
                title: 'Confirm Reset',
                content: 'Sometimes the application can get stuck from a timed out, or disconnected session. This function resets the connection between your host, and our server. It does not restart the host machine. Are you sure you want to reset this host?',
                buttons: {
                    confirm: function () {
                        // $.alert('Please wait a moment while we reset the hosts connection to the server.');
                        $.dialog({
                            title: 'Please Wait',
                            content: 'Please wait a moment while we reset the host\'s connection to the server.',
                            closeIcon: false,
                            backgroundDismiss: false,
                            escapeKey: false,
                        });
                        $.ajax({
                            url: '/hosts/okayToPush',
                            type: 'POST',
                            data: {
                                host_id:id,
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
                                    url: '/hosts/resetConnection/'+id,
                                    success: function(/*data*/){
                                        //console.log(data);
                                        location.reload();
                                    },
                                    error: function(output){
                                        $('.jconfirm').remove();
                                        $.dialog({
                                            title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                            content: 'There was a problem resetting the host!<br><br><strong>'+output.responseText+'</strong>',
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
                    cancel: function () {
                    }
                }
            });
        });

        $('.confirm-beta').click(function(e){
            e.preventDefault();
            var id = $(this).attr('id');
            var link = $(this).attr('href');
            $.confirm({
                title: 'We Are Not Responsible',
                content: 'Beta features are still in development even though they have been tested to work. By agreeing to this message, you agree to use this feature at your own risk, and do not hold us responsible for any lost data, or damages.',
                buttons: {
                    agree: function () {
                        window.location.href = link;
                    },
                    cancel: function () {
                    }
                }
            });
        });

    }); // End of: $('.table').on('draw.dt', function(){

    $('table.datatable').DataTable({
        stateSave: true,
    });

    $('.confirm-restart-host').click(function(e){
        if ($(this.children['0']).hasClass('status-on')) {
            var id = $(this.children['0']).attr('id');
            $.confirm({
                title: 'Confirm Restart',
                content: 'This will restart the host machine. Are you sure you want to reset this host?',
                buttons: {
                    confirm: function () {
                        // $.alert('Please wait a moment while we restart the host.');
                        $.dialog({
                            title: 'Please Wait',
                            content: 'Please wait a moment while we restart the host.',
                            closeIcon: false,
                            backgroundDismiss: false,
                            escapeKey: false,
                        });
                        $.ajax({
                            url: '/hosts/okayToPush',
                            type: 'POST',
                            data: {
                                host_id:id,
                            },
                        }).done(function(response) {
                            // console.log(response);
                            // return;
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
                                    url: '/hosts/restartHost/'+id,
                                    success: function(/*data*/){
                                        //console.log(data);
                                        location.reload();
                                    },
                                    error: function(output){
                                        $('.jconfirm').remove();
                                        $.dialog({
                                            title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                            content: 'There was a problem resetting the host!<br><br><strong>'+output.responseText+'</strong>',
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
                    cancel: function () {
                    }
                }
            });
        }
    });
    //license popup ajax request
    //$('a.license').click(function(e){
        //e.preventDefault();
        //var id = $(this).attr('id');
        //$.ajax({
            //url: '/license/index/'+id,
            //success: function(data){
                //$('#dialog p').html(data);
                //$('.dropdown-toggle').dropdown();
            //}
            //});
       // });
    $('.addPort').click(function(e){
        e.preventDefault();
        var id = $(this).attr('id');
        $.ajax({
            url: '/ports/create/'+id,
            success: function(data){
                $('#dialog p').html(data);
                $('.dropdown-toggle').dropdown();
            }
        });

        $('#dialog').dialog({
            maxWidth: 500,
            maxHeight: 200,
            width: 350,
            height: 350,
            modal: true,
        });
    });

    $('input[type="submit"]').click(function(){
        // $(this).attr('value', "Processing, please wait...");
        $.dialog({
            title: 'Please Wait.',
            content: 'Please wait a moment.',
            closeIcon: false,
            backgroundDismiss: false,
            escapeKey: false,
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

    $('.host-create input[type="submit"]').click(function(event){
        event.preventDefault();
        var server_id = $("#server").val();
        jQuery.ajax({
            url: '/servers/serverIsUp',
            type: 'POST',
            data: {id: server_id},
            beforeSend: function() {
                $.dialog({
                    title: 'Please Wait.',
                    content: 'Please wait a moment while the host is added.',
                    closeIcon: false,
                    backgroundDismiss: false,
                    escapeKey: false,
                });
            },
            error: function(output){
                $('.jconfirm').remove();
                $.dialog({
                    title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                    content: 'There was a problem adding the host!<br><br><strong>'+output.responseText+'</strong>',
                    type: 'red',
                    closeIcon: true,
                    backgroundDismiss: true,
                    onClose: function(){ location.reload(); }
                });
            },
            success: function(){
                // Make sure user has hosts available to them.
                jQuery.ajax({
                    url: '/hosts/ajaxCreate',
                    type: 'POST',
                    success: function(output){
                        // console.log(output);
                        // If user has more hosts available then let them create the host.
                        if (output === 'true') {
                            jQuery.ajax({
                                url: $('form').attr('action'),
                                type: 'POST',
                                data : $('form').serialize(),
                                success: function(){
                                    window.location.href = window.location.origin + '/hosts' ;
                                },
                                error: function(output){
                                    $('.jconfirm').remove();
                                    $.dialog({
                                        title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                        content: 'There was a problem adding the host!<br><br><strong>'+output.responseText+'</strong>',
                                        type: 'red',
                                        closeIcon: true,
                                        backgroundDismiss: true,
                                        onClose: function(){ window.location.href = window.location.origin + '/hosts'; }
                                    });
                                }
                            });
                        // Redirect to edit or create a plan.
                        } else {
                            $('.jconfirm').remove();
                            $('.ui-dialog').remove();
                            // Inform user that they need to upgrade their plan, then redirect if they want to.
                            $.confirm({
                                type: 'red',
                                title: '<span style="vertical-align:bottom;">404: <span class="glyphicon glyphicon-usd"></span> Not Found</span>',
                                content: 'You need to upgrade your plan before you can add more hosts. <strong>Edit your plan?</strong>',
                                buttons: {
                                    // Redirect.
                                    Yes: function () {
                                        window.location.href = window.location.origin + output;
                                    },
                                    // Just close the dialog box.
                                    // BUG: "No" results in the host being added anyway if the form is submitted again.
                                    No: function () {
                                        location.reload();
                                        // $('#addHostForm').dialog('close');
                                        // $('.jconfirm').remove();
                                        // return false;
                                    }
                                }
                            });
                        }
                    },
                    error: function(output){
                        $('.jconfirm').remove();
                        $.dialog({
                            title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                            content: 'There was a problem adding the host!<br><br><strong>'+output.responseText+'</strong>',
                            type: 'red',
                            closeIcon: true,
                            backgroundDismiss: true,
                            onClose: function(){ window.location.href = window.location.origin + '/hosts'; }
                        });
                    }
                });
                return false;
            }
        });
    });

    $('.confirm-waitingtogrind').click(function(e){
        e.preventDefault();
        var host_id = $(this).attr('id');
        $.confirm({
            title: 'Please Pick a Date',
            content: 'Input the date of the error <input id="date" type="date" name="date">',
            buttons: {
                fix: function () {
                    var date = $('#date').val();
                    $.ajax({
                        url: 'reports/fixWaitingGrind/' + host_id + '/' + date.replace(/\D/g,''),
                        type: 'GET',
                        success: function(){
                            window.location.href = window.location.origin + '/hosts' ;
                        },
                        error: function(output){
                            $('.jconfirm').remove();
                            $.dialog({
                                title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                content: 'There was a problem trying to fix the grind issue!<br><br><strong>'+output.responseText+'</strong>',
                                type: 'red',
                                closeIcon: true,
                                backgroundDismiss: true,
                                onClose: function(){ window.location.href = window.location.origin + '/hosts'; }
                            });
                        }
                    });
                },
                cancel: function () {
                }
            }
        });
    });

    /**
     * Pop up the creation form for VNC or RDP and process it.
     */
    $('.remote-create').click(function(event){
        event.preventDefault();
        // 'vnc' or 'rdp'
        var type      = $(this).data('remote-type');
        var host_id   = $(this).data('host-id');
        var host_name = $(this).data('host-name');

        $.confirm({
            title: 'We Are Not Responsible',
            content: 'Beta features are still in development even though they have been tested to work. By agreeing to this message, you agree to use this feature at your own risk, and do not hold us responsible for any lost data, or damages.',
            buttons: {
                Agree: function() {
                    if (type == 'vnc') {
                        $('#create-vnc').dialog({
                            height: 380,
                            width: 300,
                            modal: true,
                            title: 'Create VNC Connection',
                            buttons: {
                                Save: function() {
                                    // Check for already-installed TightVNC
                                    $.ajax({
                                        url: '/hosts/vncExists',
                                        type: 'POST',
                                        data : {
                                            host_id: host_id,
                                        },
                                        beforeSend: function() {
                                            // Close form and activate "Please Wait" dialog.
                                            $('#create-vnc').dialog('close');
                                            $.dialog({
                                                title: 'Please Wait.',
                                                content: 'Please wait a moment while the connection is created.',
                                                closeIcon: false,
                                                backgroundDismiss: false,
                                                escapeKey: false,
                                            });
                                        }
                                    }).done(function(response) {
                                        // If TightVNC is already installed.
                                        if (response == 'true') {
                                            $('.jconfirm').remove();
                                            $.confirm({
                                                title: 'VNC Already Installed',
                                                content: 'Remove the current VNC installation and install the new one?',
                                                // closeIcon: false,
                                                // backgroundDismiss: false,
                                                // escapeKey: false,
                                                // modal: true,
                                                buttons: {
                                                    // Remove installed TightVNC and install the new one.
                                                    Remove: function() {
                                                        // Remove installed TightVNC and install the new one.
                                                        $.ajax({
                                                            url: '/hosts/vncRemoveExists',
                                                            type: 'POST',
                                                            data : {
                                                                host_id: host_id,
                                                            },
                                                            beforeSend: function() {
                                                                // Close form and activate "Please Wait" dialog.
                                                                $('#create-vnc').dialog('close');
                                                                $.dialog({
                                                                    title: 'Please Wait.',
                                                                    content: 'Please wait a moment while the connection is created.',
                                                                    closeIcon: false,
                                                                    backgroundDismiss: false,
                                                                    escapeKey: false,
                                                                });
                                                            }
                                                        }).done(function(response) {
                                                            // Create the VNC connection.
                                                            $.ajax({
                                                                url: '/hosts/createVnc',
                                                                type: 'POST',
                                                                data : {
                                                                    host_name: host_name,
                                                                    host_id: host_id,
                                                                    vnc_port: $('#vnc-port').val(),
                                                                    vnc_password: $('#vnc-password').val(),
                                                                    push_vnc: $('#push-vnc').prop('checked'),
                                                                },
                                                            }).done(function(response) {
                                                                // Refresh the page so that new values are added to page,
                                                                // allowing VNC connection.
                                                                window.location = window.location.origin + '/hosts';
                                                            });
                                                        });
                                                    },
                                                    Cancel: function() {
                                                        $(this).confirm('close');
                                                    }
                                                }
                                            });
                                        // TightVNC is not installed, so continue with installation.
                                        } else {
                                            // Create the VNC connection.
                                            $.ajax({
                                                url: '/hosts/createVnc',
                                                type: 'POST',
                                                data : {
                                                    host_name: host_name,
                                                    host_id: host_id,
                                                    vnc_port: $('#vnc-port').val(),
                                                    vnc_password: $('#vnc-password').val(),
                                                    push_vnc: $('#push-vnc').prop('checked'),
                                                },
                                                beforeSend: function() {
                                                    // Close form and activate "Please Wait" dialog.
                                                    $('#create-vnc').dialog('close');
                                                    $.dialog({
                                                        title: 'Please Wait.',
                                                        content: 'Please wait a moment while the connection is created.',
                                                        closeIcon: false,
                                                        backgroundDismiss: false,
                                                        escapeKey: false,
                                                    });
                                                }
                                            }).done(function(response) {
                                                // Refresh the page so that new values are added to page,
                                                // allowing VNC connection.
                                                window.location = window.location.origin + '/hosts';
                                            });
                                        }
                                    });
                                }
                            }
                        });
                    } else if (type == 'rdp') {
                        $('#create-rdp').dialog({
                            height: 300,
                            width: 260,
                            modal: true,
                            buttons: {
                                Save: function() {
                                    $.ajax({
                                        url: '/hosts/createRdp',
                                        type: 'POST',
                                        data : {
                                            host_name: host_name,
                                            host_id: host_id,
                                            rdp_port: $('#rdp-port').val(),
                                            rdp_username: $('#rdp-windows-username').val(),
                                            rdp_password: $('#rdp-windows-password').val(),
                                        },
                                        beforeSend: function() {
                                            // Close form and activate "Please Wait" dialog.
                                            $('#create-rdp').dialog('close');
                                            $.dialog({
                                                title: 'Please Wait.',
                                                content: 'Please wait a moment while the connection is created.',
                                                closeIcon: false,
                                                backgroundDismiss: false,
                                                escapeKey: false,
                                            });
                                        }
                                    }).done(function(response) {
                                        // Refresh the page so that new values are added to page,
                                        // allowing VNC connection.
                                        window.location = window.location.origin + '/hosts';
                                    });
                                },
                                Cancel: function() {
                                    $(this).dialog('close');
                                }
                            }
                        });
                    }
                },
                Cancel: function() {
                }
            }
        });
    });

    $('.remote-vnc').click(function(e){
        e.preventDefault();
        var client    = btoa($(this).data('guac-vnc')+"\0c\0mysql");
        var token     = $(this).data('token');
        var server_ip = $(this).data('host-server-ip');
        window.open('http://'+server_ip+':8080/remote/#/client/'+client+'?token='+token);
    });

    $('.remote-rdp').click(function(e){
        e.preventDefault();
        var client = btoa($(this).data('guac-rdp')+"\0c\0mysql");
        var token = $(this).data('token');
        var server_ip = $(this).data('host-server-ip');
        window.open('http://'+server_ip+':8080/remote/#/client/'+client+'?token='+token);
    });

    $('#addHost').click(function(e){
        e.preventDefault();

        // Need to return something to host create form that is hidden and can't be faked,
        // like the subscription ID.
        $('#addHostForm').dialog({
                title: 'Create Host',
                //content: 'There was a problem adding the host!<br><br><strong>'+output.responseText+'</strong>',
                type: 'red',
                closeIcon: true,
                backgroundDismiss: true,
                onClose: function(){ window.location.href = window.location.origin + '/hosts'; }
        });
    });
});

/**
 * The following statement and function will update the host
 * status via AJAX so the user doesn't have to refresh the page.
 */
var statusHosts = window.setInterval(statusUpdateHosts, 5000);
function statusUpdateHosts() {
    $.ajax({
        url: '/hosts/statusUpdateHosts/'+$('#user-id').val(),
        type: 'GET',
    }).done(function(response) {
        var hosts = JSON.parse(response);
        for (var i = 0, len = hosts.length; i < len; i++) {
            if ($('#'+hosts[i].id).hasClass('status-off') && hosts[i].host_status_timestamp >= (Math.floor(Date.now() / 1000) - 40)) {
                $('#'+hosts[i].id).removeClass('status-off');
                $('#'+hosts[i].id).addClass('status-on');
            } else if ($('#'+hosts[i].id).hasClass('status-on') && hosts[i].host_status_timestamp < (Math.floor(Date.now() / 1000) - 40)) {
                $('#'+hosts[i].id).removeClass('status-on');
                $('#'+hosts[i].id).addClass('status-off');
            }
        }
    });
}

/**
 * The following statement and function will update the host
 * status via AJAX so the user doesn't have to refresh the page.
 */
var statusAssignedHosts = window.setInterval(statusUpdateAssignedHosts, 5000);
function statusUpdateAssignedHosts() {
    $.ajax({
        url: '/hosts/statusUpdateAssignedHosts/'+$('#user-id').val(),
        type: 'GET',
    }).done(function(response) {
        var hosts = JSON.parse(response);
        for (var i = 0, len = hosts.length; i < len; i++) {
            if ($('#'+hosts[i].host_id.id).hasClass('status-off') && hosts[i].host_id.host_status_timestamp >= (Math.floor(Date.now() / 1000) - 40)) {
                $('#'+hosts[i].host_id.id).removeClass('status-off');
                $('#'+hosts[i].host_id.id).addClass('status-on');
            } else if ($('#'+hosts[i].host_id.id).hasClass('status-on') && hosts[i].host_id.host_status_timestamp < (Math.floor(Date.now() / 1000) - 40)) {
                $('#'+hosts[i].host_id.id).removeClass('status-on');
                $('#'+hosts[i].host_id.id).addClass('status-off');
            }
        }
    });
}
