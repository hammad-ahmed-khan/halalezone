$(document).ready(function() {
    let uploadedFiles = [];

    // Load recipient count on page load
    loadRecipientCount();

    // Handle recipient type change
    $('input[name="recipient_type"]').change(function() {
        const recipientType = $(this).val();
        
        if (recipientType === 'specific') {
            $('#specificRecipientsDiv').show();
            // Initialize Chosen dropdowns when shown
            initializeChosenDropdowns();
            $('#recipientCount').html('<i class="fa fa-info-circle"></i> Select specific recipients below');
        } else {
            $('#specificRecipientsDiv').hide();
            // Clear selections
            $('#selectedClients').val([]).trigger('chosen:updated');
            $('#selectedTeam').val([]).trigger('chosen:updated');
            loadRecipientCount();
        }
    });

    // Handle specific recipients selection change
    $('#selectedClients, #selectedTeam').on('change', function() {
        updateSpecificRecipientCount();
    });

    // Initialize Chosen dropdowns
    function initializeChosenDropdowns() {
        if (!$('#selectedClients').hasClass('chosen-container')) {
            $('#selectedClients').chosen({
                width: '100%',
                placeholder_text_multiple: 'Choose clients...',
                search_contains: true
            });
        }
        
        if (!$('#selectedTeam').hasClass('chosen-container')) {
            $('#selectedTeam').chosen({
                width: '100%',
                placeholder_text_multiple: 'Choose team members...',
                search_contains: true
            });
        }
    }

    // Initialize file uploader (same as tasks.php)
    $('#fileupload999')
        .fileupload({
            url: 'fileupload/ProcessFiles.php',
            dataType: 'json',
            dropZone: $('#dropzone999'),
            add: function (e, data) {
                data.formData = {
                    folderType: $(this).attr('foldertype'),
                    infoType: $(this).attr('infotype'),
                    subFolder: $(this).attr('subfolder'),
                    client: 'notifications',
                };
                
                var goUpload = true;
                var uploadFile = data.files[0];
                
                // Validate file types (same as tasks.php but extended)
                if (!/\.(jpg|jpeg|png|gif|xls|xlsx|pdf|ppt|pptx|doc|docx)$/i.test(uploadFile.name)) {
                    alert('You can upload JPG, JPEG, PNG, GIF, Excel, PDF, PowerPoint, or Word files only');
                    goUpload = false;
                }
                
                // Check file size (10MB limit)
                if (uploadFile.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB');
                    goUpload = false;
                }

                if (goUpload == true) {
                    data.submit();
                }
            },
            start: function (e) {
                $(this).parent().siblings('.loader').show();
            },
            fail: function (e, data) {
                $(this).parent().siblings('.loader').hide();
                alert('Error uploading file (' + data.errorThrown + ')');
            },
            done: function (e, data) {
                $(this).parent().siblings('.loader').hide();
                $.each(data.result.files, function (index, file) {
                    var jsonstring = JSON.stringify({
                        name: file.name,
                        glink: file.googleDriveUrl,
                        hostpath: file.url,
                        hostUrl: file.hostUrl
                    });
                    
                    var filename = $('<li class="uploaded-file-name" originalname="' + encodeURI(jsonstring) + '"></li>');
                    filename.append($('<span>', { text: file.name }));
                    filename.append(
                        $('<span class="btn btn-danger btn-xs delete uploaded-file-name-close remove-doc" type="button" ' +
                          'fileid="' + file.googleDriveId + '" hostpath="' + encodeURI(file.url) + '" title="Remove the document">' +
                          '<i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
                        ).bind('click', function (e) {
                            delDocClick(e);
                        })
                    );
                    
                    $('#ul' + file.folderType).append(filename);
                    uploadedFiles.push({
                        name: file.name,
                        path: file.url,
                        hostpath: file.hostUrl
                    });
                });
            },
        })
        .prop('disabled', !$.support.fileInput)
        .parent()
        .addClass($.support.fileInput ? undefined : 'disabled');

    // Delete document function (similar to tasks.php)
    window.delDocClick = function(e) {
        var fileid = $(e.target).parent().attr('fileid');
        var hostpath = decodeURI($(e.target).parent().attr('hostpath'));
        
        // Remove from uploaded files array
        uploadedFiles = uploadedFiles.filter(function(file) {
            return file.path !== hostpath;
        });
        
        // Remove visual element
        $(e.target).parent().parent().remove();
    };

    // Preview button
    $('#previewBtn').click(function() {
        generatePreview();
    });

    // Form submission
    $('#notificationForm').submit(function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        if (!confirm('Are you sure you want to send this notification to the selected recipients?')) {
            return;
        }
        
        sendNotification();
    });

    // Save draft button
    $('#saveDraftBtn').click(function() {
        saveDraft();
    });

    // Functions
    function loadRecipientCount() {
        const recipientType = $('input[name="recipient_type"]:checked').val();
        
        if (recipientType === 'specific') {
            return;
        }
        
        $.ajax({
            url: 'ajax/get_recipient_count.php',
            type: 'POST',
            data: { recipient_type: recipientType },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let icon = '';
                    let className = '';
                    
                    switch(recipientType) {
                        case 'all_clients':
                            icon = '<i class="fa fa-users text-primary"></i>';
                            className = 'text-primary';
                            break;
                        case 'all_team':
                            icon = '<i class="fa fa-cogs text-success"></i>';
                            className = 'text-success';
                            break;
                        case 'all_both':
                            icon = '<i class="fa fa-globe text-info"></i>';
                            className = 'text-info';
                            break;
                    }
                    
                    $('#recipientCount').html(
                        icon + ` <span class="${className}"><strong>${response.count}</strong> recipients will receive this notification</span>`
                    );
                } else {
                    $('#recipientCount').html('<i class="fa fa-exclamation-triangle text-danger"></i> Error loading recipient count');
                }
            },
            error: function() {
                $('#recipientCount').html('<i class="fa fa-exclamation-triangle text-danger"></i> Error loading recipient count');
            }
        });
    }

    function updateSpecificRecipientCount() {
        const selectedClients = $('#selectedClients').val() || [];
        const selectedTeam = $('#selectedTeam').val() || [];
        const totalCount = selectedClients.length + selectedTeam.length;
        
        if (totalCount === 0) {
            $('#recipientCount').html('<i class="fa fa-info-circle text-muted"></i> Select specific recipients below');
        } else {
            let parts = [];
            if (selectedClients.length > 0) {
                parts.push(`<span class="text-primary"><strong>${selectedClients.length}</strong> clients</span>`);
            }
            if (selectedTeam.length > 0) {
                parts.push(`<span class="text-success"><strong>${selectedTeam.length}</strong> team members</span>`);
            }
            $('#recipientCount').html(
                '<i class="fa fa-check text-success"></i> ' + parts.join(' + ') + ` = <strong>${totalCount}</strong> total recipients`
            );
        }
    }

    function generatePreview() {
        const subject = $('#subject').val();
        const message = $('#message').val();
        
        if (!subject || !message) {
            alert('Please enter subject and message to generate preview.');
            return;
        }
        
        $.ajax({
            url: 'ajax/generate_email_preview.php',
            type: 'POST',
            data: {
                subject: subject,
                message: message,
                include_signature: false
            },
            success: function(response) {
                $('#emailPreview').html(response).show();
            },
            error: function() {
                alert('Error generating preview.');
            }
        });
    }

    function validateForm() {
        // Check required fields
        if (!$('#subject').val().trim()) {
            alert('Subject is required.');
            $('#subject').focus();
            return false;
        }
        
        if (!$('#message').val().trim()) {
            alert('Message is required.');
            $('#message').focus();
            return false;
        }
        
        // Check specific recipients if selected
        const recipientType = $('input[name="recipient_type"]:checked').val();
        if (recipientType === 'specific') {
            const selectedClients = $('#selectedClients').val() || [];
            const selectedTeam = $('#selectedTeam').val() || [];
            if (selectedClients.length === 0 && selectedTeam.length === 0) {
                alert('Please select at least one specific recipient.');
                return false;
            }
        }
        
        // Check confirmation
        if (!$('#confirmSend').is(':checked')) {
            alert('Please confirm that you want to send this notification.');
            $('#confirmSend').focus();
            return false;
        }
        
        return true;
    }

    function sendNotification() {
        // Collect uploaded files info
        const attachments = [];
        $("#uladdoc999 li").each(function () {
            const fileData = $(this).attr('originalname');
            if (fileData) {
                try {
                    const decodedData = decodeURI(fileData);
                    const fileInfo = JSON.parse(decodedData);
                    attachments.push(fileInfo);
                } catch (e) {
                    console.error('Error parsing file data:', e);
                }
            }
        });

        const formData = {
            recipient_type: $('input[name="recipient_type"]:checked').val(),
            selected_clients: $('#selectedClients').val() || [],
            selected_team: $('#selectedTeam').val() || [],
            subject: $('#subject').val(),
            message: $('#message').val(),
            priority: $('#priority').val(),
            send_copy: $('#sendCopy').is(':checked') ? '1' : '0',
            attachments: JSON.stringify(attachments)
        };
        
        // Show progress modal
        $('#progressModal').modal('show');
        updateProgress(0, 'Preparing to send...');
        
        $.ajax({
            url: 'ajax/send_notification.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                updateProgress(100, 'Complete!');
                
                setTimeout(function() {
                    $('#progressModal').modal('hide');
                    
                    if (response.success) {
                        showSuccessModal(response);
                    } else {
                        alert('Error: ' + (response.message || 'Unknown error occurred'));
                    }
                }, 1000);
            },
            error: function(xhr, status, error) {
                $('#progressModal').modal('hide');
                alert('Error sending notification: ' + error);
            }
        });
    }

    function updateProgress(percent, text) {
        $('#progressBar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#progressText').text(text);
    }

    function showSuccessModal(response) {
        let message = `
            <div class="alert alert-success">
                <h4><i class="fa fa-check-circle"></i> Notification sent successfully!</h4>
                <p><strong>Recipients:</strong> ${response.recipient_count} emails sent</p>
                <p><strong>Subject:</strong> ${response.subject}</p>
                ${response.attachments > 0 ? `<p><strong>Attachments:</strong> ${response.attachments} files</p>` : ''}
                <p><strong>Sent at:</strong> ${new Date().toLocaleString()}</p>
            </div>
        `;
        
        if (response.failed_emails && response.failed_emails.length > 0) {
            message += `
                <div class="alert alert-warning">
                    <h5><i class="fa fa-exclamation-triangle"></i> Some emails failed to send:</h5>
                    <ul>
                        ${response.failed_emails.map(email => `<li>${email}</li>`).join('')}
                    </ul>
                </div>
            `;
        }
        
        $('#successMessage').html(message);
        $('#successModal').modal('show');
    }

    function saveDraft() {
        const draftData = {
            recipient_type: $('input[name="recipient_type"]:checked').val(),
            selected_clients: $('#selectedClients').val() || [],
            selected_team: $('#selectedTeam').val() || [],
            subject: $('#subject').val(),
            message: $('#message').val(),
            priority: $('#priority').val(),
            send_copy: $('#sendCopy').is(':checked')
        };
        
        $.ajax({
            url: 'ajax/save_notification_draft.php',
            type: 'POST',
            data: draftData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Draft saved successfully!');
                } else {
                    alert('Error saving draft: ' + response.message);
                }
            },
            error: function() {
                alert('Error saving draft.');
            }
        });
    }
});
