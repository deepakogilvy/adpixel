<?php $this->assign('title', 'Business Unit'); ?>
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-7">
            <h1 class="page-heading">Business Unit </h1>
        </div>
        <div class="col-sm-5 text-right hidden-xs">
            <a class="btn btn-success push-5-r push-10" type="button" href="<?php echo $this->Neo->u( 'campaigns', 'add' ); ?>"><i class="fa fa-plus"></i> Add New</a>
        </div>
    </div>
</div>
<div class="content">
    <div class="block block-content col-sm-12">

        <table class="table table-striped table-bordered table-header-bg js-dataTable-serverSide" data-controller="<?php echo $this->request->params['controller'] ?>"  data-ajax="/<?php echo $this->request->params['controller'] ?>/ajaxData" data-zero_records="No campaign found matching search criteria"  data-empty_table="There are no campaign present at this moment, consider adding one to view it here.">
            <thead>
                <tr>
                    <th class="col-sm-3"  data-name="name">Business Unit</th>
                    <th class="col-sm-3"  data-name="start_date">Start Date</th>
                    <th class="col-sm-3"  data-name="end_date">End Date</th>
                    <th class="col-sm-2"  data-name="frequency">Scan Frequency(Days)</th>
                    <th class="col-sm-1"  data-name="created" data-searchable="false">Created</th>
                    <th class="col-sm-2 text-center  actions-column" data-orderable="false">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

    </div>
</div>
<?php echo $this->element( 'delete' ); ?>