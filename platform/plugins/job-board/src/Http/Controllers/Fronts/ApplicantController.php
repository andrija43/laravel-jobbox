<?php

namespace Botble\JobBoard\Http\Controllers\Fronts;

use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\Fronts\ApplicantForm;
use Botble\JobBoard\Http\Requests\EditJobApplicationRequest;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\JobApplication;
use Botble\JobBoard\Tables\Fronts\ApplicantTable;
use Botble\SeoHelper\Facades\SeoHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;

class ApplicantController extends Controller
{
    public function index(ApplicantTable $table)
    {
        PageTitle::setTitle(__('Applicants'));

        return $table->render(JobBoardHelper::viewPath('dashboard.table.base'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $jobApplication = JobApplication::query()
            ->select(['*'])
            ->whereHas('job.company.accounts', function (Builder $query) use ($account) {
                $query->where('account_id', $account->getKey());
            })
            ->with(['account'])
            ->where('id', $id)
            ->firstOrFail();

        $title = __('View applicant ":name"', ['name' => $jobApplication->full_name]);

        PageTitle::setTitle($title);

        SeoHelper::setTitle($title);

        return $formBuilder->create(ApplicantForm::class, ['model' => $jobApplication])->renderForm();
    }

    public function update(int|string $id, EditJobApplicationRequest $request, BaseHttpResponse $response)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $jobApplication = JobApplication::query()
            ->select(['*'])
            ->whereHas('job.company.accounts', function (Builder $query) use ($account) {
                $query->where('account_id', $account->getKey());
            })
            ->where('id', $id)
            ->firstOrFail();

        $jobApplication->fill($request->only(['status']));
        $jobApplication->save();

        event(new UpdatedContentEvent(JOB_APPLICATION_MODULE_SCREEN_NAME, $request, $jobApplication));

        return $response
            ->setPreviousUrl(route('public.account.applicants.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
