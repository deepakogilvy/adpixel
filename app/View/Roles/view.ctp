<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-7">
            <h1 class="page-heading">Role: <?php echo $roleName; ?></h1>
        </div>
        <div class="col-sm-5 text-right hidden-xs">
            <a class="btn btn-success push-5-r push-10" type="button" href="<?php echo $this->Neo->u( 'roles', 'add' ); ?>"><i class="fa fa-plus"></i> Add New</a>
        </div>
    </div>
</div>
<div class="col-sm-12 col-lg-12">
    <div class="block block-themed">
        <div class="block-header bg-gray">
            <h3 class="block-title">Privileges</h3>
        </div>
        <div class="block-content">
            <table class="table table-striped table-borderless table-header-bg js-dataTable-full">
                <thead>
                    <tr>
                        <th class="col-lg-3">Controller</th>
                        <th class="col-lg-3">Action</th>
                        <th class="col-lg-3">Created On</th>
                        <th class="col-lg-2">Status</th>
                        <th class="col-lg-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach( $privileges as $privilege ) { ?>
                        <tr>
                            <td><?php echo ucwords( $privilege['Privilege']['controller'] ); ?></td>
                            <td><?php echo $privilege['Privilege']['action']; ?></td>
                            <td><?php echo $this->Neo->beautify( $privilege['Privilege']['created'] ); ?></td>
                            <td>
                                <?php if( $privilege['Privilege']['is_active'] ) { $rel=1;?>
                                    <span id="label_<?php echo $privilege['Privilege']['id']; ?>" class="label label-success">Active</span>
                                <?php } else { $rel=0;?>
                                    <span id="label_<?php echo $privilege['Privilege']['id']; ?>" class="label label-danger">Inactive</span>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default setStatus" rel="<?php echo $rel;?>" id="<?php echo $privilege['Privilege']['id']; ?>" data-controller="privileges" type="button" data-toggle="tooltip" title="" data-original-title="Change Status"><i class="fa fa-exclamation"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>