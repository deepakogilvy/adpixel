<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>
             <?php echo $this->fetch('title'); ?>
        </title>
        <?php
            echo $this->Html->meta('icon');
            echo $this->Html->css( [ '/js/plugins/slick/slick.min', '/js/plugins/slick/slick-theme.min', '/js/plugins/datatables/jquery.dataTables.min', '/js/plugins/select2/select2.min.css', '/js/plugins/select2/select2-bootstrap', 'app', '/js/plugins/jquery-tags-input/jquery.tagsinput.min', '/js/plugins/bootstrap-datepicker/bootstrap-datepicker3.min', 'jquery-ui', '/js/plugins/dropzonejs/dropzone.min', '/js/plugins/summernote/summernote.min', '/js/plugins/summernote/summernote-bs3.min' ] );
            echo $this->fetch('meta');
            echo $this->fetch('css');
            echo $this->Html->script( [ 'core/jquery.min', 'core/jquery-ui', 'plugins/dropzonejs/dropzone.min', 'core/jquery.floatThead.min', 'plugins/summernote/summernote.min', 'plugins/number-format' ] );
            echo $this->fetch('script');
        ?>
        <script type="text/javascript">
            var refreshMarkets = refreshProperties = refreshRates = refreshMediaClasses = refreshMediaOwners = refreshRegions = refreshChannels = refreshLangSettings = false;
            var clearint = null;
            function bwRefresh(url) {
               var startTime = new Date().getTime();
               clearint = setInterval( function(){ jQuery("#main-container").load( url );
               if(new Date().getTime() - startTime > 15000){
                        clearInterval(clearint);
                        return;
                    }}, 5000 );
            }
        </script>
    </head>
    <body>
        <div class="main-container">
            <span >
                <a href="<?php echo $this->Neo->u( 'campaigns' ); ?>"><img height="40" src="/img/logo.png"  alt = "neo@ogilvy"></a>
            </span>
        </div>
        </header>
        
        <div id="page-container" class="sidebar-l sidebar-o side-scroll">
           
            <div class="clear-both"></div>
            <?php echo $this->element( 'aside' ); ?>
            <?php echo $this->element( 'sidebar' ); ?>
            <header id="header-navbar" class="content-mini content-mini-full">

                <?php

                 if( $this->params['controller'] == "home" ){?>
                  <span class="font-w600 push-5-t h5">Welcome, <?php echo $this->Session->read( 'Auth.User.name' );?></span>
                    <?php }?>
                <?php echo $this->element( 'header' ); ?>
            </header>
            <div id="content">
                <main id="main-container">
                    <?php echo $this->Session->flash(); ?>
                    <?php echo $this->fetch('content'); ?>
                    <?php echo $this->element('sql_dump'); ?>
                </main>
            </div>
            <?php echo $this->element( 'footer' ); ?>
        </div>
        <?php
            if( isset( $refreshJQL ) ) {
                if( !is_array( $refreshJQL ) ) $refreshJQL = [ $refreshJQL ];
        ?>
        <script type="text/javascript">
            <?php foreach( $refreshJQL as $r ) { ?>
                var <?php echo $r ?> = true;
            <?php } ?>
        </script>
        <?php } ?>
        <?php
            echo $this->Html->script( [ 'jql', 'jql.jquery', 'core/jquery.storageapi','plugins/jquery-validation/jquery.validate.min', 'core/bootstrap.min', 'core/jquery.slimscroll.min', 'core/jquery.scrollLock.min', 'core/jquery.appear.min', 'core/js.cookie.min', 'core/jquery.placeholder.min', 'plugins/datatables/jquery.dataTables.min', 'pages/base_tables_datatables', 'app', 'plugins/bootstrap-datepicker/bootstrap-datepicker.min', 'plugins/select2/select2.full.min', 'plugins/masked-inputs/jquery.maskedinput.min', 'plugins/jquery-tags-input/jquery.tagsinput.min', 'common', 'plugins/sparkline/jquery.sparkline.min', 'plugins/chartjs/Chart.min', 'pages/base_comp_charts' ] );
            echo $this->fetch('script');
        ?>
        <script>
            jQuery(function () {
                App.initHelpers(['datepicker', 'colorpicker', 'select2', 'masked-inputs', 'tags-inputs']);
            });
        </script>
    </body>
</html>