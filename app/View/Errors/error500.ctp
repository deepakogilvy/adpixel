<h1 class="font-s128 font-w300 text-modern animated zoomInDown">500</h1>
<h2 class="h3 font-w300 push-50 animated fadeInUp">We are sorry but our server encountered an internal error..</h2>
<?php
if (Configure::read('debug') > 0):
    echo $this->element('exception_stack_trace');
endif;
?>