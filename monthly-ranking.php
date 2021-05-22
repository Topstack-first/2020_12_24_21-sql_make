<?php

    require_once './init.php';
    if ( ! $app->is_authenticated()) {
        redirect(APP_URL.'/php_auth_login.php?src=login');
    }
    
    $_title = 'FixedHeader - Datatables - SmartAdmin v4.5.3';
    $_active_nav = 'datatables_fixedheader';
    $_head = '	<link rel="stylesheet" media="screen, print" href="'.ASSETS_URL.'/css/datagrid/datatables/datatables.bundle.css">
    ';
    $_description = 'Create headache free searching, sorting and pagination tables without any complex configuration';


    include_once APP_PATH.'/api/functions.php';

    $month = date('m');

    $rows =  \Models\Prediction::getRecordsInMonth($month);

    $generated_balls = [];
    $selected_balls = [];
    $total_gains = [];
    $total_stakes = [];
    for ($i=0; $i < count($rows); $i++) { 
        $possible_gains = [];

        $generated_balls = [$rows[$i]->l1, $rows[$i]->l2, $rows[$i]->l3,  $rows[$i]->l4,  $rows[$i]->l5,  $rows[$i]->l6,  $rows[$i]->l7,  $rows[$i]->l8,  $rows[$i]->l9,  $rows[$i]->l10,  $rows[$i]->l11,  $rows[$i]->l12,  $rows[$i]->l13,  $rows[$i]->l14,  $rows[$i]->l15,  $rows[$i]->l16,  $rows[$i]->l17,  $rows[$i]->l18,  $rows[$i]->l19,  $rows[$i]->l20];

        $str_balls = str_replace("[", "", $rows[$i]->balls);
        $str_balls = str_replace("]", "", $str_balls);
        $str_balls = str_replace(" ", "", $str_balls);
        $str_balls = str_replace('"', "", $str_balls);
        $selected_balls = explode(",", $str_balls);

        $str_systems = str_replace("[", "", $rows[$i]->systems);
        $str_systems = str_replace("]", "", $str_systems);
        $str_systems = str_replace(" ", "", $str_systems);
        $str_systems = str_replace('"', "", $str_systems);
        $systems = explode(",", $str_systems);


        $cnt_balls = count($selected_balls);


        $sample_data = InitSampleTableData($cnt_balls, $quotes);

        
        $combination_sum = getCombinationSum($systems, $cnt_balls);

        $lot = 1 / $combination_sum;
        $table_data = getTableData($systems, $cnt_balls, $quotes);

        $stake = $rows[$i]->stake;
        foreach ($table_data as $key => $value) {

            $nr = $key;

            $possible_gain = 0;
            foreach ($value as $key2 => $value2) {
                # code...
                $possible_gain += $value2;
            }
            $possible_gains[$nr] = floor($possible_gain * $lot * $stake * 100) / 100;
        }

        $hit = 0;
        $total_gain = 0;
        $max_gain = 0;

        for ($k=0; $k < $cnt_balls; $k++) {
            for ($j=0; $j < 20; $j++) {
                if($generated_balls[$j] == $selected_balls[$k]){
                    $hit++;
                    if($hit > 0 && array_key_exists($hit - 1, $possible_gains)){
                        $max_gain = $possible_gains[$hit - 1];
                    }
                    break;
                }
            }
        }

        if(array_key_exists($rows[$i]->user_id, $total_gains))
            $total_gains[$rows[$i]->user_id] += $max_gain;
        else
            $total_gains[$rows[$i]->user_id] = $max_gain;


        if($hit > 0 && array_key_exists($hit - 1, $possible_gains)){

            $total_gain = $possible_gains[$hit - 1];

        }

        if(array_key_exists($rows[$i]->user_id, $total_stakes))
            $total_stakes[$rows[$i]->user_id] += $rows[$i]->stake;
        else
            $total_stakes[$rows[$i]->user_id] = $rows[$i]->stake;

    }

    $results = [];

    for ($i=0; $i < count($rows); $i++) { 
        $total_earned = 0;
        if (array_key_exists($rows[$i]->user_id, $total_gains)) {
            $total_earned = $total_gains[$rows[$i]->user_id];
        }

        $total_staked = $total_stakes[$rows[$i]->user_id];
        $rows[$i]->points_available = $total_earned + $rows[$i]->bonus - $total_staked;
        $rows[$i]->points_win = $total_earned;

        if( ! array_key_exists($rows[$i]->user_id, $results)){
            $results[$rows[$i]->user_id] = array('username' => $rows[$i]->username, 'points_available' => $rows[$i]->points_available, 'points_win' => $rows[$i]->points_win);
        }
    }

    //sort by points win
    $i = 0;
    $res_array = [];
    foreach ($results as $key => $value) {
        $res_array[$i] = $value;
        $i++;
    }
    for ($i=0; $i < count($res_array); $i++) { 
        for ($j= $i + 1; $j < count($res_array); $j++) { 
            # code...
            if($res_array[$i]['points_win'] < $res_array[$j]['points_win'])
            {
                $temp = $res_array[$i]['points_win'];
                $res_array[$i]['points_win'] = $res_array[$j]['points_win'];
                $res_array[$j]['points_win'] = $temp;
            }
        }
    }

?>
<!DOCTYPE html>
<!-- 
Template Name:: SmartAdmin PHP 7 Responsive WebApp - Template built with Bootstrap 4 and PHP 7
Version: 4.5.3
Author: Jovanni Lo
Website: https://smartadmin.lodev09.com
Purchase: https://wrapbootstrap.com/theme/smartadmin-php-7-responsive-webapp-WB05M9585
License: You must have a valid license purchased only from wrapbootstrap.com (link above) in order to legally use this theme for your project.
-->
<html lang="en">
    <?php include_once APP_PATH.'/includes/head.php'; ?>
    <body class="mod-bg-1 mod-nav-link ">
        <?php include_once APP_PATH.'/includes/theme.php'; ?>
        <!-- BEGIN Page Wrapper -->
        <div class="page-wrapper">
            <div class="page-inner">
                <?php include_once APP_PATH.'/includes/nav.php'; ?>
                <div class="page-content-wrapper">
                    <?php include_once APP_PATH.'/includes/header.php'; ?>
                    <!-- BEGIN Page Content -->
                    <!-- the #js-page-content id is needed for some plugins to initialize -->
                    <main id="js-page-content" role="main" class="page-content">
                        <ol class="breadcrumb page-breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">SmartAdmin</a></li>
                            <li class="breadcrumb-item">Datatables</li>
                            <li class="breadcrumb-item active">FixedHeader</li>
                            <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
                        </ol>
                        <div class="subheader">
                            <h1 class="subheader-title">
                               
                              
                            </h1>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <div id="panel-1" class="panel">
                                    <div class="panel-hdr">
                                        <h2>
                                            Monthly ranking top 100 <span class="fw-300"><i></i></span>
                                        </h2>
                                        <div class="panel-toolbar">
                                            <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
                                            <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
                                            <button class="btn btn-panel" data-action="panel-close" data-toggle="tooltip" data-offset="0,10" data-original-title="Close"></button>
                                        </div>
                                    </div>
                                    <div class="panel-container show">
                                        <div class="panel-content">
                                            
                                            <!-- datatable start -->
                                            <table id="dt-basic-example" class="table table-bordered table-hover table-striped w-100">
                                                <thead class="bg-primary-600">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Points</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        foreach ($res_array as $key => $value) {
                                                            if($key > 99)
                                                                break;
                                                            # code...
                                                    ?>
                                                    <tr>

                                                        <td><?= $key + 1 ?></td>
                                                        <td>
        													<div class="d-flex w-100 px-3 py-2 text-dark hover-white cursor-pointer show-child-on-hover">
                                                                <div class="profile-image-md rounded-circle" style="background-image:url('<?= ASSETS_URL ?>/img/demo/avatars/avatar-e.png'); background-size: cover;"></div>
                                                                <div class="px-1 flex-1">
                                                                    <div class="text-truncate text-truncate-md">
                                                                        <?= $value['username'] ?>
                                                                        <small class="d-block text-muted text-truncate text-truncate-md">
                                                                            <?= $value['points_available'] ?> Points
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                
                                                            </div>
    													</td>
                                                        <td><?= $value['points_win'] ?></td>
                                                    </tr>                                                   

                                                    <?php
                                                        }
                                                    ?>
                                                </tfoot>
                                            </table>
                                            <!-- datatable end -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                    <!-- END Page Content -->
                    <?php include_once APP_PATH.'/includes/footer.php'; ?>
                </div>
            </div>
        </div>
        <!-- END Page Wrapper -->
        <?php include_once APP_PATH.'/includes/extra.php'; ?>
        <?php include_once APP_PATH.'/includes/js.php'; ?>
        <!-- datatble responsive bundle contains: 
    + jquery.dataTables.js
    + dataTables.bootstrap4.js
    + dataTables.autofill.js                            
    + dataTables.buttons.js
    + buttons.bootstrap4.js
    + buttons.html5.js
    + buttons.print.js
    + buttons.colVis.js
    + dataTables.colreorder.js                          
    + dataTables.fixedcolumns.js                            
    + dataTables.fixedheader.js                     
    + dataTables.keytable.js                        
    + dataTables.responsive.js                          
    + dataTables.rowgroup.js                            
    + dataTables.rowreorder.js                          
    + dataTables.scroller.js                            
    + dataTables.select.js                          
    + datatables.styles.app.js
    + datatables.styles.buttons.app.js -->
        <script src="<?= ASSETS_URL ?>/js/datagrid/datatables/datatables.bundle.js"></script>
       <!-- <script>
            $(document).ready(function()
            {
                $('#dt-basic-example').dataTable(
                {
                    responsive: true,
                    fixedHeader: true,
                    paging: false
                });
            });

        </script>-->
		<script>
            var events = $('#app-eventlog');
            var clearlogText = function()
            {
                events.empty();
            }

            $(document).ready(function()
            {
                var table = $('#dt-basic-example').DataTable(
                {
                    responsive: true,
                    blurable: false,
                    keys: true,
                    stateSave: true,
                    filter: true, //for demo purpose only
                    lengthChange: false //for demo purpose only
					paging: false
                });
                table.on('key', function(e, datatable, key, cell, originalEvent)
                    {
                        events.prepend('<div class="my-1"><span class="mr-2 badge badge-info width-10 text-center">Key press</span> <span class="fw-500 text-primary"> ' + key + ' </span> for cell <i>' + cell.data() + '</i></div>');
                    })
                    .on('key-focus', function(e, datatable, cell)
                    {
                        events.prepend('<div class="my-1"><span class="mr-2 badge badge-success width-10 text-center">Cell focus</span> <i>' + cell.data() + '</i></div>');
                    })
                    .on('key-blur', function(e, datatable, cell)
                    {
                        events.prepend('<div class="my-1"><span class="mr-2 badge badge-warning width-10 text-center">Cell blur</span> <i>' + cell.data() + '</i></div>');
                    })
            });

        </script>
    </body>
</html>
