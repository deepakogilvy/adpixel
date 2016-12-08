<?php $this->assign('title', 'Business Unit'); ?>
<?php $currentyr = date( 'Y' ); ?>
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-7">
            <h1 class="page-heading"> Update Business Unit </h1>
        </div>
        <div class="col-sm-5 text-right hidden-xs">
            <a class="btn btn-warning push-5-r push-10" type="button" href="<?php echo $this->Neo->u( 'campaigns', 'index' ); ?>"><i class="fa fa-list-ul"></i> List All</a>
        </div>
    </div>
</div>
<div class="content">
    <div class="block block-content">
        <form class="form-horizontal js-validation-bootstrap" action="<?php echo $this->Neo->u( 'campaigns', 'edit', $campaign['Campaign']['id'] ); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label class="col-sm-3 control-label" for="campaign">Business Unit Name:</label>
                <div class="col-sm-3">
                    <input class="form-control required" type="text" id="name" name="name" value="<?php echo $campaign['Campaign']['name'];?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="start_date">Start Date:</label>
                <div class="col-sm-3">
                    <input type="text" placeholder="" data-date-format="mm/dd/yyyy" name="start_date" id="start_date" style="width:100%;" class="js-datepicker form-control col-sm-4 required" value="<?php echo $campaign['Campaign']['start_date'] ? toUi( $campaign['Campaign']['start_date'] ) : "01/01/$currentyr"; ?>">
                    <br><br><label id="start_date-error" class="text-danger" for="start_date" style="display:none"></label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="end_date">End Date:</label>
                <div class="col-sm-3">
                    <input type="text" placeholder="" data-date-format="mm/dd/yyyy" name="end_date" id="end_date" style="width:100%;" class="js-datepicker form-control col-sm-4 required" value="<?php echo $campaign['Campaign']['end_date'] ? toUi( $campaign['Campaign']['end_date'] ) : "12/31/$currentyr"; ?>">
                    <br><br><label id="end_date-error" class="text-danger" for="end_date" style="display:none"></label>

                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="validation">Validation Frequencies (Days):</label>
                <div class="col-sm-7">
                    <?php $pixel_vdays = explode(",", $campaign['Campaign']['validation_week_days']);
                    foreach( AppConstants::$validationDays as $k => $v ) {
                        if(  in_array( $k, $pixel_vdays )){
                            $sel = "checked ";
                        }else{
                             $sel = " ";
                        }
                        echo '<input type="checkbox" '.$sel.' name="validation_week_days[]" id="validation_week_days" class="required" value="' . $k . '"> ' . $v . '   ';
                    }
                    ?>
                    <br><br><label id="start_date-error" class="text-danger" for="validation_week_days" style="display:none"></label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="end_date">Pixel File:</label>
                <div class="col-sm-3">
                    <input type="file" placeholder=""  name="pixel_file"  style="width:100%;" class="" value="">
                    <div class="help-block">First tab in excel file should contain data,<br/> First row should be header row containing URL and pixel code.</div>
                    <br><br><label id="end_date-error" class="text-danger" for="pixel_file" style="display:none"></label>

                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3">
                    <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> Save</button>
                    <a href="<?php echo $this->Neo->u( 'campaigns', 'index' ); ?>"><input class="btn btn-danger push-20-l" type="button" value="Cancel" /></a>
                </div>
            </div>
        </form>
    </div>
</div>