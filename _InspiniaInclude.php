<?session_start(); 
$managerSession = $_SESSION["managerSession"];
$protocol  = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
$path=$protocol.getenv('HTTP_HOST')."/eliveui/";?>

<!-- Mainly CSS --> 
    <link href="<?echo$path?>inspinia/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?echo$path?>inspinia/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?echo$path?>inspinia/css/animate.css" rel="stylesheet">
    <link href="<?echo$path?>inspinia/css/style.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?echo$path?>css/jquery.datetimepicker.css"/>  
<!-- Toastr style -->
    <link href="<?echo$path?>inspinia/css/plugins/toastr/toastr.min.css" rel="stylesheet">

<!-- Mainly scripts -->
    <script src="<?echo$path?>inspinia/js/jquery-2.1.1.js"></script>      
    <script src="<?echo$path?>inspinia/js/bootstrap.min.js"></script>
    <script src="<?echo$path?>inspinia/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="<?echo$path?>inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="<?echo$path?>inspinia/js/inspinia.js"></script>
    <script src="<?echo$path?>inspinia/js/plugins/pace/pace.min.js"></script>
<!-- Toastr script -->
    <script src="<?echo$path?>inspinia/js/plugins/toastr/toastr.min.js"></script>
 <!-- BootBox -->
    <script src="<?echo$path?>inspinia/js/plugins/bootBox/bootbox.min.js"></script> 
 <!-- DatePicker --> 
    <script src="<?echo$path?>js/jquery.datetimepicker.full.min.js"></script>

    
    <script language="javascript">
        function showNotification($msg){
            toastr.options = {
                  "closeButton": true,
                  "debug": false,
                  "progressBar": true,
                  "positionClass": "toast-top-center",
                  "onclick": null,
                  "showDuration": "400",
                  "hideDuration": "1000",
                  "timeOut": "7000",
                  "extendedTimeOut": "1000",
                  "showEasing": "swing",
                  "hideEasing": "linear",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
            }
            toastr.error($msg);
        }
    </script>