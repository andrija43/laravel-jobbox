@push('footer')
    <div
        class="modal fade"
        id="add-company-modal"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        role="dialog"
        tabindex="-1"
    >
        <div class="modal-dialog modal-xs">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title"><i class="til_img"></i><strong>{{ __('Add new company') }}</strong></h4>
                    <button
                        class="close"
                        data-bs-dismiss="modal"
                        type="button"
                        aria-hidden="true"
                    >
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body with-padding">
                    <div class="form-group">
                        <label
                            class="control-label required"
                            for="company_name"
                        >{{ trans('core/base::forms.name') }}</label>
                        <input
                            class="form-control"
                            id="company_name"
                            type="text"
                            placeholder="{{ trans('core/base::forms.name') }}"
                        >
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        class="float-start btn btn-warning"
                        data-bs-dismiss="modal"
                    >{{ trans('core/base::tables.cancel') }}</button>
                    <a
                        class="float-end btn btn-info"
                        id="btn-add-company"
                        href="#"
                    >{{ __('Save') }}</a>
                </div>
            </div>
        </div>
    </div>
@endpush
