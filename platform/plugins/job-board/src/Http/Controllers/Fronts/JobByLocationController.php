<?php

namespace Botble\JobBoard\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
use Botble\Location\Models\City;
use Botble\Location\Models\State;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JobByLocationController extends BaseController
{
    public function __construct(protected BaseHttpResponse $response, protected JobInterface $jobRepository)
    {
    }

    public function city(string $slug, Request $request): Response|BaseHttpResponse
    {
        $city = City::query()
            ->wherePublished()
            ->where('slug', $slug)
            ->firstOrFail();

        SeoHelper::setTitle(__('Jobs in :location', ['location' => $city->name]));

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(SeoHelper::getTitle(), route('public.jobs-by-city', $city->slug));

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, CITY_MODULE_SCREEN_NAME, $city);

        $request->merge(['city_id' => $city->getKey()]);
        $requestQuery = JobBoardHelper::getJobFilters($request->input());

        $jobs = $this->jobRepository->getJobs(
            $requestQuery,
            [
                'paginate' => [
                    'per_page' => $request->integer('per_page', 12),
                ],
            ]
        );

        return Theme::scope('job-board.jobs', [
            'jobs' => $jobs,
            'ajaxUrl' => route('public.jobs-by-state', $city->slug),
            'actionUrl' => route('public.jobs-by-state', $city->slug),
        ])->render();
    }

    public function state(string $slug, Request $request): Response|BaseHttpResponse
    {
        $state = State::query()
            ->wherePublished()
            ->where('slug', $slug)
            ->firstOrFail();

        SeoHelper::setTitle(__('Jobs in :location', ['location' => $state->name]));

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(SeoHelper::getTitle(), route('public.jobs-by-state', $state->slug));

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, STATE_MODULE_SCREEN_NAME, $state);

        $request->merge(['state_id' => $state->getKey()]);
        $requestQuery = JobBoardHelper::getJobFilters($request->input());

        $jobs = $this->jobRepository->getJobs(
            $requestQuery,
            [
                'paginate' => [
                    'per_page' => $request->integer('per_page', 12),
                ],
            ]
        );

        return Theme::scope('job-board.jobs', [
            'jobs' => $jobs,
            'ajaxUrl' => route('public.jobs-by-state', $state->slug),
            'actionUrl' => route('public.jobs-by-state', $state->slug),
        ])->render();
    }
}
