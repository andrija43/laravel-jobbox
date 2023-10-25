<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="form-group">
            <label
                class="control-label"
                for="facebook"
            >{{ __('Facebook Profile URL') }}</label>
            <input
                class="form-control"
                id="facebook"
                name="facebook"
                type="url"
                value="{{ $company->facebook }}"
                placeholder="https://facebook.com/company-name"
            >
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class="form-group">
            <label
                class="control-label"
                for="twitter"
            >{{ __('Twitter Profile URL') }}</label>
            <input
                class="form-control"
                id="twitter"
                name="twitter"
                type="url"
                value="{{ $company->twitter }}"
                placeholder="https://twitter.com/company-name"
            >
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="form-group">
            <label
                class="control-label"
                for="linkedin"
            >{{ __('Linkedin Profile URL') }}</label>
            <input
                class="form-control"
                id="linkedin"
                name="linkedin"
                type="url"
                value="{{ $company->linkedin }}"
                placeholder="https://linkedin.com/company/company-name"
            >
        </div>
    </div>
    <div class="col-md-6 col-sm-12">
        <div class="form-group">
            <label
                class="control-label"
                for="instagram"
            >{{ __('Instagram Profile URL') }}</label>
            <input
                class="form-control"
                id="instagram"
                name="instagram"
                type="url"
                value="{{ $company->instagram }}"
                placeholder="https://instagram.com/company-name"
            >
        </div>
    </div>
</div>
