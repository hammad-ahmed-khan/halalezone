<?php
if (!defined('__HQC__')) {
    exit;
} // Prevent direct access
if ($reminders = $amdb->get_results("SELECT hqc_default_invoice_reminders.*,companies.company_name,companies.clid FROM `hqc_default_invoice_reminders` RIGHT JOIN companies ON hqc_default_invoice_reminders.clid = companies.clid WHERE companies.active = 'y' AND companies.offid = '0' AND companies.clof = '0' ORDER BY TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC")) {

    $office = $amdb->get_row("SELECT offid,office_name,reference_prefix,certificate_prefix FROM offices WHERE offid = '0'");
?>
    <style>
        .status,
        .center {
            text-align: center;
        }

        input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }
    </style>
    <script>
        function changeReminderStatus(obj) {
            var clid = $(obj).closest('tr').data('clid');
            var status = $(obj).hasClass('fa-toggle-on') ? 'off' : 'on';
            $.ajax({
                url: 'reminder_save.php',
                type: 'POST',
                data: {
                    act: 'changeReminderStatus',
                    clid: clid,
                    status: status
                },
                success: function(data) {
                    if (data == 'ok') {
                        $('.com_' + clid).find('.status').html('<i class="fas fa-toggle-' + status + '"></i>');
                        alert_message('Status changed successfully');
                    } else {
                        alert_message(data);
                    }
                }
            });
        }

        function checkAllCheckboxes(obj) {
            // Check or uncheck all checkboxes based on visibility
            var checkboxes = document.querySelectorAll('.clidCheckbox');
            checkboxes.forEach(function(checkbox) {
                if (checkbox.closest('tr').style.display !== 'none') {
                    checkbox.checked = obj.checked;
                }
            });
        }

        async function changeBulkReminderStatus(obj) {
            var status = $(obj).hasClass('fa-toggle-on') ? 'on' : 'off';
            var clids = [];
            $('.clidCheckbox:checked').each(function() {
                clids.push($(this).val());
            });
            if (clids.length == 0) {
                alert_message('Please select companies to edit');
                return;
            }
            await working_alert();
            $.ajax({
                url: 'reminder_save.php',
                type: 'POST',
                data: {
                    act: 'changeBulkReminderStatus',
                    clids: clids,
                    status: status
                },
                success: function(data) {
                    if (data == 'ok') {
                        $('.clidCheckbox:checked').each(function() {
                            var clid = $(this).val();
                            $('.com_' + clid).find('.status').html('<i class="fas fa-toggle-' + status + '"></i>');
                        });
                        close_alert();
                    } else {
                        alert_message(data);
                    }
                }
            });
        }

        function massEdit() {
            var clids = [];
            $('.clidCheckbox:checked').each(function() {
                clids.push($(this).val());
            });
            if (clids.length == 0) {
                alert_message('Please select companies to edit');
                return;
            }
            var url = 'reminder_edit.php?act=massEdit&clid=*';
            doLoadPopup(url, '', 'Update selected companies');
        }

        function searchClients(obj) {
            var search = obj.toLowerCase();
            var rows = document.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                var companyName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                if (companyName.includes(search)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    <h2 style="text-align:center">Invoice default payment terms & reminders</h2>
        <table class="alternateOn" style="width:90%">
            <thead>
                <tr>
                    <th>Nr</th>
                    <th><input type="checkbox" onclick="checkAllCheckboxes(this)" /></th>
                    <th>Company <input type="search" style="height:24px;margin-left:20px;" placeholder="Search companies" name="searchClients" onkeyup="searchClients(this.value)" /></th>
                    <th>Term/First reminder</th>
                    <th>Second reminder</th>
                    <th>Telephone call</th>
                    <th colspan="2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $first_reminder = 21;
                $second_reminder = 7;
                $telephone_call = 7;
                $i = 1;
                foreach ($reminders as $reminder) {
                    $status = $reminder['status'] != '' ? $reminder['status'] : 'off';
                ?>
                    <tr data-clid="<?php echo $reminder['clid']; ?>" class="com_<?php echo $reminder['clid']; ?>">
                        <td><?php echo $i; ?></td>
                        <td style="text-align:center">
                            <input type="checkbox" class="clidCheckbox" name="clid[]" value="<?php echo $reminder['clid']; ?>">
                        </td>
                        <td>
                            <span style="cursor:pointer;white-space: normal !important;max-width:350px" class="com com_<?php echo $reminder['clid']; ?> clid load_popup" data-url="../../admin/load_company.php?clid=<?php echo $reminder['clid']; ?><?php echo (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) ? '&uid=' . $_SESSION['user']['uid'] : ''; ?>" title="<?php echo str_replace('"', '&quot;', $reminder['company_name']); ?>"><?php echo $office['reference_prefix']; ?><?php echo $office['certificate_prefix']; ?><?php echo str_pad($reminder['clid'], 6, '0', STR_PAD_LEFT); ?></span>
                            <?php echo $reminder['company_name']; ?>
                        </td>
                        <td class="center"><?php echo $reminder['first_reminder'] != '' ? $reminder['first_reminder'] : $first_reminder; ?> Days</td>
                        <td class="center"><?php echo $reminder['second_reminder'] != '' ? $reminder['second_reminder'] : $second_reminder; ?> Days</td>
                        <td class="center"><?php echo $reminder['telephone_call'] != '' ? $reminder['telephone_call'] : $telephone_call; ?> Days</td>
                        <td class="action">
                            <i class="fas fa-edit load_popup" data-url="reminder_edit.php?act=edit&clid=<?php echo $reminder['clid']; ?>" title=" Edit Reminder"></i>
                        </td>
                        <td class="status">
                            <i class="fas fa-toggle-<?php echo $status; ?>" onclick="changeReminderStatus(this)"></i>
                        </td>
                    </tr>
                <?php
                    $i++;
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Nr</th>
                    <th><input type="checkbox" onclick="checkAllCheckboxes(this)" /></th>
                    <th>Company</th>
                    <th>First reminder</th>
                    <th>Second reminder</th>
                    <th>Telephone call</th>
                    <th colspan="2">Action</th>
                </tr>
            </tfoot>
        </table>
        <div style="width:90%;margin:0 auto">Set selected companies <i class="fas fa-toggle-on" onclick="changeBulkReminderStatus(this)"><span>On</span></i> <i class="fas fa-toggle-off" onclick="changeBulkReminderStatus(this)"><span>Off</span></i> <i class="far fa-edit" onclick="massEdit()"><span>Edit selected</span></i> </div>
    <?php
    //    print_r($reminders);
}
    ?>