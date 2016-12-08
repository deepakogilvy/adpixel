<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-7">
            <h1 class="page-heading"> Add User </h1>
        </div>
        <div class="col-sm-5 text-right hidden-xs">
            <a class="btn btn-warning push-5-r push-10" type="button" href="<?php echo $this->Neo->u( 'users', 'index' ); ?>"><i class="fa fa-list-ul"></i> List All</a>
        </div>
    </div>
</div>
<div class="content">
    <div class="block block-content">
        <form class="form-horizontal js-validation-bootstrap" action="<?php echo $this->Neo->u( 'users', 'add' ); ?>" method="post" id="addUserForm" >

            <div class="form-group">
                <label class="col-sm-3 control-label" for="first_name">First Name:</label>
                <div class="col-sm-4">
                    <input class="form-control required " maxlength="64" type="text" id="first_name" name="first_name" value="<?php echo $data['first_name'] ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="last_name">Last Name:</label>
                <div class="col-sm-4">
                    <input class="form-control " type="text" maxlength="64" id="last_name" name="last_name" value="<?php echo $data['last_name'] ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="email">Email:</label>
                <div class="col-sm-4">
                    <input class="form-control required email" type="email" maxlength="156" id="email" name="email" value="<?php echo $data['email'] ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="role_id">Role:</label>
                <div class="col-sm-4">
                    <select style="width:100%" name='role_id' id="role_id" class="required js-select2 form-control select2-hidden-accessible">
                        <?php echo $this->Neo->setOptions( $roles, $data['role_id'] ) ?>
                    </select>
                    <label for="role_id" class="text-danger" id="role_id-error" style="display: none;"></label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="is_active">Active:</label>
                <div class="col-sm-4">
                    <label class="css-input css-checkbox css-checkbox-success">
                        <input name='is_active' id='is_active' <?php if( $data['is_active'] ) { ?> checked="" <?php } ?> type="checkbox"><span></span> 
                    </label>
                </div>
            </div>

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
        jQuery("#addUserForm").validate({
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
                        url: '/users/validEmail/' + jQuery('#email').val(), success: function (data) {
                            console.log(data);
                            response = (data == 'YES') ? true : false;
                        }
                    });
                    return response;
                }, 'Email already exist.'
                );

    });
</script>