<?php
if (!isset($_GET['file'])) {
    exit();
}

function clean($string)
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\.\-]/', '', $string); // Removes special chars.
    $string = str_replace('-', ' ', $string); // Replaces all spaces with hyphens.
    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

//function to view file using Google Docs Viewer
$file = $_GET['file'];

$ext = pathinfo($file, PATHINFO_EXTENSION);
$ext = strtolower($ext);
$ext = trim($ext);
$ext = str_replace('.', '', $ext);

if ($ext == 'pdf' || $ext == 'doc' || $ext == 'docx' || $ext == 'xls' || $ext == 'xlsx' || $ext == 'ppt' || $ext == 'pptx' || $ext == 'txt') {
?>
    <html>

    <head>
        <title><?php echo clean($file); ?></title>
        <!--load jQuery-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script>
            $(document).ready(function() {
                //an interval to check if the iframe is loaded or not
                //if not loaded reload the iframe
                var interval = setInterval(function() {
                    if ($('iframe').contents().find('body').html() == "") {
                        $('iframe').attr('src', $('iframe').attr('src'));
                    }
                }, 1000);
            });
        </script>
    </head>

    <body style="margin:0px;padding:0px;overflow:hidden">
        <iframe src='https://docs.google.com/viewer?url=https://hqc.halaloffice.com/data/DMC/documents/<?php echo $file; ?>&embedded=true' style='position:fixed; width:100%; height:100%;' frameborder='0'></iframe>
    </body>

    </html>
<?php } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
    echo "<div style='text-align:center;padding:50px'><img src='https://hqc.halaloffice.com/data/DMC/documents/" . $file . "' style='max-width:100%;max-height:100%;' /></div>";
} else {
    echo "<div style='text-align:center;padding:50px'><h3>File type not supported!</h3></div>";
}
