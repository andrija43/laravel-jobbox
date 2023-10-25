<?php

namespace Botble\JobBoard\Http\Controllers\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Http\Requests\AvatarRequest;
use Botble\JobBoard\Http\Requests\SettingRequest;
use Botble\JobBoard\Http\Requests\UpdatePasswordRequest;
use Botble\JobBoard\Http\Requests\UploadResumeRequest;
use Botble\JobBoard\Http\Resources\ActivityLogResource;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\AccountActivityLog;
use Botble\JobBoard\Models\AccountEducation;
use Botble\JobBoard\Models\AccountExperience;
use Botble\JobBoard\Models\JobSkill;
use Botble\JobBoard\Models\Tag;
use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFile;
use Botble\Media\Services\ThumbnailService;
use Botble\Optimize\Facades\OptimizerHelper;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function __construct()
    {
        OptimizerHelper::disable();
    }

    public function getOverview()
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        SeoHelper::setTitle($account->name);
        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('My Profile'), route('public.account.overview'))
            ->add($account->name);

        $educations = AccountEducation::query()
            ->where('account_id', $account->id)
            ->get();

        $experiences = AccountExperience::query()
            ->where('account_id', $account->id)
            ->get();

        $data = compact('account', 'educations', 'experiences');

        return JobBoardHelper::scope('account.overview', $data);
    }

    public function getSettings()
    {
        SeoHelper::setTitle(__('Account settings'));

        Assets::addScriptsDirectly('vendor/core/core/js-validation/js/js-validation.js')
            ->addScriptsDirectly('//cdnjs.cloudflare.com/ajax/libs/dropzone/4.3.0/dropzone.js')
            ->addStylesDirectly('//cdnjs.cloudflare.com/ajax/libs/dropzone/4.3.0/dropzone.css');

        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $jobSkills = [];
        $jobTags = [];
        $selectedJobSkills = [];
        $selectedJobTags = [];

        if ($account->isJobSeeker()) {
            $selectedJobSkills = $account->favoriteSkills()->pluck('jb_job_skills.id')->all();

            $jobSkills = JobSkill::query()
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->select(['id', 'name'])
                ->get();

            $selectedJobTags = $account->favoriteTags()->pluck('jb_tags.id')->all();

            $jobTags = Tag::query()
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->select(['id', 'name'])
                ->get();
        }

        return JobBoardHelper::scope('account.settings.index', compact('account', 'jobSkills', 'jobTags', 'selectedJobSkills', 'selectedJobTags'));
    }

    public function postSettings(SettingRequest $request, BaseHttpResponse $response)
    {
        $account = auth('account')->user();
        $data = $request->validated();
        Arr::forget($data, ['resume', 'cover_letter']);

        if ($request->hasFile('resume')) {
            $result = RvMedia::handleUpload($request->file('resume'), 0, $account->upload_folder);

            if (! $result['error']) {
                if ($path = $account->resume) {
                    Storage::disk('public')->delete($path);
                }

                $data['resume'] = $result['data']->url;
            }
        }

        if ($request->hasFile('cover_letter')) {
            $result = RvMedia::handleUpload($request->file('cover_letter'), 0, $account->upload_folder);

            if (! $result['error']) {
                if ($path = $account->cover_letter) {
                    Storage::disk('public')->delete($path);
                }

                $data['cover_letter'] = $result['data']->url;
            }
        }

        event(new UpdatedContentEvent(ACCOUNT_MODULE_SCREEN_NAME, $request, $account));

        $account->fill($data);
        $account->save();

        AccountActivityLog::query()->create(['action' => 'update_setting']);

        return $response
            ->setNextUrl(route('public.account.settings'))
            ->setMessage(__('Update profile successfully!'));
    }

    public function getSecurity()
    {
        SeoHelper::setTitle(__('Security'));

        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        return JobBoardHelper::scope('account.settings.security', compact('account'));
    }

    public function postSecurity(UpdatePasswordRequest $request, BaseHttpResponse $response)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        if (! Hash::check($request->input('old_password'), $account->getAuthPassword())) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/job-board::dashboard.current_password_incorrect'));
        }

        $account->update([
            'password' => Hash::make($request->input('password')),
        ]);

        AccountActivityLog::query()->create(['action' => 'update_security']);

        return $response->setMessage(trans('plugins/job-board::dashboard.password_update_success'));
    }

    public function postAvatar(AvatarRequest $request, ThumbnailService $thumbnailService, BaseHttpResponse $response)
    {
        try {
            $account = auth('account')->user();

            $result = RvMedia::handleUpload($request->file('avatar_file'), 0, $account->upload_folder);

            if ($result['error']) {
                return $response->setError()->setMessage($result['message']);
            }

            $avatarData = json_decode($request->input('avatar_data'));

            $file = $result['data'];

            $thumbnailService
                ->setImage(RvMedia::getRealPath($file->url))
                ->setSize((int)$avatarData->width, (int)$avatarData->height)
                ->setCoordinates((int)$avatarData->x, (int)$avatarData->y)
                ->setDestinationPath(File::dirname($file->url))
                ->setFileName(File::name($file->url) . '.' . File::extension($file->url))
                ->save('crop');

            $avatar = MediaFile::query()->find($account->avatar_id);

            if ($avatar) {
                $avatar->forceDelete();
            }

            $account->avatar_id = $file->id;
            $account->save();

            AccountActivityLog::query()->create([
                'action' => 'changed_avatar',
            ]);

            return $response
                ->setMessage(trans('plugins/job-board::dashboard.update_avatar_success'))
                ->setData(['url' => RvMedia::url($file->url)]);
        } catch (Exception $ex) {
            return $response
                ->setError()
                ->setMessage($ex->getMessage());
        }
    }

    public function getActivityLogs(BaseHttpResponse $response)
    {
        $activities = AccountActivityLog::query()
            ->where('account_id', auth('account')->user()->getAuthIdentifier())
            ->latest('created_at')
            ->paginate(10);

        return $response->setData(ActivityLogResource::collection($activities))->toApiResponse();
    }

    public function postUpload(UploadResumeRequest $request, BaseHttpResponse $response)
    {
        $account = auth('account')->user();

        $result = RvMedia::handleUpload($request->file('file'), 0, $account->upload_folder);

        if ($result['error']) {
            return $response->setError();
        }

        return $response->setData($result['data']);
    }

    public function postUploadFromEditor(Request $request)
    {
        $account = auth('account')->user();

        return RvMedia::uploadFromEditor($request, 0, $account->upload_folder);
    }

    public function postUploadResume(
        UploadResumeRequest $request,
        BaseHttpResponse $response
    ) {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $result = RvMedia::handleUpload($request->file('file'), 0, $account->upload_folder);

        if ($result['error']) {
            return $response->setError();
        }

        $account->update(['resume' => $result['data']->url]);

        $url = null;
        if (! $account->phone) {
            $url = route('public.account.settings');
        }

        return $response->setData(compact('url'));
    }
}
