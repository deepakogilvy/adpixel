<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-7">
            <h1 class="page-heading">Edit Role </h1>
        </div>
        <div class="col-sm-5 text-right hidden-xs">
            <a class="btn btn-warning push-5-r push-10" type="button" href="<?php echo $this->Neo->u( 'roles', 'index' ); ?>"><i class="fa fa-list-ul"></i> List All</a>
        </div>
    </div>
</div>
<div class="content">
    <div class="block block-content">
        <form class="form-horizontal js-validation-bootstrap" action="<?php echo $this->Neo->u( 'roles', 'edit' ); ?>" method="post">
            <div class="form-group">
                <label class="col-sm-3 control-label" for="role_name">Role Name:</label>
                <div class="col-sm-3">
                    <input class="form-control required alphabet" value="<?php echo $role['Role']['role_name']; ?>" type="text" id="role_name" name="role_name">
                </div>
            </div>
            <input type = "hidden" name = 'id' value = '<?php echo $role['Role']['id']; ?>' >
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3">
                    <button class="btn btn-success push-5-r push-10" type="submit"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
</div>