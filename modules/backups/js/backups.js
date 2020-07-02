jQuery(document).ready(function($){
    // Check to make sure no other backup exists; only one backup will work for Switchr client.
    $('.create-backup').click(function(e){
        e.preventDefault();
        var id = $(this).attr('id');
        var type = $(this).data('type');
        var host_id = $(this).data('hostId');

        $.ajax({
            url: '/backups/check',
            type: 'POST',
            data: {
                type: type,
                host_id: host_id,
            },
            error: function() {
                $.dialog({
                    title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                    content: 'There was a problem!<br><strong>Only one backup per host can be set.</strong>',
                    type: 'red',
                    closeIcon: true,
                    backgroundDismiss: true,
                });
            },
            success: function() {
                window.location.href = '/backups/create/'+type+'/'+host_id;
            }
        });
    });

    // Check for existing bucket name and DNS compatibility before submitting the form.
    $('#s3-backup-submit').click(function(e){
        e.preventDefault();

        var message;
        if ($('#backup_dest_name').val() === '') {
            message = "Enter a display name.";
        }
        if (message) {
            $.dialog({
                title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                content: 'There was a problem!<br><strong>'+message+'</strong>',
                type: 'red',
                closeIcon: true,
                backgroundDismiss: true,
            });
            return false;
        }

        var bucket_name = $('#backup_s3_bucketname').val();
        var region = $('#backup_s3_regionendpoint').val();
        var key = $('#backup_s3_awsaccesskeyid').val();
        var secret = $('#backup_s3_awssecretkey').val();
        var host_id = $('[name="backup_dest_host_id"]').val();

        // Check for DNS-compatible name and follow Amazon's rules for bucket names.
        if ( bucket_name.length < 3 ||
             bucket_name.length > 63 ||
             bucket_name.match(/[^a-z0-9-]+/) ||
             bucket_name.match(/^[^a-z0-9]/) ||
             bucket_name.match(/[^a-z0-9]$/)) {
             $.dialog({
                 title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                 content: 'There was a problem!<br>There are rules that must be followed when creating bucket names:<br><br><strong>Bucket names must be at least 3 and no more than 63 characters long.<br><br>Bucket names can only contain lowercase letters, numbers, and hyphens.<br><br>Bucket names must start and end with a lowercase letter or a number.</strong>',
                 type: 'red',
                 closeIcon: true,
                 backgroundDismiss: true,
             });
        // If the local name-check passes then make a dummy request to AWS to make sure credentials are valid.
        // In order for AWS queries to work the credentials (key and secret) must be correct,
        // otherwise incorrect values are returned.
        } else {
            $.ajax({
                url: '/backups/checkCreds',
                type: 'POST',
                data: {
                    region:region,
                    key:key,
                    secret:secret,
                },
                beforeSend: function() {
                    $.dialog({
                        title: 'Please Wait',
                        content: 'Please wait a moment while the backup is created.',
                        closeIcon: false,
                        backgroundDismiss: false,
                        escapeKey: false,
                    });
                },
            }).done(function(response) {
                if (response.substr(0,1) == "{" && $.parseJSON(response).status === false) {
                    $('.jconfirm').remove();
                    $.dialog({
                        title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                        content: 'There was a problem!<br><strong>Key ID or Secret Key incorrect.</strong>',
                        type: 'red',
                        closeIcon: true,
                        backgroundDismiss: true,
                    });
                // If credentials are valid then query Amazon to make sure the bucket name doesn't already exist.
                // This will actually attempt to create the bucket after calling doesBucketExist(),
                // because doesBucketExist() doesn't always work.
                } else {
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
                            // Only run select statements on the create from, not the edit form.
                            // If this is the S3 create form.
                            if (!window.edit) {
                                $.ajax({
                                    url: '/backups/createBucket',
                                    type: 'POST',
                                    data: {
                                        bucket_name:bucket_name,
                                        region:region,
                                        key:key,
                                        secret:secret,
                                    }
                                }).done(function(response) {
                                    if (response === 'false') {
                                        $('.jconfirm').remove();
                                        $.dialog({
                                            title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                            content: 'There was a problem!<br><strong>Bucket name already exists on Amazon.<br>You must choose a unique name.</strong>',
                                            type: 'red',
                                            closeIcon: true,
                                            backgroundDismiss: true,
                                        });
                                    // If everything looks good then submit the form.
                                    } else {
                                        var selected_items = new Object();
                                        // For each selected folder. We use the recursive checkbox.
                                        $('ul#source-selected-list > div.source-selected-parent').each(function() {
                                            var path = $(this).data('id');
                                            selected_items[path] = new Object();
                                            if ($(this).data('type') == 'dir') {
                                                var checkbox_recursive = $(this).find('input[type=checkbox]');
                                                if (checkbox_recursive[0].checked) {
                                                    selected_items[path].recursive = true;
                                                } else {
                                                    selected_items[path].recursive = false;
                                                }
                                            }
                                        });

                                        // Assign all selected items to input.
                                        $('#backup_dest_source').val(JSON.stringify(selected_items));
                                        // Submit the form.
                                        $('#create-s3-form').submit();
                                    }
                                });
                            // If this is the S3 edit form.
                            } else {
                                var selected_items = new Object();
                                // For each selected folder. We use the recursive checkbox.
                                $('ul#source-selected-list > div.source-selected-parent').each(function() {
                                    var path = $(this).data('id');
                                    selected_items[path] = new Object();
                                    if ($(this).data('type') == 'dir') {
                                        var checkbox_recursive = $(this).find('input[type=checkbox]');
                                        if (checkbox_recursive[0].checked) {
                                            selected_items[path].recursive = true;
                                        } else {
                                            selected_items[path].recursive = false;
                                        }
                                    }
                                });

                                // Assign all selected items to input.
                                $('#backup_dest_source').val(JSON.stringify(selected_items));
                                // Submit the form.
                                $('#edit-s3-form').submit();
                            }
                        }
                    });
                }
            });
        }
    });

    $('.delete-backup').click(function(){
        $.dialog({
            title: 'Please Wait',
            content: 'It will take up to one minute to delete the backup.',
            closeIcon: false,
            backgroundDismiss: false,
            escapeKey: false,
        });
    });

    // FTP client main window.
    // Allows the user to make a directory.
    ftp_dialog = $('#ftp-client').dialog({
        autoOpen: false,
        height: 300,
        width: 350,
        modal: true,
        buttons: {
            'Make Dir': function() {
                $('#ftp-new-dir').show();
                $('#ftp-mkdir').dialog({
                    modal: true,
                    resizable: false,
                    buttons: [
                        {
                            text: 'Ok',
                            click: function() {
                                // 'ftp' or 'sftp'.
                                var type;
                                // Create form selector.
                                if ($('input[name=backup_dest_type_button].btn-primary').length) {
                                    type = $('input[name=backup_dest_type_button].btn-primary').val().toLowerCase();
                                // Edit form selector.
                                } else {
                                    type = $('#backup_dest_type').val();
                                }
                                // var type     = $('input[name=backup_dest_type_button].btn-primary').val().toLowerCase();
                                var username = $('#backup_dest_username').val();
                                var password = $('#backup_dest_password').val();
                                var hostname = $('#backup_dest_hostname').val();
                                var port     = $('#backup_dest_port').val();
                                var passive  = $('#backup_dest_passive').val();
                                var key      = $('#backup_dest_ssh_key').val();
                                var key_password  = $('#backup_dest_ssh_password').val();

                                var cur_path = $('#backup_dest_dest').val();
                                var new_dir = cur_path + '/' + $('#ftp-new-dir').val();
                                // Close the mkdir dialog.
                                $('#ftp-mkdir').dialog('close');
                                // Clear the mkdir input field.
                                $('#ftp-new-dir').val('');

                                var url_mkdir;
                                if (type == 'ftp') {
                                    url_mkdir = '/backups/ftpMkDir';
                                } else if (type == 'sftp') {
                                    url_mkdir = '/backups/sftpMkDir';
                                }

                                $.ajax({
                                    url: url_mkdir,
                                    type: 'POST',
                                    data: {
                                        // Directory needs to be previous path plus selected directory.
                                        current_path: cur_path,
                                        new_dir: new_dir,
                                        username: username,
                                        password: password,
                                        hostname: hostname,
                                        port: port,
                                        passive: passive,
                                        key: key,
                                        key_password: key_password,
                                    },
                                    dataType: 'json'
                                }).done(function(response) {
                                    // Inform user if we have a problem connecting.
                                    if (response == 'false' || response === false || response === null) {
                                        $.dialog({
                                            title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                            content: 'There was a problem!<br><strong>Check FTP hostname, port, username, and password.</strong>',
                                            type: 'red',
                                            closeIcon: true,
                                            backgroundDismiss: true,
                                        });
                                    // If we are connected to FTP then list the directories.
                                    } else {
                                        $('#backup_dest_dest').val(cur_path);
                                        // Remove previous directory list.
                                        $('.ftp-dir').remove();
                                        // Add new directories.
                                        $.each(response.dirs, function(key, dir_name) {
                                            // If server responds with '.' and '..' then do not include them in the list.
                                            // We already manually include '..' to go up, and '.' is not needed.
                                            if (dir_name != '.' && dir_name != '..') {
                                                $('#ftp-folders').append("<li class='ftp-dir'>"+ dir_name +"</li>");
                                            }
                                        });
                                        // Refresh to activate the new items as menu items.
                                        $('#ftp-folders').menu('refresh');
                                    }
                                });
                            },
                        },
                        {
                            text: 'Cancel',
                            click: function() {
                                $(this).dialog('close');
                            },
                        },
                    ],
                });
            },
            Okay: function() {
                if ($('#backup_dest_dest').val() === '') {
                    $('#backup_dest_dest').val('/');
                }
                $(this).dialog('close');
            },
        },
    });

    // Handles the FTP directory-chooser.
    $('#ftp-open').click(function(e){
        // Need this because form will try to submit for some reason.
        e.preventDefault();
        // Empty in case this isn't the first time the client is being opened.
        $('.ftp-dir').remove();
        $('#backup_dest_dest').val(null);

        // 'ftp' or 'sftp'.
        var type;
        // Create form selector.
        if ($('input[name=backup_dest_type_button].btn-primary').length) {
            type = $('input[name=backup_dest_type_button].btn-primary').val().toLowerCase();
        // Edit form selector.
        } else {
            type = $('#backup_dest_type').val();
        }
        var username = $('#backup_dest_username').val();
        var password = $('#backup_dest_password').val();
        var hostname = $('#backup_dest_hostname').val();
        var port     = $('#backup_dest_port').val();
        var passive  = $('#backup_dest_passive').val();
        var key      = $('#backup_dest_ssh_key').val();
        var key_password  = $('#backup_dest_ssh_password').val();

        var url_connect;
        var url_chdir;
        if (type == 'ftp') {
            url_connect = '/backups/ftpConnect';
            url_chdir = '/backups/ftpChDir';
        } else if (type == 'sftp') {
            url_connect = '/backups/sftpConnect';
            url_chdir = '/backups/sftpChDir';
        }

        $.ajax({
            url: url_connect,
            type: 'POST',
            data: {
                username: username,
                password: password,
                hostname: hostname,
                port: port,
                passive: passive,
                key: key,
                key_password: key_password,
            },
            dataType: "json"
        }).done(function(response) {
            // Inform user if we have a problem connecting.
            if (response == 'false' || response.dirs === null) {
                $.dialog({
                    title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                    content: 'There was a problem!<br><strong>Check FTP hostname, port, username, and password.</strong>',
                    type: 'red',
                    closeIcon: true,
                    backgroundDismiss: true,
                });
            // If we are connected to FTP then list the directories.
            } else {
                // SFTP needs absolute path.
                if (type != 'ftp') {
                    $('#backup_dest_dest').val(response.cwd);
                // FTP needs path relative to user's home dir, so we use '.' as initial directory.
                } else {
                    $('#backup_dest_dest').val('.');
                }
                $.each(response.dirs, function(key, dir_name) {
                    // If server responds with '.' and '..' then do not include them in the list.
                    // We already manually include '..' to go up, and '.' is not needed.
                    if (dir_name != '.' && dir_name != '..') {
                        $('#ftp-folders').append("<li class='ftp-dir'>"+dir_name+"</li>");
                    }
                });
                $('#ftp-folders').menu({
                    select: function(event, ui) {
                        // Prevent Clicky McClickerson from doing what he does best.
                        // The elements will redraw, so no need to reverse this operation.
                        $('.ftp-dir').prop('disabled', true);

                        var cur_path = $('#backup_dest_dest').val();
                        var new_path;
                        // If we are traversing up then provide the current path up to the last slash.
                        if (ui.item['0'].textContent == '..') {
                            new_path = cur_path.substr(0, cur_path.lastIndexOf('/'));
                        } else {
                            new_path = cur_path + '/' + ui.item['0'].textContent;
                        }

                        $('#backup_dest_dest').val(new_path);
                        $.ajax({
                            url: url_chdir,
                            type: 'POST',
                            data: {
                                // Directory needs to be previous path plus selected directory.
                                dir: new_path,
                                username: username,
                                password: password,
                                hostname: hostname,
                                port: port,
                                passive: passive,
                                key: key,
                                key_password: key_password,
                            },
                            dataType: 'json'
                        }).done(function(response) {
                            // Inform user if we have a problem connecting.
                            if (response == 'false' || response === false || response === null) {
                                $.dialog({
                                    title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                    content: 'There was a problem!<br><strong>Check FTP hostname, port, username, and password.</strong>',
                                    type: 'red',
                                    closeIcon: true,
                                    backgroundDismiss: true,
                                });
                            // If we are connected to FTP then list the directories.
                            } else {
                                // Remove previous directory list.
                                $('.ftp-dir').remove();
                                // If at the top-most directory and '..' is clicked, then we need to re-include '.'.
                                if ($('#backup_dest_dest').val() === '' && type == 'ftp') {
                                    $('#backup_dest_dest').val('.');
                                }
                                // Add new directories.
                                $.each(response.dirs, function(key, dir_name) {
                                    // If server responds with '.' and '..' then do not include them in the list.
                                    // We already manually include '..' to go up, and '.' is not needed.
                                    if (dir_name != '.' && dir_name != '..') {
                                        $('#ftp-folders').append("<li class='ftp-dir'>"+ dir_name +"</li>");
                                    }
                                });
                                // Refresh to activate the new items as menu items.
                                $('#ftp-folders').menu('refresh');
                            }
                        });
                    }
                });
                $('#ftp-client').dialog('open');
                // Need to refresh in case this isn't the first time it's being opened.
                $('#ftp-folders').menu('refresh');
            }
        });
    });

    // Show either FTP or SFTP fields upon radio select.
    $('input[name=backup_dest_type_button').click(function(e){
        var type = $(this).val().toLowerCase();
        $(this).addClass('btn-primary');
        if (type == 'ftp') {
            $('#sftp-radio').removeClass('btn-primary');
            $('#backup_dest_port').val('21');
            $('.sftp').hide();
            $('.ftp').show();
            $('.both').show();
        } else if (type == 'sftp') {
            $('#ftp-radio').removeClass('btn-primary');
            $('#backup_dest_port').val('22');
            $('.ftp').hide();
            $('.sftp').show();
            $('.both').show();
        }
    });

    // SSH source client main window.
    source_dialog = $('#source-client').dialog({
        autoOpen: false,
        height: 300,
        width: 350,
        modal: true,
        buttons: {
            // 'Add Selected': function() {
            //
            //     $(this).dialog('close');
            // },
            Okay: function() {
                $(this).dialog('close');
            },
        },
    });

    // Handles the SSH source directory-chooser.
    $('#source-open').click(function(e){
        // Need this because form will try to submit for some reason.
        e.preventDefault();
        // Empty in case this isn't the first time the client is being opened.
        $('.source-dir').remove();
        $('.source-file').remove();
        $('#backup_dest_source').val(null);

        var host_id  = $('#backup_dest_host_id').val();

        $.ajax({
            url: '/backups/sourceConnect',
            type: 'POST',
            data: {
                host_id: host_id,
            },
            dataType: "json"
        }).done(function(response) {
            // Inform user if we have a problem connecting.
            if (response == 'false' || response === false || response.dirs === null) {
                $.dialog({
                    title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                    content: 'There was a problem!<br><strong>Could not connect to client. Is it running?</strong>',
                    type: 'red',
                    closeIcon: true,
                    backgroundDismiss: true,
                });
            // If we are connected to source then list the directories.
            } else {
                $('#backup_dest_source').val(response.cwd);
                $.each(response.dirs, function(key, dir_name) {
                    $('#source-folders').append("<li class='source-dir'>"+dir_name+"</li>");
                });
                $('#source-folders').menu({
                    select: function(event, ui) {
                        // Prevent Clicky McClickerson from doing what he does best.
                        // The elements will redraw, so no need to reverse this operation.
                        $('.source-dir').prop('disabled', true);
                        $('.source-file').prop('disabled', true);

                        var cur_path = $('#backup_dest_source').val();
                        var new_path;
                        // If we are traversing up then provide the current path up to the last slash.
                        // All paths are specific to Windows OS.
                        if (ui.item['0'].textContent == '..') {
                            new_path = cur_path.split("\\");
                            new_path = new_path.slice(0, -2);
                            new_path = new_path.join("\\") + "\\";
                            // If the user goes out of the drive, then just
                            // close the dialog and make them open it again.
                            // Ideally it would instead show the initial drives,
                            // but this can be polished later.
                            if (new_path == '\\') {
                                $(source_dialog).dialog('close');
                                $('#backup_dest_source').val(null);
                                return false;
                            }
                        // If we are just starting, then we are working with a driver letter, ie: 'C:'.
                        } else if (cur_path === false) {
                            new_path = ui.item['0'].textContent + "\\";
                        // Normal path operations and bacon and biscuits with butter.
                        } else {
                            new_path = cur_path + ui.item['0'].textContent + "\\";
                        }

                        $('#backup_dest_source').val(new_path);
                        $.ajax({
                            url: '/backups/sourceChDir',
                            type: 'POST',
                            data: {
                                // Directory needs to be previous path plus selected directory.
                                dir: new_path,
                                host_id: host_id,
                            },
                            dataType: 'json'
                        }).done(function(response) {
                            // Inform user if we have a problem connecting.
                            if (response == 'false' || response === false || response === null) {
                                $.dialog({
                                    title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                                    content: 'There was a problem!<br><strong></strong>',
                                    type: 'red',
                                    closeIcon: true,
                                    backgroundDismiss: true,
                                });
                            // If we are connected to source then list the directories.
                            } else {
                                // Remove previous directory list.
                                $('.source-dir').remove();
                                // Remove previous file list.
                                $('.source-file').remove();
                                // Directories.
                                $.each(response.dirs, function(key, dir_name) {
                                    var path = btoa(response.cwd+dir_name);
                                    $('#source-folders').append(
                                        // Checkbox for directory.
                                        "<input data-id='"+path+"' type='checkbox' value='"+response.cwd+dir_name+"' class='source-dir source-dir-checkbox' style='width:15px;height:15px;display:inline-block;margin-left:10px;margin-right:10px;' onclick='sourceDirClick(this);' checked=''>"+

                                        // Checkbox for recursive.
                                        "<input data-id='"+path+"' type='checkbox' value='"+response.cwd+dir_name+"' class='source-dir source-dir-checkbox-recursive' style='width:15px;height:15px;display:inline-block;margin-left:10px;margin-right:10px;' onclick='sourceDirClickRecursive(this);' checked=''>"+

                                        "<div data-id='"+path+"' class='source-dir' style='width:75%;display:inline-block;'>"+
                                            // Directory name.
                                            "<li class='source-dir' style='width:100%;display:inline-block;'>"+ dir_name +"</li>"+
                                        "</div>");
                                    // If item is in the selected list.
                                    if (window.selected_dir_list[path]) {
                                        // Check the include checkbox.
                                        $('.source-dir-checkbox[data-id="'+path+'"]').prop('checked', true);
                                        // If item is in the selected list and recursive has been selected.
                                        if (window.selected_dir_list[path].recursive) {
                                            // Check the recursive checkbox.
                                            $('.source-dir-checkbox-recursive[data-id="'+path+'"]').prop('checked', true);
                                        // If item is in the selected list and recursive has not been selected.
                                        } else {
                                            // Uncheck the recursive checkbox.
                                            $('.source-dir-checkbox-recursive[data-id="'+path+'"]').prop('checked', false);
                                        }
                                    // If item is not in the selected list.
                                    } else {
                                        // Uncheck the include checkbox.
                                        $('.source-dir-checkbox[data-id="'+path+'"]').prop('checked', false);
                                        // Uncheck the recursive checkbox.
                                        $('.source-dir-checkbox-recursive[data-id="'+path+'"]').prop('checked', false);
                                    }

                                });
                                // Files
                                $.each(response.files, function(key, file_name) {
                                    var path = btoa(response.cwd+file_name);
                                    $('#source-folders').append(
                                        // Checkbox for file.
                                        "<input data-id='"+path+"' type='checkbox' value='"+response.cwd+file_name+"' class='source-file source-file-checkbox' style='width:15px;height:15px;display:inline-block;margin-left:10px;margin-right:20px;' onclick='sourceFileClick(this);' checked=''>"+

                                        "<div data-id='"+path+"' class='source-file' style='width:80%;display:inline-block;'>"+
                                            // File name.
                                            "<li class='source-file' style='width:100%;display:inline-block;'>"+ file_name +"</li>"+
                                        "</div>");
                                    // If item is in the selected list.
                                    if (window.selected_file_list[path]) {
                                        // Check the include checkbox.
                                        $('.source-file-checkbox[data-id="'+path+'"]').prop('checked', true);
                                    // If item is not in the selected list.
                                    } else {
                                        // Uncheck the include checkbox.
                                        $('.source-file-checkbox[data-id="'+path+'"]').prop('checked', false);
                                    }
                                });
                                // Refresh to activate the new items as menu items.
                                $('#source-folders').menu('refresh');
                            }
                        });
                    }
                });
                $('#source-client').dialog('open');
                // Need to refresh in case this isn't the first time it's being opened.
                $('#source-folders').menu('refresh');
            }
        });
    });

    // List of selected directories.
    window.selected_dir_list = new Object();
    // List of selected files.
    window.selected_file_list = new Object();

    // Handle directory checkboxes.
    // We want to add the element value (full directory path) to another ul element.
    // That element will have the 'recursive' checkbox and 'remove' button.
    window.sourceDirClick = function(el) {
        // Encode the path to base64 because there are non-alphanum characters.
        var path = btoa($(el).val());
        // If checkbox is checked.
        if (el.checked) {
            window.selected_dir_list[path] = new Object();
            window.selected_dir_list[path].element = $('.source-dir-checkbox[data-id="'+path+'"]');
            $('#source-selected-list').append(
                // This wrapper div is needed to remove the item from the list using selectedRemove().
                "<div data-id='"+path+"' data-type='dir' class='source-selected source-selected-parent' style='width:100%;'>"+
                    "<div class='source-selected' style='width:50%;display:inline-block;'>"+
                        "<li class='source-selected source-selected-dir' style='width:100%;' data-value='"+path+"'>"+$(el).val()+"</li>"+
                    "</div>"+
                    // Checkbox for recursive.
                    "<input data-id='"+path+"' type='checkbox' value='"+path+"' class='source-selected source-selected-recursive' style='width:15px;height:15px;display:inline-block;margin-left:20px;margin-right:10px;' onclick='sourceSelectedClickRecursive(this);'>"+

                    "<button class='source-selected source-selected-remove' style='width:75px;display:inline-block;margin-left:20px;' onclick='selectedRemove(this);'>Remove</button>"+
                "</div>");
        // If checkbox is unchecked.
        } else {
            // Delete from object list.
            delete window.selected_dir_list[path];
            // Remove from selected list.
            $('.source-selected-parent[data-id="'+path+'"]').remove();
        }
    };

    // Handle directory recursive checkboxes.
    // We want to add the element value (full directory path) to another ul element.
    // That element will have the 'recursive' checkbox and 'remove' button.
    window.sourceDirClickRecursive = function(el) {
        // Encode the path to base64 because there are non-alphanum characters.
        var path = btoa($(el).val());
        // If recursive checkbox is checked.
        if (el.checked) {
            // If the include checkbox is unchecked then we need to automatically check it.
            if (!window.selected_dir_list[path]) {
                $('.source-dir-checkbox[data-id="'+path+'"]').prop('checked', true);
                window.sourceDirClick(el);
                window.selected_dir_list[path].recursive = true;
            }
            // Check the recursive checkbox in the selected list.
            $('.source-selected-recursive[data-id="'+path+'"]').prop('checked', true);
        // If recursive checkbox is unchecked.
        } else {
            window.selected_dir_list[path].recursive = false;
            $('.source-selected-recursive[data-id="'+path+'"]').prop('checked', false);
        }
    };

    // Handle directory recursive checkboxes.
    // This will check/uncheck the boxes in #source-folders.
    window.sourceSelectedClickRecursive = function(el) {
        // The path is already base64 encoded when it is created, so we just need to grab it.
        var path = $(el).val();
        // If recursive checkbox is checked.
        if (el.checked) {
            window.selected_dir_list[path].recursive = true;
            $('.source-dir-checkbox-recursive[data-id="'+path+'"]').prop('checked', true);
        // If recursive checkbox is unchecked.
        } else {
            window.selected_dir_list[path].recursive = false;
            $('.source-dir-checkbox-recursive[data-id="'+path+'"]').prop('checked', false);
        }
    };


    // Handle file checkboxes.
    // We want to add the element value (full file path) to another ul element.
    // That element will have the 'recursive checkbox' and 'remove' button.
    window.sourceFileClick = function(el) {
        // Encode the path to base64 because there are non-alphanum characters.
        var path = btoa($(el).val());
        if (el.checked) {
            window.selected_file_list[path] = new Object();
            window.selected_file_list[path].element = $('.source-file-checkbox[data-id="'+path+'"]');
            $('#source-selected-list').append(
                // This wrapper div is needed to remove the item from the list using selectedRemove().
                "<div data-id='"+path+"' data-type='file' class='source-selected source-selected-parent' style='width:100%;'>"+
                    "<div class='source-selected' style='width:50%;display:inline-block;'>"+
                        // Grab with $(this).data('value')
                        "<li class='source-selected source-selected-dir source-selected-file' style='width:100%;' data-value='"+path+"'>"+$(el).val()+"</li>"+
                    "</div>"+

                    "<div style='width:15px;height:15px;display:inline-block;margin-left:20px;margin-right:10px;'></div>"+

                    "<button class='source-selected source-selected-remove' style='width:75px;display:inline-block;margin-left:20px;' onclick='selectedRemove(this);'>Remove</button>"+
                "</div>");
        } else {
            delete window.selected_file_list[path];
            $('.source-selected-parent[data-id="'+path+'"]').remove();
        }
    };

    // Handle removal of #source-selected-list children.
    window.selectedRemove = function(el) {
        // Get base64 ID.
        var path = $(el).parent()[0].dataset.id;
        // If it is a file.
        if ($(el).parent()[0].dataset.type == 'file') {
            // Delete from list.
            delete window.selected_file_list[path];
        // If it is a directory.
        } else if ($(el).parent()[0].dataset.type == 'dir') {
            // Delete from list.
            delete window.selected_dir_list[path];
        }
        // Remove element.
        $(el).parent().remove();
    };

    // SSH source client main window.
    selected_dialog = $('#source-selected').dialog({
        autoOpen: false,
        height: 300,
        width: 350,
        modal: true,
        buttons: {
            Close: function() {
                $(this).dialog('close');
            },
        },
    });

    // Handles the SSH source directory-chooser.
    $('#source-selected-open').click(function(e){
        // Need this because form will try to submit for some reason.
        e.preventDefault();
        $('#source-selected').dialog('open');
    });

    // If necessary fields are empty then tell user they are required.
    // The 'if' statements are in reverse order so the first empty field is shown in the dialog.
    $('#backup-submit').click(function(event){
        event.preventDefault();
        var message;
        if ($('#backup_dest_dest').val() === '') {
            message = "Specify a destination.";
        }
        if ($('#backup_dest_name').val() === '') {
            message = "Enter a display name.";
        }
        if (message) {
            $.dialog({
                title: '<img src="/addons/cry_60.png" style=""> Oh nohs...',
                content: 'There was a problem!<br><strong>'+message+'</strong>',
                type: 'red',
                closeIcon: true,
                backgroundDismiss: true,
            });
        } else {
            var selected_items = new Object();
            // For each selected folder. We use the recursive checkbox.
            $('ul#source-selected-list > div.source-selected-parent').each(function() {
                var path = $(this).data('id');
                selected_items[path] = new Object();
                if ($(this).data('type') == 'dir') {
                    var checkbox_recursive = $(this).find('input[type=checkbox]');
                    if (checkbox_recursive[0].checked) {
                        selected_items[path].recursive = true;
                    } else {
                        selected_items[path].recursive = false;
                    }
                }
            });

            // Assign all selected items to input.
            $('#backup_dest_source').val(JSON.stringify(selected_items));
            // Put value of selected type into hidden input, either FTP or SFTP.
            if ($('input[name=backup_dest_type_button]').length) {
                $('input[name=backup_dest_type]').val($('input[name=backup_dest_type_button].btn-primary').val().toLowerCase());
            }
            // Submit the form.
            $('#backup-create-form').submit();
        }
    });
});
