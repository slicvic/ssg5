<div class="row">
    <h3><i class="fa fa-building-o"></i> Company Profile</h3>
    <hr>
</div>

<div class="row">
    <div class="col-md-12">
        <form data-parsley-validate action="/company/profile" method="post" class="form-horizontal">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label class="control-label col-sm-2">Name</label>
                <div class="col-sm-4">
                    <input required type="text" name="name" placeholder="Name" class="form-control" value="<?php echo Input::old('name', $company->name); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-2">Code</label>
                <div class="col-sm-4">
                    <input required type="text" name="code" placeholder="Code" class="form-control" value="<?php echo Input::old('code', $company->code); ?>">
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-8">
                    <button type="submit" class="btn btn-flat primary">Save Changes</button>
                    <a href="/company/profile">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
