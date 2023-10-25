<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Forms\JobShiftForm;
use Botble\JobBoard\Http\Requests\JobShiftRequest;
use Botble\JobBoard\Models\JobShift;
use Botble\JobBoard\Tables\JobShiftTable;
use Exception;
use Illuminate\Http\Request;

class JobShiftController extends BaseController
{
    public function index(JobShiftTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-shift.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-shift.create'));

        return $formBuilder->create(JobShiftForm::class)->renderForm();
    }

    public function store(JobShiftRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            JobShift::query()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $jobShift = JobShift::query()->create($request->input());

        event(new CreatedContentEvent(JOB_SHIFT_MODULE_SCREEN_NAME, $request, $jobShift));

        return $response
            ->setPreviousUrl(route('job-shifts.index'))
            ->setNextUrl(route('job-shifts.edit', $jobShift->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(JobShift $jobShift, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $jobShift));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $jobShift->name]));

        return $formBuilder->create(JobShiftForm::class, ['model' => $jobShift])->renderForm();
    }

    public function update(JobShift $jobShift, JobShiftRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            JobShift::query()->where('id', '!=', $jobShift->getKey())->update(['is_default' => 0]);
        }

        $jobShift->fill($request->input());
        $jobShift->save();

        event(new UpdatedContentEvent(JOB_SHIFT_MODULE_SCREEN_NAME, $request, $jobShift));

        return $response
            ->setPreviousUrl(route('job-shifts.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(JobShift $jobShift, Request $request, BaseHttpResponse $response)
    {
        try {
            $jobShift->delete();

            event(new DeletedContentEvent(JOB_SHIFT_MODULE_SCREEN_NAME, $request, $jobShift));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $jobShift = JobShift::query()->findOrFail($id);
            $jobShift->delete();
            event(new DeletedContentEvent(JOB_SHIFT_MODULE_SCREEN_NAME, $request, $jobShift));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
