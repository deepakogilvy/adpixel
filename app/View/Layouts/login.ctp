<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>
            AD Pixel Tracker - Neo@Ogilvy
        </title>
        <?php
            echo $this->Html->meta('icon');
            echo $this->Html->css( [ '/js/plugins/slick/slick.min', '/js/plugins/slick/slick-theme.min', 'app' ] );
            echo $this->fetch('meta');
            echo $this->fetch('css');
        ?>
    </head>
    <body class="bg-image">
        <div class="content overflow-hidden">
            <div class="row">
                <?php echo $this->Session->flash(); ?>
                <?php echo $this->fetch('content'); ?>
                <?php echo $this->element('sql_dump'); ?>
            </div>
        </div>
        <div class="push-10-t text-center animated fadeInUp">
            <small class="text-black-op font-w600"><span class="js-year-copy"></span> &copy; NEO@Ogilvy</small>
        </div>
        <?php
            echo $this->Html->script( [ 'core/jquery.min', 'plugins/jquery-validation/jquery.validate.min', 'common', 'core/bootstrap.min', 'core/jquery.slimscroll.min', 'core/jquery.scrollLock.min', 'core/jquery.appear.min', 'core/js.cookie.min', 'core/jquery.placeholder.min', 'app' ] );
            echo $this->fetch('script');
        ?>  
    </body>
</html>
