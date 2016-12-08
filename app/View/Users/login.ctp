<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
    <div class="block block-themed animated bounceIn">
        <div class="block-header bg-primary">
            <h3 class="block-title">Login</h3>
        </div>
        <div class="block-content block-content-full block-content-narrow">
            <div class="text-center push-15-t push-15">
                <img class="img-avatar img-avatar96" src="/img/avatars/avatar10.jpg" alt="">
            </div>
            <div class="text-danger">
                <?php if( $messgae == 'err1' ){
                        echo "Email and Password did not match! Please try again.<br/>";
                    }?>
            </div>
            <form class="js-validation-lock form-horizontal push-30-t push-30" action="" method="post">
                <div class="form-group">
                    <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2">
                        <div class="form-material form-material-primary">
                            <input class="form-control required" type="email" id="email" name="email" placeholder="Please enter your email">
                            <label for="email">Email</label>
                        </div>
                        <label id="email-error" class="text-danger" style="display:none;" for="email"></label>
                        <div class="form-material form-material-primary">
                            <input class="form-control required" type="password" id="password" name="password" placeholder="Please enter your password">
                        </div>
                        <label id="password-error" class="text-danger" for="password" style="display:none;"></label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3">
                        <button class="btn btn-block btn-default" type="submit"><i class="si si-lock-open pull-right"></i> Login</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>