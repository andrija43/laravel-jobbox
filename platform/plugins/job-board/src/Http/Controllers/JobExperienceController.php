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
use Botble\JobBoard\Forms\JobExperienceForm;
use Botble\JobBoard\Http\Requests\JobExperienceRequest;
use Botble\JobBoard\Models\JobExperience;
use Botble\JobBoard\Tables\JobExperienceTable;
use Exception;
use Illuminate\Http\Request;

class JobExperienceController extends BaseController
{
    public function index(JobExperienceTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-experience.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-experience.create'));

        return $formBuilder->create(JobExperienceForm::class)->renderForm();
    }

    public function store(JobExperienceRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            JobExperience::query()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $jobExperience = JobExperience::query()->create($request->input());

        event(new CreatedContentEvent(JOB_EXPERIENCE_MODULE_SCREEN_NAME, $request, $jobExperience));

        return $response
            ->setPreviousUrl(route('job-experiences.index'))
            ->setNextUrl(route('job-experiences.edit', $jobExperience->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(JobExperience $jobExperience, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $jobExperience));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $jobExperience->name]));

        return $formBuilder->create(JobExperienceForm::class, ['model' => $jobExperience])->renderForm();
    }

    public function update(JobExperience $jobExperience, JobExperienceRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            JobExperience::query()->where('id', '!=', $jobExperience->getKey())->update(['is_default' => 0]);
        }

        $jobExperience->fill($request->input());
        $jobExperience->save();

        event(new UpdatedContentEvent(JOB_EXPERIENCE_MODULE_SCREEN_NAME, $request, $jobExperience));

        return $response
            ->setPreviousUrl(route('job-experiences.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(JobExperience $jobExperience, Request $request, BaseHttpResponse $response)
    {
        try {
            $jobExperience->delete();

            event(new DeletedContentEvent(JOB_EXPERIENCE_MODULE_SCREEN_NAME, $request, $jobExperience));

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
            $jobExperience = JobExperience::query()->findOrFail($id);
            $jobExperience->delete();
            event(new DeletedContentEvent(JOB_EXPERIENCE_MODULE_SCREEN_NAME, $request, $jobExperience));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
