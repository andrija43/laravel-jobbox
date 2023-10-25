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
use Botble\JobBoard\Forms\JobSkillForm;
use Botble\JobBoard\Http\Requests\JobSkillRequest;
use Botble\JobBoard\Models\JobSkill;
use Botble\JobBoard\Tables\JobSkillTable;
use Exception;
use Illuminate\Http\Request;

class JobSkillController extends BaseController
{
    public function index(JobSkillTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-skill.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::job-skill.create'));

        return $formBuilder->create(JobSkillForm::class)->renderForm();
    }

    public function store(JobSkillRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            JobSkill::query()->where('id', '>', 0)->update(['is_default' => 0]);
        }

        $jobSkill = JobSkill::query()->create($request->input());

        event(new CreatedContentEvent(JOB_SKILL_MODULE_SCREEN_NAME, $request, $jobSkill));

        return $response
            ->setPreviousUrl(route('job-skills.index'))
            ->setNextUrl(route('job-skills.edit', $jobSkill->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(JobSkill $jobSkill, FormBuilder $formBuilder, Request $request)
    {
        event(new BeforeEditContentEvent($request, $jobSkill));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $jobSkill->name]));

        return $formBuilder->create(JobSkillForm::class, ['model' => $jobSkill])->renderForm();
    }

    public function update(JobSkill $jobSkill, JobSkillRequest $request, BaseHttpResponse $response)
    {
        if ($request->input('is_default')) {
            JobSkill::query()->where('id', '!=', $jobSkill->getKey())->update(['is_default' => 0]);
        }

        $jobSkill->fill($request->input());
        $jobSkill->save();

        event(new UpdatedContentEvent(JOB_SKILL_MODULE_SCREEN_NAME, $request, $jobSkill));

        return $response
            ->setPreviousUrl(route('job-skills.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(JobSkill $jobSkill, Request $request, BaseHttpResponse $response)
    {
        try {
            $jobSkill->delete();

            event(new DeletedContentEvent(JOB_SKILL_MODULE_SCREEN_NAME, $request, $jobSkill));

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
            $jobSkill = JobSkill::query()->findOrFail($id);
            $jobSkill->delete();
            event(new DeletedContentEvent(JOB_SKILL_MODULE_SCREEN_NAME, $request, $jobSkill));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function getAllSkills()
    {
        return JobSkill::query()->pluck('name')->all();
    }
}
