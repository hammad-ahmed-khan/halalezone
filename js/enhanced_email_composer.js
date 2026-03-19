/**
 * Enhanced Email Composer JavaScript
 * Handles rich HTML email composition with WYSIWYG editor, templates, and preview
 */

var EmailComposer = {
  editor: null,
  currentMode: "visual",
  templates: {},

  init: function () {
    this.initializeEditor();
    this.bindEvents();
    this.initializeChosenSelects();
    this.updateRecipientCount();
    this.initializeFileUpload();
    this.loadTemplates();
  },

  initializeEditor: function () {
    // Initialize Summernote WYSIWYG editor
    $("#messageVisual").summernote({
      height: 350,
      minHeight: null,
      maxHeight: null,
      focus: true,
      placeholder: "Start composing your email content here...",
      toolbar: [
        ["style", ["style"]],
        ["font", ["bold", "underline", "clear"]],
        ["fontname", ["fontname"]],
        ["fontsize", ["fontsize"]],
        ["color", ["color"]],
        ["para", ["ul", "ol", "paragraph"]],
        ["table", ["table"]],
        ["insert", ["link", "hr"]],
        ["view", ["fullscreen", "codeview"]],
      ],
      styleTags: ["p", "blockquote", "pre", "h1", "h2", "h3", "h4", "h5", "h6"],
      fontSizes: [
        "8",
        "9",
        "10",
        "11",
        "12",
        "14",
        "16",
        "18",
        "20",
        "24",
        "36",
        "48",
      ],
      callbacks: {
        onChange: function (contents, $editable) {
          EmailComposer.syncEditors("visual");
        },
        onImageUpload: function (files) {
          EmailComposer.handleImageUpload(files);
        },
      },
    });

    this.editor = $("#messageVisual").summernote("editor");
  },

  bindEvents: function () {
    // Editor mode switching
    $('input[name="editor_mode"]').change(function () {
      EmailComposer.switchEditorMode($(this).val());
    });

    // Template events
    $("#loadTemplateBtn").click(function () {
      EmailComposer.loadSelectedTemplate();
    });

    $("#previewTemplateBtn").click(function () {
      EmailComposer.previewSelectedTemplate();
    });

    $("#saveTemplateBtn").click(function () {
      $("#saveTemplateModal").modal("show");
    });

    // Variable insertion
    $(".variable-tag").click(function (e) {
      e.preventDefault();
      EmailComposer.insertVariable($(this).data("variable"));
    });

    // Recipient type changes
    $('input[name="recipient_type"]').change(function () {
      EmailComposer.handleRecipientTypeChange();
    });

    // Preview buttons
    $("#previewDesktopBtn").click(function () {
      EmailComposer.showPreview("desktop");
    });

    $("#previewMobileBtn").click(function () {
      EmailComposer.showPreview("mobile");
    });

    // Send time change
    $("#sendTime").change(function () {
      EmailComposer.handleSendTimeChange();
    });

    // Form submission
    $("#emailComposerForm").submit(function (e) {
      e.preventDefault();
      EmailComposer.handleFormSubmission();
    });

    // Test email
    $("#testEmailBtn").click(function () {
      $("#testEmailModal").modal("show");
    });

    // Save draft
    $("#saveDraftBtn").click(function () {
      EmailComposer.saveDraft();
    });

    // HTML code editors
    $("#messageCode, #messageCodeSplit").on("input", function () {
      EmailComposer.syncEditors("code");
    });
  },

  switchEditorMode: function (mode) {
    this.currentMode = mode;

    // Hide all editor divs
    $("#visualEditorDiv, #codeEditorDiv, #splitEditorDiv").hide();

    // Sync content before switching
    this.syncContent();

    switch (mode) {
      case "visual":
        $("#visualEditorDiv").show();
        break;
      case "code":
        $("#codeEditorDiv").show();
        break;
      case "split":
        $("#splitEditorDiv").show();
        this.updateLivePreview();
        break;
    }
  },

  syncEditors: function (sourceMode) {
    var content = "";

    if (sourceMode === "visual") {
      content = $("#messageVisual").summernote("code");
      $("#messageCode, #messageCodeSplit").val(content);
    } else if (sourceMode === "code") {
      if ($("#messageCode").is(":visible")) {
        content = $("#messageCode").val();
      } else {
        content = $("#messageCodeSplit").val();
      }
      $("#messageVisual").summernote("code", content);
    }

    if (this.currentMode === "split") {
      this.updateLivePreview();
    }
  },

  syncContent: function () {
    var content = "";

    switch (this.currentMode) {
      case "visual":
        content = $("#messageVisual").summernote("code");
        break;
      case "code":
        content = $("#messageCode").val();
        break;
      case "split":
        content = $("#messageCodeSplit").val();
        break;
    }

    // Update all editors with the current content
    $("#messageVisual").summernote("code", content);
    $("#messageCode, #messageCodeSplit").val(content);
  },

  updateLivePreview: function () {
    var content = $("#messageCodeSplit").val();
    var styledContent = this.wrapEmailForPreview(content);
    $("#livePreview").html(styledContent);
  },

  insertVariable: function (variable) {
    var variableHTML =
      '<span style="background-color: #e7f3ff; color: #0066cc; padding: 2px 4px; border-radius: 3px; font-weight: bold;">' +
      variable +
      "</span>";

    switch (this.currentMode) {
      case "visual":
        $("#messageVisual").summernote("pasteHTML", variableHTML);
        break;
      case "code":
        var textarea = $("#messageCode")[0];
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var text = textarea.value;
        textarea.value =
          text.substring(0, start) +
          variable +
          text.substring(end, text.length);
        break;
      case "split":
        var textarea = $("#messageCodeSplit")[0];
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var text = textarea.value;
        textarea.value =
          text.substring(0, start) +
          variable +
          text.substring(end, text.length);
        this.updateLivePreview();
        break;
    }
  },

  loadTemplates: function () {
    $.ajax({
      url: "ajax/get_email_templates.php",
      type: "GET",
      dataType: "json",
      success: function (data) {
        EmailComposer.templates = data;
      },
      error: function () {
        console.log("Failed to load templates");
      },
    });
  },

  loadSelectedTemplate: function () {
    var selectedTemplate = $("#emailTemplate").val();

    if (!selectedTemplate) {
      alert("Please select a template first.");
      return;
    }

    if (
      selectedTemplate === "basic" ||
      selectedTemplate === "announcement" ||
      selectedTemplate === "newsletter"
    ) {
      this.loadBuiltInTemplate(selectedTemplate);
    } else {
      this.loadCustomTemplate(selectedTemplate);
    }
  },

  loadBuiltInTemplate: function (templateType) {
    var templates = {
      basic: {
        subject: "Important Update from {{COMPANY_NAME}}",
        content: `
                <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
                    <div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-bottom: 3px solid #007bff;">
                        <h1 style="color: #333; margin: 0;">{{COMPANY_NAME}}</h1>
                    </div>
                    <div style="padding: 30px; background-color: #ffffff;">
                        <h2 style="color: #007bff;">Hello {{CLIENT_NAME}},</h2>
                        <p style="font-size: 16px; line-height: 1.6; color: #555;">
                            This is your personalized message content. You can edit this to include your specific information.
                        </p>
                        <div style="margin: 20px 0; padding: 15px; background-color: #e9ecef; border-left: 4px solid #007bff;">
                            <p style="margin: 0; font-weight: bold; color: #333;">
                                Important: Add your main message content here.
                            </p>
                        </div>
                        <p style="font-size: 16px; line-height: 1.6; color: #555;">
                            Thank you for your continued partnership.
                        </p>
                        <p style="font-size: 16px; line-height: 1.6; color: #555;">
                            Best regards,<br>
                            {{SENDER_NAME}}<br>
                            {{COMPANY_NAME}}
                        </p>
                    </div>
                    <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666;">
                        <p>© {{DATE}} {{COMPANY_NAME}}. All rights reserved.</p>
                        <p><a href="{{UNSUBSCRIBE_LINK}}" style="color: #007bff;">Unsubscribe</a></p>
                    </div>
                </div>`,
      },
      announcement: {
        subject: "Important Announcement - {{COMPANY_NAME}}",
        content: `
                <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
                        <h1 style="color: white; margin: 0; font-size: 28px;">📢 Important Announcement</h1>
                    </div>
                    <div style="padding: 30px; background-color: #ffffff;">
                        <h2 style="color: #333;">Dear {{CLIENT_NAME}},</h2>
                        <p style="font-size: 18px; line-height: 1.6; color: #555; font-weight: bold;">
                            We have an important announcement to share with you.
                        </p>
                        <div style="margin: 25px 0; padding: 20px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
                            <h3 style="color: #856404; margin-top: 0;">What's New?</h3>
                            <p style="color: #856404; margin-bottom: 0;">
                                [Add your announcement details here]
                            </p>
                        </div>
                        <p style="font-size: 16px; line-height: 1.6; color: #555;">
                            If you have any questions, please don't hesitate to contact us.
                        </p>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="#" style="background-color: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">Learn More</a>
                        </div>
                    </div>
                    <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666;">
                        <p>{{COMPANY_NAME}} | {{DATE}}</p>
                    </div>
                </div>`,
      },
      newsletter: {
        subject: "{{COMPANY_NAME}} Newsletter - {{DATE}}",
        content: `
                <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
                    <div style="background-color: #2c3e50; padding: 20px; text-align: center;">
                        <h1 style="color: white; margin: 0;">{{COMPANY_NAME}} Newsletter</h1>
                        <p style="color: #ecf0f1; margin: 5px 0 0 0;">{{DATE}}</p>
                    </div>
                    <div style="padding: 30px; background-color: #ffffff;">
                        <h2 style="color: #2c3e50;">Hello {{CLIENT_NAME}},</h2>
                        
                        <div style="margin: 25px 0;">
                            <h3 style="color: #3498db; border-bottom: 2px solid #3498db; padding-bottom: 5px;">📰 Latest Updates</h3>
                            <p style="font-size: 16px; line-height: 1.6; color: #555;">
                                [Add your latest news and updates here]
                            </p>
                        </div>
                        
                        <div style="margin: 25px 0;">
                            <h3 style="color: #e74c3c; border-bottom: 2px solid #e74c3c; padding-bottom: 5px;">🎯 Featured Content</h3>
                            <p style="font-size: 16px; line-height: 1.6; color: #555;">
                                [Highlight your most important content]
                            </p>
                        </div>
                        
                        <div style="margin: 25px 0;">
                            <h3 style="color: #27ae60; border-bottom: 2px solid #27ae60; padding-bottom: 5px;">💡 Tips & Insights</h3>
                            <ul style="font-size: 16px; line-height: 1.6; color: #555;">
                                <li>Tip 1: [Add your tip here]</li>
                                <li>Tip 2: [Add your tip here]</li>
                                <li>Tip 3: [Add your tip here]</li>
                            </ul>
                        </div>
                    </div>
                    <div style="background-color: #2c3e50; padding: 20px; text-align: center;">
                        <p style="color: #ecf0f1; margin: 0;">Thank you for reading our newsletter!</p>
                        <p style="color: #bdc3c7; font-size: 12px; margin: 10px 0 0 0;">
                            <a href="{{UNSUBSCRIBE_LINK}}" style="color: #3498db;">Unsubscribe</a> | 
                            © {{COMPANY_NAME}} {{DATE}}
                        </p>
                    </div>
                </div>`,
      },
    };

    var template = templates[templateType];
    if (template) {
      $("#subject").val(template.subject);
      $("#messageVisual").summernote("code", template.content);
      $("#messageCode, #messageCodeSplit").val(template.content);

      if (this.currentMode === "split") {
        this.updateLivePreview();
      }
    }
  },

  loadCustomTemplate: function (templateId) {
    $.ajax({
      url: "ajax/get_template_content.php",
      type: "POST",
      data: { template_id: templateId },
      dataType: "json",
      success: function (data) {
        if (data.success) {
          $("#subject").val(data.subject);
          $("#messageVisual").summernote("code", data.content);
          $("#messageCode, #messageCodeSplit").val(data.content);

          if (EmailComposer.currentMode === "split") {
            EmailComposer.updateLivePreview();
          }
        }
      },
      error: function () {
        alert("Failed to load template");
      },
    });
  },

  previewSelectedTemplate: function () {
    var selectedTemplate = $("#emailTemplate").val();

    if (!selectedTemplate) {
      alert("Please select a template first.");
      return;
    }

    var content = "";
    if (
      selectedTemplate === "basic" ||
      selectedTemplate === "announcement" ||
      selectedTemplate === "newsletter"
    ) {
      var templates = this.getBuiltInTemplates();
      content = templates[selectedTemplate].content;
    } else {
      // Load custom template content via AJAX for preview
      this.loadCustomTemplatePreview(selectedTemplate);
      return;
    }

    $("#templatePreview").html(this.wrapEmailForPreview(content)).show();
  },

  showPreview: function (deviceType) {
    this.syncContent();
    var content = $("#messageVisual").summernote("code");
    var subject = $("#subject").val();

    if (!content.trim()) {
      alert("Please add some content to preview.");
      return;
    }

    var wrappedContent = this.wrapEmailForPreview(content);

    if (deviceType === "desktop") {
      $("#desktopPreviewFrame").contents().find("body").html(wrappedContent);
      $("#desktopPreview").show();
      $("#mobilePreview").hide();
    } else {
      $("#mobilePreviewFrame").contents().find("body").html(wrappedContent);
      $("#mobilePreview").show();
      $("#desktopPreview").hide();
    }

    $("#responsivePreview").show();

    // Also show enhanced modal preview
    this.showEnhancedPreview(wrappedContent, subject);
  },

  showEnhancedPreview: function (content, subject) {
    // Set preview content
    var fullEmailHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>${subject || "Email Preview"}</title>
            <style>
                body { margin: 0; padding: 20px; font-family: Arial, sans-serif; background-color: #f5f5f5; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            </style>
        </head>
        <body>
            <div class="email-container">
                ${content}
            </div>
        </body>
        </html>`;

    // Create blob URL for iframe
    var blob = new Blob([fullEmailHTML], { type: "text/html" });
    var url = URL.createObjectURL(blob);

    $("#desktopFrame").attr("src", url);
    $("#mobileFrame").attr("src", url);
    $("#sourceCode").text(fullEmailHTML);

    $("#enhancedPreviewModal").modal("show");
  },

  wrapEmailForPreview: function (content) {
    return `
        <div style="max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            ${content}
        </div>`;
  },

  handleRecipientTypeChange: function () {
    var selectedType = $('input[name="recipient_type"]:checked').val();

    if (selectedType === "specific") {
      $("#specificRecipientsDiv").show();
    } else {
      $("#specificRecipientsDiv").hide();
    }

    this.updateRecipientCount();
  },

  updateRecipientCount: function () {
    var selectedType = $('input[name="recipient_type"]:checked').val();
    var count = 0;
    var countText = "";

    switch (selectedType) {
      case "all_clients":
        count = $(".chosen-select#selectedClients option").length;
        countText = `All Clients (${count} recipients)`;
        break;
      case "all_team":
        count = $(".chosen-select#selectedTeam option").length;
        countText = `All Team Members (${count} recipients)`;
        break;
      case "all_both":
        var clientCount = $(".chosen-select#selectedClients option").length;
        var teamCount = $(".chosen-select#selectedTeam option").length;
        count = clientCount + teamCount;
        countText = `All Recipients (${count} total: ${clientCount} clients + ${teamCount} team)`;
        break;
      case "specific":
        var selectedClients = $("#selectedClients").val() || [];
        var selectedTeam = $("#selectedTeam").val() || [];
        count = selectedClients.length + selectedTeam.length;
        countText = `Selected Recipients (${count} total: ${selectedClients.length} clients + ${selectedTeam.length} team)`;
        break;
      default:
        countText = "Please select recipient type";
    }

    $("#recipientCount").html(`<i class="fa fa-users"></i> ${countText}`);
  },

  handleSendTimeChange: function () {
    var sendTime = $("#sendTime").val();

    if (sendTime === "scheduled") {
      $("#scheduledTimeDiv").show();
      $("#scheduledDateTime").prop("required", true);
    } else {
      $("#scheduledTimeDiv").hide();
      $("#scheduledDateTime").prop("required", false);
    }
  },

  initializeChosenSelects: function () {
    $(".chosen-select").chosen({
      width: "100%",
      placeholder_text_multiple: "Choose recipients...",
      no_results_text: "No recipients found matching",
    });

    // Update count when specific recipients change
    $(".chosen-select").on("change", function () {
      EmailComposer.updateRecipientCount();
    });
  },

  initializeFileUpload: function () {
    // File upload initialization
    $("#fileupload999").fileupload({
      url: "ajax/upload_notification_attachment.php",
      dataType: "json",
      done: function (e, data) {
        if (data.result.success) {
          var fileName = data.result.fileName;
          var fileSize = data.result.fileSize;

          var fileHtml = `
                        <div class="uploaded-file-name" data-file="${fileName}">
                            <span><i class="fa fa-file"></i> ${fileName} (${fileSize})</span>
                            <button type="button" class="btn btn-xs btn-danger" onclick="EmailComposer.removeAttachment('${fileName}')">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>`;

          $("#uladdoc999").append(fileHtml);
        } else {
          $(".alert-string").html(
            '<div class="alert alert-danger">' + data.result.message + "</div>"
          );
        }
      },
      start: function (e) {
        $(".loader").show();
      },
      stop: function (e) {
        $(".loader").hide();
      },
    });
  },

  removeAttachment: function (fileName) {
    $(`[data-file="${fileName}"]`).remove();
  },

  handleImageUpload: function (files) {
    var formData = new FormData();
    formData.append("file", files[0]);

    $.ajax({
      url: "ajax/upload_inline_image.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (data) {
        if (data.success) {
          $("#messageVisual").summernote(
            "insertImage",
            data.imageUrl,
            function ($image) {
              $image.css("max-width", "100%");
            }
          );
        }
      },
      error: function () {
        alert("Failed to upload image");
      },
    });
  },

  saveTemplate: function () {
    var templateName = $("#templateName").val().trim();
    var templateDescription = $("#templateDescription").val().trim();

    if (!templateName) {
      alert("Please enter a template name.");
      return;
    }

    this.syncContent();
    var content = $("#messageVisual").summernote("code");
    var subject = $("#subject").val();

    $.ajax({
      url: "ajax/save_email_template.php",
      type: "POST",
      data: {
        name: templateName,
        description: templateDescription,
        subject: subject,
        content: content,
      },
      dataType: "json",
      success: function (data) {
        if (data.success) {
          alert("Template saved successfully!");
          $("#saveTemplateModal").modal("hide");
          EmailComposer.loadTemplates(); // Refresh templates list
        } else {
          alert("Failed to save template: " + data.message);
        }
      },
      error: function () {
        alert("Failed to save template");
      },
    });
  },

  sendTestEmail: function () {
    var testEmail = $("#testEmailAddress").val().trim();

    if (!testEmail) {
      alert("Please enter a test email address.");
      return;
    }

    this.syncContent();
    var formData = this.getFormData();
    formData.append("test_email", testEmail);
    formData.append("is_test", "1");

    $.ajax({
      url: "ajax/send_notification.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (data) {
        if (data.success) {
          alert("Test email sent successfully!");
          $("#testEmailModal").modal("hide");
        } else {
          alert("Failed to send test email: " + data.message);
        }
      },
      error: function () {
        alert("Failed to send test email");
      },
    });
  },

  saveDraft: function () {
    this.syncContent();
    var formData = this.getFormData();
    formData.append("save_draft", "1");

    $.ajax({
      url: "ajax/save_email_draft.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (data) {
        if (data.success) {
          alert("Draft saved successfully!");
        } else {
          alert("Failed to save draft: " + data.message);
        }
      },
      error: function () {
        alert("Failed to save draft");
      },
    });
  },

  handleFormSubmission: function () {
    if (!this.validateForm()) {
      return;
    }

    this.syncContent();

    $("#progressModal").modal("show");

    var formData = this.getFormData();

    $.ajax({
      url: "ajax/send_notification.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      xhr: function () {
        var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener(
          "progress",
          function (evt) {
            if (evt.lengthComputable) {
              var percentComplete = (evt.loaded / evt.total) * 100;
              $("#progressBar").css("width", percentComplete + "%");
              $("#progressText").text(
                Math.round(percentComplete) + "% complete"
              );
            }
          },
          false
        );
        return xhr;
      },
      success: function (data) {
        $("#progressModal").modal("hide");

        if (data.success) {
          $("#successMessage").html(`
                        <h4><i class="fa fa-check-circle text-success"></i> Email sent successfully!</h4>
                        <p><strong>Recipients:</strong> ${data.recipient_count}</p>
                        <p><strong>Subject:</strong> ${data.subject}</p>
                        <p><strong>Sent at:</strong> ${data.sent_time}</p>
                    `);
          $("#successModal").modal("show");
        } else {
          alert("Failed to send email: " + data.message);
        }
      },
      error: function () {
        $("#progressModal").modal("hide");
        alert("Failed to send email. Please try again.");
      },
    });
  },

  getFormData: function () {
    var formData = new FormData($("#emailComposerForm")[0]);

    // Add editor content
    var content = $("#messageVisual").summernote("code");
    formData.append("message", content);

    // Add attachments
    $(".uploaded-file-name").each(function () {
      formData.append("attachments[]", $(this).data("file"));
    });

    return formData;
  },

  validateForm: function () {
    var subject = $("#subject").val().trim();
    var content = $("#messageVisual").summernote("code").trim();
    var recipientType = $('input[name="recipient_type"]:checked').val();

    if (!subject) {
      alert("Please enter a subject line.");
      return false;
    }

    if (!content || content === "<p><br></p>") {
      alert("Please enter email content.");
      return false;
    }

    if (!recipientType) {
      alert("Please select recipient type.");
      return false;
    }

    if (recipientType === "specific") {
      var selectedClients = $("#selectedClients").val() || [];
      var selectedTeam = $("#selectedTeam").val() || [];

      if (selectedClients.length === 0 && selectedTeam.length === 0) {
        alert("Please select at least one recipient.");
        return false;
      }
    }

    if (!$("#confirmSend").is(":checked")) {
      alert("Please confirm that you want to send this email.");
      return false;
    }

    return true;
  },
};

// Initialize when document is ready
$(document).ready(function () {
  EmailComposer.init();
});
