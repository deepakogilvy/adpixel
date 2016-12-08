<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-7">
            <h1 class="page-heading">Roles </h1>
        </div>
        <div class="col-sm-5 text-right hidden-xs">
            <?php echo $this->Neo->addBtn( 'Add New', 'roles', 'add', [ 'class' => 'btn btn-success push-5-r push-10', 'icon' => 'fa fa-plus' ] ); ?>
        </div>
    </div>
</div>
<div class="content">
    <div class="block block-content col-sm-12">
        <table class="table table-striped table-bordered table-header-bg js-dataTable-serverSide" data-controller="<?php echo $this->request->params['controller'] ?>"  data-ajax="/<?php echo $this->request->params['controller'] ?>/ajaxData" data-zero_records="No roles found matching search criteria"  data-empty_table="There are no roles present at this moment, consider adding one to view it here.">
            <thead>
                <tr>
                    <th class="col-sm-3" data-name="role_name">Name</th>
                    <th class="col-sm-1" data-name="is_active">Status</th>
                    <th class="col-sm-2 text-center  actions-column" data-orderable="false">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->element( 'delete' ); ?>