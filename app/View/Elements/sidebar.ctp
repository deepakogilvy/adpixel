<nav id="sidebar" style="top:45px;">
    <div id="sidebar-scroll">
        <div class="sidebar-content">

            <div class="side-content">
                <ul class="nav-main">
                    <?php echo $this->Neo->addMenuLink( 'Business Unit', 'campaigns', 'index', [ 'icon' => 'si si-home' ] ); ?>
                    <?php
                    if( $current_user->isAllowed( 'users', 'index' ) || $current_user->isAllowed( 'data_imports', 'index' ) || $current_user->isAllowed( 'roles', 'index' ) || $current_user->isAllowed( 'privileges', 'index' ) || $current_user->isAllowed( 'audits', 'index' ) || $current_user->isAllowed( 'business_units', 'index' ) ) {

                        $adminActions = array();
                        $adminActions['users:index'] = [ 'Manage Users', 'users', 'index' ];
                        $adminActions['roles:index'] = [ 'Manage Roles', 'roles', 'index' ];
                        ?>
                        <li class="<?php echo in_array( "{$this->request->params['controller']}:{$this->request->params['action']}", array_keys( $adminActions ) ) ? 'open' : ''; ?>" >
                            <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="si si-key"></i><span class="sidebar-mini-hide">Administration</span></a>
                            <ul>
                                <?php
                                foreach( $adminActions as $link ) {
                                    echo $this->Neo->addMenuLink( $link[0], $link[1], $link[2] );
                                }
                                ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <?php echo $this->Neo->addMenuLink( '&nbsp;Support', 'supports', 'index', [ 'icon' => 'fa fa-male' ] ); ?>

                </ul>
            </div>
        </div>
    </div>
</nav>