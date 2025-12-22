<?php if (!defined("_HQC_")) {
    exit();
}; ?>
<?php if (isset($_SESSION['offid']) && $_SESSION['offid'] != '0') {
    $_GET['offid'] = $_SESSION['offid'];
};
?>
<style>
    td.status {
        /* white-space: nowrap */
    }

    td.status b {
        display: inline-block;
        width: 100px;
        white-space: nowrap
    }

    #searchAnnualCertificates select {
        margin: 5px 0px
    }

    .load_popup {
        cursor: pointer
    }

    .load_popup:hover {
        color: red
    }

    fieldset {
        background: lightyellow;
        border: 1px solid darkgrey;
        border-radius: 5px;
    }

    .remarks {
        position: relative;
        background: lightyellow;
        border: 1px solid darkgrey;
        min-height: 20px;
        padding: 10px;
    }

    i.fas.fa-paperclip {
        color: darkorange !important;
    }

    .remarks i.fas.fa-paperclip {
        right: 0px !important;
        top: -10px;
        position: absolute;
        color: blue;
    }

    .remarks i.fa.fa-trash-alt {
        right: 5px !important;
        bottom: 5px;
        position: absolute;
        color: red;
        font-size: 12px !important;
    }

    #halal_standards {
        display: none;
    }
</style>
<script type="text/javascript">
    $("#page_title").html("Annual Certificate");

    function deleteCert(crtNr) {
        if (confirm("Are you sure Delete selected Certificate") == 1) {
            var time = new Date().getTime();
            $.post("/certificates/annual/certificate_save.php?tm=" + time, {
                    act: "delete",
                    crtNr: crtNr
                },
                function(data) {
                    if (data != "") {
                        if (data.indexOf('success') > -1) {
                            document.location = document.location.href
                        } else {
                            alert(data);
                        }
                    }
                });
        }
    }

    function document_ready() {
        $(".crtDocNr").css({
            "cursor": "pointer"
        });
        $('#certificatesA tr').bind('mouseenter', function() {
            $('#crtId').val($(this).attr('data-crtNr'));
        });
        var selectedCrtNr = null;
        $('#certificatesA .crtDocNr').bind('click', function() {
            $('#crtDocNr').val($(this).attr('data-crtDocNr'));
            $('#crtDocNr').css({
                "width": $(this).width()
            });
            var position = $(this).position();
            $("#fixDocNrDiv").css({
                "left": position.left + "px",
                "top": position.top + "px",
                "display": "block"
            });
            jQuery("#crtDocNr").focus()
            selectedCrtNr = jQuery(this)
        });
    }
    var itemNr
    var clid;
    var st = -1;
    var orderBy = 'certificate_nr';
    var ascDsc = 'DESC';
    var srearchQ = '';
    var searchField = '';
    //async function

    async function deleteMemo(crtNr) {
        await confirm_message('Remove memo?');
        jQuery.post('/certificates/annual/certificate_save.php', {
            act: 'delete_memo',
            crtNr: crtNr
        }).done(function(data) {
            jQuery("#remark_" + crtNr).parent("div").remove();
        })
    }

    function loadCertificates(newLoad) {
        if (typeof newLoad != 'undefined') {
            st++;
        }
        loading = '<tr id="certificateItemsLoading"><td colspan="12" style="text-align:center;vertical-align:middle;"><img src="<?php echo $prog_www; ?>/images/loading.gif" style="height:50px;" /></td></tr>';
        jQuery("#certificateItems").html(loading);
        jQuery.post('<?php echo $prog_www; ?>/certificates/annual/load_certificates.php', jQuery("#searchAnnualCertificates").serialize()).done(function(data) {
            if (data.trim().length > 0) {
                if (jQuery("#certificateItemsLoading")) {
                    jQuery("#certificateItemsLoading").remove();
                }
                if (data.toLowerCase().indexOf("error:") > -1) {
                    jQuery("#certificateItems").html('<tr><td colspan="7" style="text-align:center;color:red">' + data + '</td></tr>')
                } else {
                    if (newLoad != '')
                        jQuery("#certificateItems").html(data);
                    else
                        jQuery("#certificateItems").append(data);

                    var trs = jQuery("#annualCertificatesInProcess").find("tbody tr").length;
                    if (trs > 1) {
                        jQuery("#annualCertificatesInProcess").find("thead").find('.sub_title').prepend('<span class="totalCerts" style="margin-top:10px">' + trs + '</span>');
                    } else {
                       // jQuery("#annualCertificatesInProcess").find("thead tr th span.showRows").replaceWith('::');
                    }
                    post_links();
                    do_document_ready();
                    load_popup();
                }
            }
        });
        return false;
    }

    function searchInvoicedCertificates() {
        srearchQ = '';
        searchField = '';
        if (jQuery("#srearchQ").val().trim() != '') {
            srearchQ = jQuery("#srearchQ").val();
            searchField = jQuery("#searchField").val()
            loadCertificates('yes');
        }
        return false;
    }

    function showSearchInputs(val) {
        if (val == 'all_certificates')
            jQuery("#loadLimit").val('12');
        else
            jQuery("#loadLimit").val('all');

        jQuery("#searchAnnualCertificates input[type='text'],#halal_standards").val('')
        jQuery("#searchAnnualCertificates span,#halal_standards,#showSearchInputs").css("display", "none")
        jQuery("#searchAnnualCertificates input[type='submit']").css("display", "none")

        if (val == 'crtNr' || val == 'company_name') {
            jQuery("#searchAnnualCertificates span#srearchQ").css("display", "inline-block")
            jQuery("#searchAnnualCertificates input[type='submit']").css("display", "inline-block")
        }
        if (val == 'date_of_expiry' || val == 'order_date') {
            jQuery("#searchAnnualCertificates span#srearchDates").css("display", "inline-block")
            jQuery("#searchAnnualCertificates input[type='submit']").css("display", "inline-block")
        }
        if (val == 'reference_standards') {
            jQuery("#halal_standards").css("display", "inline-block");

            //check if halal_standards has onchange property
            if (jQuery("#halal_standards").attr('onchange') == undefined)
                jQuery("#halal_standards").attr("onchange", 'loadCertificates()');
        }
        if (val == '' || val == 'new_certificates' || val == 'all_expired' || val == 'all_certificates' || val == 'certificates_not_sent')
            loadCertificates('yes')
    }

    //TODO add export to excel to new system
    function exportCertificates() {
        certificates = [];
        jQuery("#annualCertificates tr").each(function(index) {
            if (index > 0 && index != 2 && jQuery(this).is(":visible")) {
                row = [];
                jQuery(this).find("th,td").each(function(count) {
                    if (count < 5) {
                        item = '<item>' + jQuery(this).html().replace(/\t/g, '').replace(/<br>/g, "\n").replace(/\n\n/g, "\n") + '</item>';
                        row.push(jQuery(item).text().trim());
                    }
                })
                certificates.push(row);
            }
        })
        expItems = JSON.stringify(certificates);
        var form = document.createElement("form");
        form.setAttribute("method", "post");
        form.setAttribute("action", "export_excel_file.php");
        var itemInput = document.createElement("input");
        itemInput.setAttribute("type", "hidden");
        itemInput.setAttribute("name", "items");
        itemInput.setAttribute("value", expItems);
        form.appendChild(itemInput);
        document.body.appendChild(form);
        form.submit();
        form.remove();
    }

    function meetingEmailResults(crtNr) {
        jQuery("#status_" + crtNr).html("Verification process started");
    }
</script>

<table class="alternateOn" style="min-width:100% !important" id="annualCertificatesInProcess">
    <thead>
        <tr class="alternateOff">
            <th colspan=7 style="padding:0px 10px;" title="CERTIFICATES In process" class="sub_title">
                <span style="float: left;margin-top:10px"> Annual <FONT COLOR=RED>CERTIFICATES </FONT> In process</span>
                <span style="float:right">
                    <form id="searchAnnualCertificates" onsubmit="loadCertificates('yes');return false">
                        <input type="hidden" id="searchField" name="searchField" value="new_certificates" />
                        <input type="hidden" id="referer" name="referer" value="home" />
                        Get certificates for the:
                        <select name="limit" id="loadLimit" size="1" onchange="loadCertificates();">
                            <option value="3">Last 3 months</option>
                            <option value="6">Last 6 months</option>
                            <option value="12" selected>Last 12 months</option>
                            <option value="18">Last 18 months</option>
                            <option value="all">ALL Certificates</option>
                        </select>
                    </form>
                </span>

            </th>
        </tr>
        <tr id="headerTh">
            <th style="width:20px;"><span class="showRows" onclick="showRows(this)"><i class="far fa-caret-square-down"></i></span></th>
            <th data-id="company_name">Company / Country / City</th>
            <th id="thDates" data-id="issue_expiry">Issue / Expiry</th>
            <th data-id="ordered_on" style="width:200px">Certificate Request</th>
            <th id="thStatus" data-id="status" style="width:200px">Certificate Status</th>
            <th id="thAction" data-id="action" style="width:90px">Action</th>
        </tr>
    </thead>
    <tbody id="certificateItems">
        <tr id="certificateItemsLoading">
            <td colspan="12" style="text-align:center;vertical-align:middle;"><img src="<?php echo $prog_www; ?>/images/loading.gif" style="height:50px;" /></td>
        </tr>
    </tbody>
</table>
<script>
    loadCertificates('yes');
</script>