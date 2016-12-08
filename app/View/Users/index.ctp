<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-7">
            <h1 class="page-heading">Users </h1>
        </div>
        <div class="col-sm-5 text-right hidden-xs">
            <!--a class="btn btn-warning push-5-r push-10" type="button" id="search-user"><i class="fa fa-search"></i> Search</a-->
            <?php echo $this->Neo->addBtn( 'Add New', 'users', 'add', [ 'class' => 'btn btn-success push-5-r push-10', 'icon' => 'fa fa-plus' ] ); ?>
        </div>
    </div>
</div>
<div class="content col-sm-6 col-lg-12" id="searchBlock" <?php if( !isset( $queryString ) ) { ?> style="display:none;"  <?php } ?> >
    <div class="block">
        <div class="block-content">
            <form class="form-horizontal js-validation-bootstrap" action="<?php echo $this->Neo->u( 'users', 'index' ); ?>" method="get">
                <div class="form-group">
                    <label class="col-md-4 control-label" for="q">Search: </label>
                    <div class="col-sm-4">
                        <input class="form-control" type="text" id="q" name="q" <?php if( isset( $queryString['q'] ) ) { ?> value="<?php echo $queryString['q']; ?>" <?php } ?> >
                    </div>
                    <input class="btn btn-primary" type="Submit" value="GO" />
                </div>
            </form>
        </div>
    </div>
</div>
<div class="content">
    <div class="block block-content col-sm-12">

        <table class="table table-striped table-bordered table-header-bg js-dataTable-serverSide " data-controller="<?php echo $this->request->params['controller'] ?>"   data-ajax="/users/ajaxData"    data-zero_records="No users found matching search criteria"  data-empty_table="There are no users present at this moment, consider adding one to view it here.">
            <thead>
                <tr>
                    <th class="col-sm-3"  data-name="name">Name</th>
                    <th class="col-sm-3"  data-name="email" >Email</th>
                    <th class="col-sm-3"  data-name="Role.role_name" >Role</th>
                    <th class="col-sm-1"  data-name="is_active">Status</th>
                    <th class="col-sm-2 text-center actions-column" data-orderable="false" >Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

    </div>
</div>
<?php echo $this->element( 'delete' ); ?>
<script type="text/javascript">
    jQuery(document).on('click', '#search-user', function () {
        jQuery('#searchBlock').slideToggle();
    });
</script>