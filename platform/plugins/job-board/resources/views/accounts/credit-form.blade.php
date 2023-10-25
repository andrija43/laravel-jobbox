{!! Form::open(['url' => route('accounts.credits.add', $account->id)]) !!}
<div class="next-form-section">
    <div class="next-form-grid">
        <div class="next-form-grid-cell">
            <label class="text-title-field">{{ trans('plugins/job-board::account.form.number_of_credits') }}</label>
            <input
                class="next-input"
                name="credits"
                type="number"
                value="0"
                placeholder="{{ trans('plugins/job-board::account.form.number_of_credits') }}"
            >
        </div>
    </div>
    <div class="next-form-grid">
        <div class="next-form-grid-cell">
            <label class="text-title-field">{{ __('Description') }}</label>
            <textarea
                class="next-input"
                name="description"
                placeholder="{{ trans('plugins/job-board::account.form.description') }}"
                rows="5"
            ></textarea>
        </div>
    </div>
</div>
{!! Form::close() !!}
