<!DOCTYPE html>
<html>
<head>

<title>NBU Open Data Explorer</title>

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/font-awesome.min.css" rel="stylesheet">
    <style type="text/css">
    	.container {
    		padding: 15px 0 15px 0;
    	}
        .fa-check {
            color: #359768;
        }
        .fa-times {
            color: #900C3F;
        }
        .tab-content {
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 10px;
            background-color: #FCFCFC;
        }

        .nav-tabs {
            margin-bottom: 0;
        }
        div#content {
            display: none;
        }
        i#loading {
            position: fixed;
            color: #FFD460;
            height: 1em;
            width: 1em;
            margin: auto;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }
        .table-modern {
            padding: 10px 50px 10px 50px;
            text-align: center;
        }
    </style>
    <!-- Developed by Artem Rumiantsev -->
    <script src="assets/js/jquery-3.2.1.slim.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/fusioncharts.js"></script>
    <script src="assets/js/themes/fusioncharts.theme.fint.js"></script>
</head>
<body>
<?php
    // implementing preloading spinner
    for ($i = 0; $i < 10; $i++) {
        echo str_repeat(' ', 1024 * 64); // for the buffer to achieve the minimum size in order to flush data
        if ($i == 1)
            echo '<i class="fa fa-spinner fa-spin fa-4x fa-fw" id="loading"></i>';
    }
    // more about output buffering: http://www.php.net/manual/en/book.outcontrol.php
?>
<div id="content" style="display: block;">
    <?php
        sleep(5);
    ?>
     
<br>

<a href="https://github.com/artemworks/"><img style="position: absolute; top: 0; right: 0; border: 0;" src="assets/images/forkme.png" alt="Fork me on GitHub" data-canonical-src="assets/images/forkme.png"></a>
<div class="container">
	<div class="row">