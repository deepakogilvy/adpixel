<h1 class="font-s128 font-w300 text-city animated flipInX">404</h1>
<h2 class="h3 font-w300 push-50 animated fadeInUp">We are sorry but the page you are looking for was not found..</h2>
<?php
if (Configure::read('debug') > 0):
    echo $this->element('exception_stack_trace');
endif;
?>