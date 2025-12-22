<div style="text-align:center; padding: 10px; font-size: 14px; color: #666;">
    <form method="post" action="upload_clients_save.php" enctype="multipart/form-data">
        <input type="hidden" name="act" value="upload_clients">
        <h2>Upload Clients from Excel</h2>
        <p>Please upload an Excel file containing client data. The file should be in .xls or .xlsx format.</p>
        <input type="file" name="excelFile" accept=".xls, .xlsx" required>
        <input type="submit" value="Upload Clients">
    </form>
    please use the <a href="/data/templates/Customer_list_IIDC_bulkupload.xlsx" target="_blank">template</a> for the Excel file.
    <p>Ensure that the file contains the necessary columns for client data, such as Name, Email, Phone, etc. Please do not change the position of columns.</p>
</div>