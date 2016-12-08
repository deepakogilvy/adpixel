<div class="content">
    <div class="push-50-t push-15 clearfix">
        <div class="push-15-r pull-left">
            <img class="img-avatar img-avatar-thumb" src="/img/avatars/avatar10.jpg" alt="">
        </div>
        <h1 class="h2 push-5-t animated zoomIn"><?php echo ucwords( $user['User']['name'] ); ?></h1>
    </div>
</div>
<div class="content bg-white border-b">
    <div class="row items-push">
        <div class="col-sm-12">
            <div class="font-w700 text-gray-darker">Email</div>
            <span class="" ><?php echo $user['User']['email']; ?></span>
        </div>
        <div class="col-sm-12">
            <div class="font-w700 text-gray-darker">Organization</div>
            <span class="" ><?php echo $company; ?></span>
        </div>
        <div class="col-sm-12">
            <div class="font-w700 text-gray-darker">Last Login</div>
            <span class="" ><?php echo $this->Neo->beautify( $user['User']['last_login'] ); ?></span>
        </div>
        <div class="col-sm-12">
            <div class="font-w700 text-gray-darker">Status</div>
            <span class="" ><?php echo $user['User']['is_active'] ? 'Active' : 'Inactive'; ?></span>
        </div>
    </div>
</div>