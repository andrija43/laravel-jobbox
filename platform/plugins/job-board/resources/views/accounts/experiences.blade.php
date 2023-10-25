<div
    class="widget meta-boxes"
    id="experience_wrap"
>
    <div class="widget-title">
        <h4><span>{{ __('Experiences') }}</span></h4>
    </div>
    <div
        class="widget-body"
        id="experience-histories"
    >
        <a
            class="btn-trigger-add-experience"
            href="#"
            v-pre=""
        >{{ trans('plugins/job-board::account.add_experience') }}</a>
        <div class="comment-log-timeline">
            @if ($experiences->count() > 0)
                <div
                    class="column-left-history ps-relative"
                    id="order-history-wrapper"
                >
                    <div class="item-card">
                        <div class="item-card-body ">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">
                                            {{ trans('plugins/job-board::account.table.experiences.company') }}</th>
                                        <th scope="col">
                                            {{ trans('plugins/job-board::account.table.experiences.position') }}</th>
                                        <th scope="col">{{ trans('plugins/job-board::account.table.started_at') }}
                                        </th>
                                        <th scope="col">{{ trans('plugins/job-board::account.table.ended_at') }}</th>
                                        <th scope="col">{{ trans('plugins/job-board::account.table.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($experiences as $experience)
                                        <tr>
                                            <th scope="row">{{ $loop->iteration }}</th>
                                            <td class="text-start"> {{ $experience->company }}</td>
                                            <td>{{ $experience->position }}</td>
                                            <td>{{ $experience->started_at->format('Y-m-d') }}</td>
                                            <td>{{ $experience->ended_at ? $experience->ended_at->format('Y-m-d') : trans('plugins/job-board::account.now') }}
                                            </td>
                                            <td
                                                class="text-center"
                                                style="width: 120px;"
                                            >
                                                <a
                                                    class="btn btn-icon btn-sm btn-warning me-1 btn-trigger-edit-experience"
                                                    data-bs-toggle="tooltip"
                                                    data-section="{{ route('accounts.experiences.edit-modal', [$experience->id, $experience->account_id]) }}"
                                                    data-bs-original-title="{{ trans('plugins/job-board::account.action_table.edit') }}"
                                                    href="#"
                                                    role="button"
                                                >
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a
                                                    class="btn btn-icon btn-sm btn-danger deleteDialog"
                                                    data-bs-toggle="tooltip"
                                                    data-section="{{ route('accounts.experiences.destroy', $experience->id) }}"
                                                    data-bs-original-title="{{ trans('plugins/job-board::account.action_table.delete') }}"
                                                    href="#"
                                                    role="button"
                                                >
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <p>{{ trans('plugins/job-board::account.no_experience') }}</p>
            @endif
        </div>
    </div>
</div>

{!! Form::modalAction(
    'edit-experience-modal',
    trans('plugins/job-board::account.edit_experience'),
    'info',
    null,
    'confirm-edit-experience-button',
    trans('plugins/job-board::account.action_table.edit'),
    'modal-md',
) !!}
