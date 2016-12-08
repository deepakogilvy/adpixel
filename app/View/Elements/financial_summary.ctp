<?php if( strtolower( $current_user->loggedUser['User']['user_type'] ) != 'nca' ) { ?>
<span class="text-budget pull-left push-30-r" id="total_budget">
    <h5>Media Budget: <?php
        if( $rate && $rate != 0 ) {
            echo $currency . " "; echo $this->Neo->nf( $total_budget / $rate, 0 );
        }
        ?></h5> <h6 class="text-budget-d pull-right">$ <?= $this->Neo->nf( $total_budget, 0 ); ?></h6>
</span>
<span class="text-budget pull-left push-30-r" id="total_non_media_budget">
    <h5>Non Media Budget: <?php
        if( $rate && $rate != 0 ) {
            echo $currency . " "; echo $this->Neo->nf( $total_non_media_budget / $rate, 0 );
        }
        ?></h5> <h6 class="text-budget-d pull-right">$ <?= $this->Neo->nf( $total_non_media_budget, 0 ); ?></h6>
</span>
<?php } ?>
<span class="text-proj pull-left push-30-r" id="total_projection">
    <h5 >Projections: <?php
        if( $rate && $rate != 0 ) {
            echo $currency . " "; echo $this->Neo->nf( $total_projection / $rate, 0 );
        }
        ?></h5> <h6 class="text-proj-d pull-right">$ <?= $this->Neo->nf( $total_projection, 0 ); ?></h6>                                         
</span>

<span class="text-actual pull-left" id="total_actual">
    <h5 >Actuals:<?php
        if( $rate && $rate != 0 ) {
            echo $currency . " "; echo $this->Neo->nf( $total_actual / $rate, 0 );
        }
        ?><h5> <h6 class="text-actual-d pull-right" >$ <?= $this->Neo->nf( $total_actual, 0 ); ?></h6>
            </span>  
