<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Models\Review;
use Botble\JobBoard\Tables\ReviewTable;
use Exception;
use Illuminate\Http\Request;

class ReviewController
{
    public function index(ReviewTable $dataTable)
    {
        PageTitle::setTitle(trans('plugins/job-board::review.name'));

        Assets::addStylesDirectly('vendor/core/plugins/job-board/css/review.css');

        return $dataTable->renderTable();
    }

    public function destroy(Review $review, Request $request, BaseHttpResponse $response)
    {
        try {
            $review->delete();

            event(new DeletedContentEvent(REVIEW_MODULE_SCREEN_NAME, $request, $review));

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
            $review = Review::query()->findOrFail($id);
            $review->delete();

            event(new DeletedContentEvent(REVIEW_MODULE_SCREEN_NAME, $request, $review));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
