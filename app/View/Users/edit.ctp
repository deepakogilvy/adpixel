<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-7">
            <h1 class="page-heading"> Edit User </h1>
        </div>
        <div class="col-sm-5 text-right hidden-xs">
            <a class="btn btn-warning push-5-r push-10" type="button" href="<?php echo $this->Neo->u( 'users', 'index' ); ?>"><i class="fa fa-list-ul"></i> List All</a>
        </div>
    </div>
</div>
<div class="content">
    <div class="block block-content">
        <form id="editUserForm" class="form-horizontal js-validation-bootstrap" action="<?php echo $this->Neo->u( 'users', 'edit' ); ?>" method="post">
            <div class="form-group">
                <label class="col-sm-3 control-label" for="first_name">First Name:</label>
                <div class="col-sm-4">
                    <input class="form-control required " <?php if( isset( $user ) ) { ?> value="<?php echo $user['User']['first_name']; ?>" <?php } ?> type="text" maxlength="64" id="first_name" name="first_name">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="last_name">Last Name:</label>
                <div class="col-sm-4">
                    <input  class="form-control " <?php if( isset( $user ) ) { ?> value="<?php echo $user['User']['last_name']; ?>" <?php } ?> type="text"  maxlength="64" id="last_name" name="last_name">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="email">Email:</label>
                <div class="col-sm-4">
                    <input class="form-control required email" <?php if( isset( $user ) ) { ?> value="<?php echo $user['User']['email']; ?>"  <?php } ?> type="email" maxlength="156" id="email" name="email">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="role_id">Role:</label>
                <div class="col-sm-4">
                    <select style="width: 100%;" name='role_id' id="role_id" class="js-select2 form-control select2-hidden-accessible required required">
                        <?php echo $this->Neo->setOptions( $roles, $user['User']['role_id'] ) ?>
                    </select>
                    <label for="role_id" class="text-danger" id="role_id-error" style="display: none;"></label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="is_active">Active:</label>
                <div class="col-sm-4">
                    <label class="css-input css-checkbox css-checkbox-success">
                        <input name='is_active' id='is_active' type="checkbox" <?php if( isset( $user ) && $user['User']['is_active'] ) { ?> checked="checked" <?php } ?>><span></span> 
                    </label>
                </div>
            </div>
            <input value="<?php echo $user['User']['id']; ?>" type="hidden" id="id" name="id">
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3">
                    <button class="btn btn-success push-5-r push-10" type="submit"><i class="fa fa-save"></i> Save</button>
                    <a href="<?php echo Router::url( [ 'controller' => 'users', 'action' => 'index' ] ) ?>" type="button" class="btn btn-danger push-5-r push-10"><i class="fa fa-ban"></i> Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("#editUserForm").validate({
            rules: {email: {validateName: true}},
            messages: {email: {validateName: "Email already exist."}},
            onkeyup: false,
            errorClass: 'text-danger'
        });

        jQuery.validator.addMethod("validateName",
                function (value, element) {
                    var response;
                    jQuery.ajax({
                        async: false,
                        url: '/users/validEmail?email=' + jQuery('#email').val() + '&&id=' + jQuery('#id').val(), success: function (data) {
                            console.log(data);
                            response = (data == 'YES') ? true : false;
                        }
                    });
                    return response;
                }, 'Email already exist.'
                );


    });
</script>