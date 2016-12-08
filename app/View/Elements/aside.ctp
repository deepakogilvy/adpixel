<aside id="side-overlay">
    <div>
        <div class="side-header side-content">
            <button class="btn btn-default pull-right" type="button" data-toggle="layout" data-action="side_overlay_close">
                <i class="fa fa-times"></i>
            </button>
            <span>
                <img class="img-avatar img-avatar32" src="/img/avatars/avatar10.jpg" alt="image">
                <span class="font-w600 push-10-l"><?php echo $this->Session->read( 'Auth.User.name' );?></span>
            </span>
        </div>
        <div class="side-content remove-padding-t">
            <div class="block pull-r-l">
                <div class="block-header bg-gray-lighter">
                    <ul class="block-options">
                        <li>
                            <button type="button" data-toggle="block-option" data-action="refresh_toggle" data-action-mode="demo"><i class="si si-refresh"></i></button>
                        </li>
                        <li>
                            <button type="button" data-toggle="block-option" data-action="content_toggle"></button>
                        </li>
                    </ul>
                    <h3 class="block-title">Notifications</h3>
                </div>
                <div class="block-content" id="side-overlay-scroll" style="max-height:530px;">
                    <ul class="list list-activity notifications">
                    <?php if($notifications_for_menubar) {
                    foreach($notifications_for_menubar as $notification) { ?>
                        <li id="<?php echo $notification['Notification']['notification_id'] ?>" <?php echo $notification['Notification']['read'] ? '' : 'style="background-color: #f0f0f0;"' ?>>
                            <i class="si si-pencil text-info"></i>
                            <div class="font-w600"><a id="<?php echo $notification['Notification']['notification_id'] ?>" <?php echo $notification['Notification']['url'] ? 'href="' . $notification['Notification']['url'] . '"' : ''  ?>><?php echo $notification['Notification']['message']; ?></a></div>
                            <div>By <?php echo $notification['Notification']['source'][1]; ?></div>
                            <div><small class="text-muted"><?php echo $this->Neo->ago($notification['Notification']['modified']->sec);?></small></div>
                        </li>
                       
                <?php  }  ?>
                    <li class="load-more text-danger">Load More</li>
                <?php } else {
                   echo  "<li class='text-danger'> No Notifications </li>";
                    } ?>
                    </ul>
                    
                </div>
            </div>
        </div>
    </div>
</aside>