<?php

namespace Botble\JobBoard\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\TagField;
use Botble\Base\Forms\FormAbstract;
use Botble\JobBoard\Enums\CustomFieldEnum;
use Botble\JobBoard\Enums\JobStatusEnum;
use Botble\JobBoard\Enums\ModerationStatusEnum;
use Botble\JobBoard\Enums\SalaryRangeEnum;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Http\Requests\JobRequest;
use Botble\JobBoard\Models\CareerLevel;
use Botble\JobBoard\Models\Category;
use Botble\JobBoard\Models\Currency;
use Botble\JobBoard\Models\CustomField;
use Botble\JobBoard\Models\DegreeLevel;
use Botble\JobBoard\Models\FunctionalArea;
use Botble\JobBoard\Models\Job;
use Botble\JobBoard\Models\JobExperience;
use Botble\JobBoard\Models\JobSkill;
use Botble\JobBoard\Models\JobType;

class JobForm extends FormAbstract
{
    public function buildForm(): void
    {
        Assets::addScripts(['input-mask'])
            ->addScriptsDirectly('vendor/core/plugins/job-board/js/components.js')
            ->addScriptsDirectly('vendor/core/plugins/job-board/js/job.js')
            ->addScriptsDirectly('vendor/core/plugins/job-board/js/employer-colleagues.js');

        Assets::usingVueJS();

        $model = $this->getModel();

        $currencies = Currency::query()
            ->orderBy('order')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();

        $skills = JobSkill::query()
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => $item->name])
            ->all();

        $selectedSkills = [];
        if ($skills && $model) {
            $selectedSkills = $model->skills()->pluck('job_skill_id')->all();
        }

        $jobTypes = JobType::query()
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => $item->name])
            ->all();

        $selectedJobTypes = [];
        if ($jobTypes && $model) {
            $selectedJobTypes = $model->jobTypes()->pluck('job_type_id')->all();
        }

        $careerLevels = CareerLevel::query()
            ->orderBy('order')
            ->orderBy('name')
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => $item->name])
            ->all();

        $degreeLevels = DegreeLevel::query()
            ->orderBy('order')
            ->orderBy('name')
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => $item->name])
            ->all();

        $jobExperiences = JobExperience::query()
            ->orderBy('order')
            ->orderBy('name')
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => $item->name])
            ->all();

        $functionalArea = FunctionalArea::query()
            ->orderBy('order')
            ->orderBy('name')
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => $item->name])
            ->all();

        $categories = Category::query()
            ->select('name', 'id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => $item->name])
            ->all();

        $selectedCategories = $model ? $model->categories()->pluck('category_id')->all() : [];

        $tags = null;

        if ($model) {
            $tags = $model
                ->tags()
                ->select('name')
                ->get()
                ->mapWithKeys(fn($item) => [$item->name => $item->name])
                ->implode(',');
        }

        $this
            ->setupModel(new Job())
            ->setValidatorClass(JobRequest::class)
            ->withCustomFields()
            ->addCustomField('tags', TagField::class)
            ->addCustomField('multiCheckList', MultiCheckListField::class)
            ->addCustomField('tags', TagField::class)
            ->add('name', 'text', [
                'label' => __('Job title'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('core/base::forms.description'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 500,
                ],
            ])
            ->add('is_featured', 'onOff', [
                'label' => trans('core/base::forms.is_featured'),
                'default_value' => false,
            ])
            ->add('content', 'editor', [
                'label' => trans('core/base::forms.content'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                ],
            ])
            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('company_id', 'autocomplete', [
                'label' => __('Company'),
                'label_attr' => [
                    'class' => 'control-label required',
                ],
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'attr' => [
                    'id' => 'company_id',
                    'data-url' => route('companies.list'),
                ],
                'choices' => $model && $model->company_id ?
                    [
                        $model->company->id => $model->company->name,
                    ]
                    :
                    ['' => __('Select company...')],
                'help_block' => [
                    'text' => __('Not in the list? ') . Html::link(
                            '#',
                            __('Add new'),
                            ['data-bs-toggle' => 'modal', 'data-bs-target' => '#add-company-modal']
                        ),
                ],
            ])
            ->add('number_of_positions', 'number', [
                'label' => __('Number of positions'),
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'attr' => [
                    'placeholder' => __('Number of positions'),
                ],
                'default_value' => 1,
            ])
            ->add('rowClose2', 'html', [
                'html' => '</div>',
            ]);

        if (JobBoardHelper::isZipCodeEnabled()) {
            $this->add('zip_code', 'text', [
                'label' => __('Zip code'),
                'attr' => [
                    'placeholder' => __('Zip code'),
                    'data-counter' => 20,
                ],
            ]);
        }

        $this->add('address', 'text', [
            'label' => __('Address'),
            'attr' => [
                'placeholder' => __('Address'),
                'data-counter' => 120,
            ],
        ]);

        if (is_plugin_active('location')) {
            $this->add('location', 'selectLocation', [
                'wrapper' => [
                    'class' => 'mb-0 col-sm-4',
                ],
                'wrapperClassName' => 'row g-1',
            ]);
        }

        $this
            ->add('rowOpen4', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('latitude', 'text', [
                'label' => __('Latitude'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-6',
                ],
                'attr' => [
                    'placeholder' => 'Ex: 1.462260',
                    'data-counter' => 25,
                ],
                'help_block' => [
                    'tag' => 'a',
                    'text' => __('Go here to get Latitude from address.'),
                    'attr' => [
                        'href' => 'https://www.latlong.net/convert-address-to-lat-long.html',
                        'target' => '_blank',
                        'rel' => 'nofollow',
                    ],
                ],
            ])
            ->add('longitude', 'text', [
                'label' => __('Longitude'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-6',
                ],
                'attr' => [
                    'placeholder' => 'Ex: 103.812530',
                    'data-counter' => 25,
                ],
                'help_block' => [
                    'tag' => 'a',
                    'text' => __('Go here to get Longitude from address.'),
                    'attr' => [
                        'href' => 'https://www.latlong.net/convert-address-to-lat-long.html',
                        'target' => '_blank',
                        'rel' => 'nofollow',
                    ],
                ],
            ])
            ->add('rowClose4', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('salary_from', 'text', [
                'label' => __('Salary from'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'id' => 'salary-from',
                    'placeholder' => __('Salary from'),
                    'class' => 'form-control input-mask-number',
                ],
            ])
            ->add('salary_to', 'text', [
                'label' => __('Salary to'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'attr' => [
                    'id' => 'salary-to',
                    'placeholder' => __('Salary to'),
                    'class' => 'form-control input-mask-number',
                ],
            ])
            ->add('salary_range', 'customSelect', [
                'label' => __('Salary Range'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'choices' => SalaryRangeEnum::labels(),
            ])
            ->add('currency_id', 'customSelect', [
                'label' => __('Currency'),
                'wrapper' => [
                    'class' => 'form-group col-md-3',
                ],
                'choices' => $currencies,
            ])
            ->add('rowClose', 'html', [
                'html' => '</div>',
            ])
            ->add('hide_salary', 'onOff', [
                'label' => __('Hide salary?'),
                'default_value' => false,
            ])
            ->add('rowOpen5', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('start_date', 'datePicker', [
                'label' => __('Start date'),
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'value' => $model ? BaseHelper::formatDate($model->start_date) : '',
            ])
            ->add('application_closing_date', 'datePicker', [
                'label' => __('Application closing date'),
                'wrapper' => [
                    'class' => 'form-group col-md-6',
                ],
                'value' => $model ? BaseHelper::formatDate($model->application_closing_date) : '',
            ])
            ->add('rowClose5', 'html', [
                'html' => '</div>',
            ])
            ->add('apply_url', 'text', [
                'label' => __('URL to Apply Off-Site'),
                'attr' => [
                    'placeholder' => __('Ex: https://example.com'),
                    'data-counter' => 255,
                ],
                'help_block' => [
                    'text' => __('Provide a URL if you prefer job seeker to apply directly on your website.'),
                ],
            ])
            ->add('hide_company', 'onOff', [
                'label' => __('Hide my company details (post anonymously)?'),
                'default_value' => false,
            ])
            ->add('never_expired', 'onOff', [
                'label' => __('Never expired?'),
                'default_value' => true,
            ])
            ->add('auto_renew', 'onOff', [
                'label' => __(
                    'Renew automatically (you will be charged again in :days days)?',
                    ['days' => JobBoardHelper::jobExpiredDays()]
                ),
                'default_value' => true,
                'wrapper' => [
                    'class' => 'form-group ' . (! $model || $model->never_expired ? ' hidden' : null),
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'choices' => JobStatusEnum::labels(),
            ])
            ->add('moderation_status', 'customSelect', [
                'label' => trans('plugins/job-board::job.moderation_status'),
                'choices' => ModerationStatusEnum::labels(),
            ])
            ->add('is_freelance', 'onOff', [
                'label' => __('Is freelance?'),
                'default_value' => false,
            ])
            ->add('categories[]', 'multiCheckList', [
                'label' => __('Job categories'),
                'choices' => $categories,
                'value' => old('categories', $selectedCategories),
            ]);

        if (count($skills) > 0) {
            $this->add('skills[]', 'multiCheckList', [
                'label' => __('Job skills'),
                'choices' => $skills,
                'value' => old('skills', $selectedSkills),
            ]);
        }

        if (count($jobTypes) > 0) {
            $this->add('jobTypes[]', 'multiCheckList', [
                'label' => __('Job types'),
                'choices' => $jobTypes,
                'value' => old('jobTypes', $selectedJobTypes),
            ]);
        }

        $this
            ->add('career_level_id', 'customSelect', [
                'label' => __('Career level'),
            ])
            ->add('functional_area_id', 'customSelect', [
                'label' => __('Functional area'),
                'choices' => [0 => __('-- select --')] + $functionalArea,
            ])
            ->add('degree_level_id', 'customSelect', [
                'label' => __('Degree level'),
                'choices' => [0 => __('-- select --')] + $degreeLevels,
            ])
            ->add('job_experience_id', 'customSelect', [
                'label' => __('Job experience'),
                'choices' => [0 => __('-- select --')] + $jobExperiences,
            ])
            ->add('tag', 'tags', [
                'label' => trans('plugins/job-board::job.tags'),
                'value' => $tags,
                'attr' => [
                    'placeholder' => trans('plugins/job-board::job.write_some_tags'),
                    'data-url' => route('job-board.tag.all'),
                ],
            ])
            ->setBreakFieldPoint('status')
            ->addMetaBoxes([
                'add-company' => [
                    'title' => null,
                    'content' => view('plugins/job-board::partials.add-company', ['model' => $model]),
                    'priority' => 0,
                    'attributes' => ['style' => 'display: none'],
                ],
                'colleagues' => [
                    'title' => __('Add colleagues'),
                    'content' => view('plugins/job-board::partials.colleagues', ['model' => $model]),
                    'priority' => 0,
                ],
            ]);

        if (JobBoardHelper::isEnabledCustomFields()) {
            Assets::addScriptsDirectly('vendor/core/plugins/job-board/js/custom-fields.js');

            $customFields = CustomField::query()->select(['name', 'id', 'type'])->get();

            $this->addMetaBoxes([
                'custom_fields_box' => [
                    'title' => trans('plugins/job-board::custom-fields.name'),
                    'content' => view('plugins/job-board::custom-fields.custom-fields', [
                        'options' => CustomFieldEnum::labels(),
                        'customFields' => $customFields,
                        'model' => $model,
                        'ajax' => is_in_admin(true) ? route('job-board.custom-fields.get-info') : route(
                            'public.account.custom-fields.get-info'
                        ),
                    ]),
                    'priority' => 0,
                ],
            ]);
        }
    }
}
