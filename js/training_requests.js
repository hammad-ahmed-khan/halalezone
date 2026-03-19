var Training = {
  table: null,
  signaturePad: null,
  selectedTrainingId: null,
  isAdmin: false,

  init: function () {
    // Check if user is admin (auditor panel exists)
    this.isAdmin = $("#selectAuditor").length > 0;

    this.initDataTable();
    this.initSignaturePad();
    this.bindEvents();

    if (this.isAdmin) {
      this.initAuditorAssignment();
    }
  },

  initDataTable: function () {
    var self = this;
    this.table = $("#trainingTable").DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "ajax/get_training_requests.php",
        type: "POST",
        error: function (xhr, error, thrown) {
          console.error("DataTables error:", error, thrown);
          toastr.error("Error loading training requests", "Error");
        },
      },
      columns: [
        { data: "id", width: "50px" },
        { data: "company_name" },
        { data: "contact_person" },
        { data: "email_address" },
        { data: "phone_number" },
        {
          data: "num_participants",
          width: "80px",
          className: "text-center",
        },
        {
          data: "training_type",
          width: "80px",
          className: "text-center",
        },
        {
          data: "training_cost",
          width: "100px",
          className: "text-right",
        },
        {
          data: "auditor_name",
          width: "120px",
          className: "text-center",
          render: function (data, type, row) {
            return data || '<span class="text-muted">Not Assigned</span>';
          },
        },
        {
          data: "training_request_form",
          width: "150px",
          className: "text-center",
          orderable: false,
          render: function (data, type, row) {
            return Training.renderFileLinks(data, "Training Request");
          },
        },
        {
          data: "attendance_list",
          width: "150px",
          className: "text-center",
          orderable: false,
          render: function (data, type, row) {
            return Training.renderFileLinks(data, "Attendance");
          },
        },
        {
          data: "customer_feedback_form",
          width: "150px",
          className: "text-center",
          orderable: false,
          render: function (data, type, row) {
            return Training.renderFileLinks(data, "Feedback");
          },
        },
        {
          data: "attendance_certificates",
          width: "150px",
          className: "text-center",
          orderable: false,
          render: function (data, type, row) {
            return Training.renderFileLinks(data, "Certificates");
          },
        },
        {
          data: "created_at",
          width: "120px",
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          width: "140px",
          className: "text-center",
          render: function (data, type, row) {
            return (
              '<button class="btn btn-xs btn-info view-btn" data-id="' +
              row.id +
              '" title="View">' +
              '<i class="ace-icon fa fa-eye"></i></button> ' +
              '<button class="btn btn-xs btn-success edit-btn" data-id="' +
              row.id +
              '" title="Edit">' +
              '<i class="ace-icon fa fa-pencil"></i></button> ' +
              '<button class="btn btn-xs btn-danger delete-btn" data-id="' +
              row.id +
              '" title="Delete">' +
              '<i class="ace-icon fa fa-trash-o"></i></button>'
            );
          },
        },
      ],
      order: [[0, "desc"]],
      pageLength: 25,
      lengthMenu: [
        [10, 25, 50, 100],
        [10, 25, 50, 100],
      ],
      drawCallback: function () {
        // Re-highlight selected row after table redraw
        if (self.selectedTrainingId) {
          $("#trainingTable tbody tr").each(function () {
            var rowData = self.table.row(this).data();
            if (rowData && rowData.id == self.selectedTrainingId) {
              $(this).addClass("selected-row");
            }
          });
        }
      },
    });
  },

  renderFileLinks: function (data, label) {
    if (!data) {
      return '<span class="text-muted">No file</span>';
    }

    try {
      var files = [];
      var parsed = JSON.parse(data);

      // Handle both single object and array of objects
      if (Array.isArray(parsed)) {
        files = parsed;
      } else if (typeof parsed === "object" && parsed !== null) {
        files = [parsed];
      }

      if (files.length === 0) {
        return '<span class="text-muted">No file</span>';
      }

      var links = [];
      files.forEach(function (file, index) {
        if (file && file.name && file.hostUrl) {
          var fileName = file.name;
          // Truncate long filenames
          var displayName =
            fileName.length > 20 ? fileName.substring(0, 17) + "..." : fileName;

          links.push(
            '<a href="' +
              file.hostUrl +
              '" ' +
              'target="_blank" ' +
              'title="' +
              fileName +
              '" ' +
              'class="file-link">' +
              '<i class="ace-icon fa fa-file-pdf-o text-danger"></i> ' +
              displayName +
              "</a>"
          );
        }
      });

      if (links.length === 0) {
        return '<span class="text-muted">No file</span>';
      }

      return links.join("<br>");
    } catch (e) {
      console.error("Error parsing file data:", e, data);
      return '<span class="text-muted">Invalid data</span>';
    }
  },

  initSignaturePad: function () {
    var canvas = document.getElementById("signaturePad");
    if (canvas) {
      // Set canvas size to match container width
      var container = canvas.parentElement;
      canvas.width = container.offsetWidth;
      canvas.height = 120;

      this.signaturePad = new SignaturePad(canvas, {
        backgroundColor: "rgb(255, 255, 255)",
        penColor: "rgb(0, 0, 0)",
      });
    }
  },

  initAuditorAssignment: function () {
    this.loadAuditors();
    this.bindAuditorEvents();
  },

  bindEvents: function () {
    var self = this;

    // Add button
    $("#btnAdd").on("click", function () {
      self.showAddModal();
    });

    // Edit button
    $("#trainingTable").on("click", ".edit-btn", function () {
      var id = $(this).data("id");
      self.showEditModal(id);
    });

    // View button
    $("#trainingTable").on("click", ".view-btn", function () {
      var id = $(this).data("id");
      self.viewTrainingRequest(id);
    });

    // Delete button
    $("#trainingTable").on("click", ".delete-btn", function () {
      var id = $(this).data("id");
      self.deleteTrainingRequest(id);
    });

    // Row selection for admin auditor assignment
    if (this.isAdmin) {
      $("#trainingTable tbody").on("click", "tr", function (e) {
        // Don't select row if clicking on buttons
        if ($(e.target).closest("button, a").length > 0) {
          return;
        }

        var rowData = self.table.row(this).data();
        if (rowData) {
          self.selectTrainingForAuditorAssignment(rowData, $(this));
        }
      });
    }

    // Form submit
    $("#frmTraining").on("submit", function (e) {
      e.preventDefault();
      self.saveTrainingRequest();
    });

    // Clear signature
    $("#clearSignature").on("click", function () {
      self.signaturePad.clear();
    });

    // Auto-populate acceptance company
    $("#company_name").on("blur", function () {
      var companyName = $(this).val();
      if (companyName && !$("#acceptance_company").val()) {
        $("#acceptance_company").val(companyName);
      }
    });

    // Participant tier change
    $('input[name="participantTier"]').on("change", function () {
      var tierValue = $(this).val();
      var tierParts = tierValue.split("|");
      var maxParticipants = parseInt(tierParts[0]);

      $("#num_participants").val(maxParticipants);
    });

    // Scroll detection for sticky footer opacity
    $("#modalTraining .modal-body").on("scroll", function () {
      var scrollTop = $(this).scrollTop();
      var $footer = $("#stickyFooter");

      if (scrollTop > 50) {
        $footer.addClass("scrolled");
      } else {
        $footer.removeClass("scrolled");
      }
    });
  },

  bindAuditorEvents: function () {
    var self = this;

    // Assign auditor button
    $("#btnAssignAuditor").on("click", function () {
      self.assignAuditor();
    });
  },

  selectTrainingForAuditorAssignment: function (rowData, rowElement) {
    // Remove previous selection
    $("#trainingTable tbody tr").removeClass("selected-row");
    rowElement.addClass("selected-row");

    this.selectedTrainingId = rowData.id;
    var displayText =
      "#" +
      rowData.id +
      " - " +
      rowData.company_name +
      " (" +
      rowData.contact_person +
      ")";
    $("#selectedTrainingInfo").val(displayText);
    $("#selectedTrainingId").val(rowData.id);

    // Set current auditor if assigned
    if (rowData.idauditor) {
      $("#selectAuditor").val(rowData.idauditor);
    } else {
      $("#selectAuditor").val("");
    }

    // Enable controls
    $("#selectAuditor").prop("disabled", false);
    $("#btnAssignAuditor").prop("disabled", false);
  },

  loadAuditors: function () {
    var self = this;

    $.ajax({
      url: "ajax/assign_auditor.php",
      type: "POST",
      data: { action: "get_auditors" },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          var select = $("#selectAuditor");
          select
            .empty()
            .append('<option value="">-- Select Auditor --</option>');

          response.auditors.forEach(function (auditor) {
            select.append(
              '<option value="' + auditor.id + '">' + auditor.name + "</option>"
            );
          });
        } else {
          toastr.error("Failed to load auditors: " + response.message);
        }
      },
      error: function () {
        toastr.error("Error loading auditors.");
      },
    });
  },

  assignAuditor: function () {
    var self = this;
    var trainingId = $("#selectedTrainingId").val();
    var auditorId = $("#selectAuditor").val();
    var auditorName = $("#selectAuditor option:selected").text();

    if (!trainingId) {
      toastr.warning("Please select a training request first.");
      return;
    }

    var confirmMessage = auditorId
      ? 'Are you sure you want to assign "' +
        auditorName +
        '" to this training request?'
      : "Are you sure you want to remove the auditor assignment from this training request?";

    if (confirm(confirmMessage)) {
      $.ajax({
        url: "ajax/assign_auditor.php",
        type: "POST",
        data: {
          action: "assign_auditor",
          training_id: trainingId,
          auditor_id: auditorId,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            toastr.success(response.message);
            self.table.ajax.reload(null, false); // Reload table data without losing current page

            // Clear selection
            self.clearAuditorSelection();
          } else {
            toastr.error("Assignment failed: " + response.message);
          }
        },
        error: function () {
          toastr.error("Error assigning auditor.");
        },
      });
    }
  },

  clearAuditorSelection: function () {
    this.selectedTrainingId = null;
    $("#selectedTrainingInfo").val("");
    $("#selectedTrainingId").val("");
    $("#selectAuditor").val("").prop("disabled", true);
    $("#btnAssignAuditor").prop("disabled", true);
    $("#trainingTable tbody tr").removeClass("selected-row");
  },

  showAddModal: function () {
    $("#id").val("");
    $("#frmTraining")[0].reset();
    if (this.signaturePad) {
      this.signaturePad.clear();
    }
    $("#frmTraining #errors").hide();
    $(".title-add").show();
    $(".title-edit").hide();
    $("#modalTraining").modal("show");

    // Reinitialize signature pad after modal is shown
    var self = this;
    setTimeout(function () {
      self.initSignaturePad();
    }, 300);
  },

  showEditModal: function (id) {
    var self = this;
    $("#frmTraining #errors").hide();
    $(".title-add").hide();
    $(".title-edit").show();

    $.ajax({
      url: "ajax/view_training_request.php",
      type: "POST",
      data: { id: id },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $("#modalTraining").modal("show");
          // Wait for modal to be visible before populating
          setTimeout(function () {
            self.initSignaturePad();
            self.populateForm(response.data);
          }, 300);
        } else {
          toastr.error(
            response.message || "Failed to load training request",
            "Error"
          );
        }
      },
      error: function (xhr, status, error) {
        toastr.error(
          "Error loading training request: " + (xhr.responseText || error),
          "Error"
        );
      },
    });
  },

  populateForm: function (data) {
    $("#id").val(data.id);
    $("#company_name").val(data.company_name);
    $("#address").val(data.address);
    $("#contact_person").val(data.contact_person);
    $("#phone_number").val(data.phone_number);
    $("#email_address").val(data.email_address);

    // Languages
    var languages = data.language.split(", ");
    $('input[name="language[]"]').prop("checked", false);
    languages.forEach(function (lang) {
      $('input[name="language[]"][value="' + lang + '"]').prop("checked", true);
    });

    $("#other_language").val(data.other_language || "");
    $("#preferred_date_1").val(data.preferred_date_1 || "");
    $("#preferred_date_2").val(data.preferred_date_2 || "");
    $("#preferred_date_3").val(data.preferred_date_3 || "");
    $("#num_participants").val(data.num_participants);

    // Calculate tier
    var participants = parseInt(data.num_participants);
    var tierValue = "";
    if (participants <= 1) tierValue = "1|499";
    else if (participants <= 3) tierValue = "3|1190";
    else if (participants <= 6) tierValue = "6|1390";
    else tierValue = "10|1690";

    $('input[name="participantTier"][value="' + tierValue + '"]').prop(
      "checked",
      true
    );
    $('input[name="training_type"][value="' + data.training_type + '"]').prop(
      "checked",
      true
    );

    $("#acceptance_company").val(data.acceptance_company);
    $("#acceptance_name_position").val(data.acceptance_name_position);
    $("#acceptance_place_date").val(data.acceptance_place_date);

    // Load signature
    if (data.signature_data && this.signaturePad) {
      this.signaturePad.fromDataURL(data.signature_data);
    }
  },

  saveTrainingRequest: function () {
    var self = this;

    // Clear previous errors
    $("#frmTraining #errors").hide().html("");

    // Collect validation errors
    var errors = [];

    // Validate required fields
    if (!$("#company_name").val().trim()) {
      errors.push("Company name is required");
    }

    if (!$("#address").val().trim()) {
      errors.push("Address is required");
    }

    if (!$("#contact_person").val().trim()) {
      errors.push("Contact person is required");
    }

    if (!$("#phone_number").val().trim()) {
      errors.push("Phone number is required");
    }

    if (!$("#email_address").val().trim()) {
      errors.push("Email address is required");
    }

    // Validate languages
    var languages = [];
    $('#frmTraining input[name="language[]"]:checked').each(function () {
      languages.push($(this).val());
    });

    if (languages.length === 0) {
      errors.push("Please select at least one language");
    }

    if (!$("#preferred_date_1").val()) {
      errors.push("At least one preferred date is required");
    }

    // Validate participant tier
    if (!$('#frmTraining input[name="participantTier"]:checked').length) {
      errors.push("Please select a participant tier");
    }

    if (
      !$("#num_participants").val() ||
      parseInt($("#num_participants").val()) < 1
    ) {
      errors.push("Number of participants is required and must be at least 1");
    }

    // Validate training type
    if (!$('#frmTraining input[name="training_type"]:checked').length) {
      errors.push("Please select a training type");
    }

    if (!$("#acceptance_company").val().trim()) {
      errors.push("Company name in acceptance section is required");
    }

    if (!$("#acceptance_name_position").val().trim()) {
      errors.push("Name and position are required");
    }

    if (!$("#acceptance_place_date").val().trim()) {
      errors.push("Place and date are required");
    }

    // Validate signature
    if (this.signaturePad.isEmpty()) {
      errors.push("Signature is required");
    }

    // Show all validation errors
    if (errors.length > 0) {
      var errorHtml = "<ul>";
      errors.forEach(function (error) {
        errorHtml += "<li>" + error + "</li>";
      });
      errorHtml += "</ul>";
      $("#frmTraining #errors").html(errorHtml).show();
      toastr.error("Please correct the form errors", "Validation Error");
      return false;
    }

    // Get signature data
    var signatureData = this.signaturePad.toDataURL();

    // Get selected tier
    var tierValue = $(
      '#frmTraining input[name="participantTier"]:checked'
    ).val();
    var tierParts = tierValue.split("|");
    var baseCost = parseFloat(tierParts[1]);

    // Calculate actual cost
    var actualParticipants = parseInt($("#num_participants").val());
    var trainingCost = baseCost;

    if (actualParticipants > 10) {
      var extraParticipants = actualParticipants - 10;
      trainingCost = 1690 + extraParticipants * 99;
    }

    // Add other language cost
    if ($("#other_language").val().trim()) {
      trainingCost += 399;
    }

    // Prepare form data
    var formData = {
      id: $("#id").val(),
      company_name: $("#company_name").val(),
      address: $("#address").val(),
      contact_person: $("#contact_person").val(),
      phone_number: $("#phone_number").val(),
      email_address: $("#email_address").val(),
      language: languages.join(", "),
      other_language: $("#other_language").val(),
      preferred_date_1: $("#preferred_date_1").val(),
      preferred_date_2: $("#preferred_date_2").val(),
      preferred_date_3: $("#preferred_date_3").val(),
      num_participants: actualParticipants,
      training_type: $(
        '#frmTraining input[name="training_type"]:checked'
      ).val(),
      training_cost: trainingCost,
      acceptance_company: $("#acceptance_company").val(),
      acceptance_name_position: $("#acceptance_name_position").val(),
      acceptance_place_date: $("#acceptance_place_date").val(),
      signature_data: signatureData,
    };

    // Disable save button
    var $btnSave = $("#btnsave");
    var originalText = $btnSave.html();
    $btnSave
      .prop("disabled", true)
      .html('<i class="ace-icon fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({
      url: "ajax/save_training_request.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $("#modalTraining").modal("hide");
          self.table.ajax.reload();

          // Show success message
          if (formData.id) {
            toastr.success("Training request updated successfully!", "Success");
          } else {
            toastr.success(
              "Training request submitted successfully! Request ID: " +
                response.id,
              "Success"
            );
          }
        } else {
          if (response.errors) {
            var errorHtml = "<ul>";
            if (typeof response.errors === "string") {
              errorHtml += "<li>" + response.errors + "</li>";
              toastr.error(response.errors, "Error");
            } else {
              $.each(response.errors, function (field, message) {
                errorHtml += "<li>" + message + "</li>";
              });
              toastr.error(
                "Please correct the form errors",
                "Validation Error"
              );
            }
            errorHtml += "</ul>";
            $("#frmTraining #errors").html(errorHtml).show();
          } else {
            var errorMsg =
              response.message || "Failed to save training request";
            $("#frmTraining #errors")
              .html("<ul><li>" + errorMsg + "</li></ul>")
              .show();
            toastr.error(errorMsg, "Error");
          }
        }
      },
      error: function (xhr, status, error) {
        var errorMsg = xhr.responseText || error;
        $("#frmTraining #errors")
          .html("<ul><li>Error: " + errorMsg + "</li></ul>")
          .show();
        toastr.error("Error: " + errorMsg, "Server Error");
      },
      complete: function () {
        $btnSave.prop("disabled", false).html(originalText);
      },
    });
  },

  viewTrainingRequest: function (id) {
    $("#modalView").modal("show");
    $("#viewModalBody").html(
      '<div class="text-center"><i class="ace-icon fa fa-spinner fa-spin fa-3x"></i><p>Loading...</p></div>'
    );

    $.ajax({
      url: "ajax/view_training_request.php",
      type: "POST",
      data: { id: id },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          Training.displayTrainingDetails(response.data);
        } else {
          $("#viewModalBody").html(
            '<div class="alert alert-danger">' +
              (response.message || "Failed to load training request details") +
              "</div>"
          );
          toastr.error(
            response.message || "Failed to load training request details",
            "Error"
          );
        }
      },
      error: function (xhr, status, error) {
        $("#viewModalBody").html(
          '<div class="alert alert-danger">Error loading details: ' +
            (xhr.responseText || error) +
            "</div>"
        );
        toastr.error(
          "Error loading details: " + (xhr.responseText || error),
          "Error"
        );
      },
    });
  },

  displayTrainingDetails: function (html) {
    $("#viewModalBody").html(html);
  },

  deleteTrainingRequest: function (id) {
    if (
      !confirm(
        "Are you sure you want to delete this training request?\n\nThis action cannot be undone."
      )
    ) {
      return;
    }

    var self = this;

    $.ajax({
      url: "ajax/delete_training_request.php",
      type: "POST",
      data: { id: id },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          toastr.success("Training request deleted successfully", "Success");
          self.table.ajax.reload();

          // Clear selection if deleted item was selected
          if (self.selectedTrainingId == id) {
            self.clearAuditorSelection();
          }
        } else {
          toastr.error(
            response.message || "Failed to delete training request",
            "Error"
          );
        }
      },
      error: function (xhr, status, error) {
        toastr.error(
          "Error deleting training request: " + (xhr.responseText || error),
          "Error"
        );
      },
    });
  },
};

// Initialize when document is ready
$(document).ready(function () {
  Training.init();
});
