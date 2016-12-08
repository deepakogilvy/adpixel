<ul class="nav-header pull-right">
    <li>
        <div class="btn-group">
            <button class="btn btn-default btn-image dropdown-toggle" data-toggle="dropdown" type="button">
                <img src="/img/avatars/avatar10.jpg" alt="Image">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <li class="dropdown-header">Profile</li>
                <li>
                    <a tabindex="-1" href="<?php echo $this->Neo->u( 'users', 'me' ); ?>"><i class="si si-user pull-right"></i> Profile</a>
                </li>
                <li class="divider"></li>
                <li class="dropdown-header">Actions</li>
                <li>
                    <a tabindex="-1" href="<?php echo $this->Neo->u( 'users', 'logout' ); ?>"><i class="si si-logout pull-right"></i>Log out</a>
                </li>
            </ul>
        </div>
    </li>
    <li>
        <button class="btn btn-default mark_all_read" data-toggle="layout" data-action="side_overlay_toggle" type="button">
        <i class="fa fa-exclamation-triangle text-danger"></i>&nbsp<span class="number"><?php echo $notifications_count_for_menubar ? $notifications_count_for_menubar : 0 ?></span>
        </button>
    </li>
</ul>

<ul class="nav-header pull-left">
    <li class="hidden-md hidden-lg">
        <button class="btn btn-default" data-toggle="layout" data-action="sidebar_toggle" type="button"><i class="fa fa-navicon"></i></button>
    </li>
    <li class="hidden-xs hidden-sm">
        <button class="btn btn-default" data-toggle="layout" data-action="sidebar_mini_toggle" type="button"><i class="fa fa-ellipsis-v"></i></button>
    </li>
</ul>