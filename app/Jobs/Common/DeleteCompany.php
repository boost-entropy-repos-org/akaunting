<?php

namespace App\Jobs\Common;

use App\Abstracts\Job;
use App\Traits\Users;

class DeleteCompany extends Job
{
    use Users;

    protected $company;

    protected $active_company_id;

    /**
     * Create a new job instance.
     *
     * @param  $request
     */
    public function __construct($company, $active_company_id)
    {
        $this->company = $company;
        $this->active_company_id = $active_company_id;
    }

    /**
     * Execute the job.
     *
     * @return boolean|Exception
     */
    public function handle()
    {
        $this->authorize();

        \DB::transaction(function () {
            $this->deleteRelationships($this->company, [
                'accounts', 'documents', 'document_histories', 'document_items', 'document_item_taxes', 'document_totals', 'categories',
                'contacts', 'currencies', 'dashboards', 'email_templates', 'items', 'modules', 'module_histories', 'reconciliations',
                'recurring', 'reports', 'settings', 'taxes', 'transactions', 'transfers', 'widgets',
            ]);

            $this->company->delete();
        });

        return true;
    }

    /**
     * Determine if this action is applicable.
     *
     * @return void
     */
    public function authorize()
    {
        // Can't delete active company
        if ($this->company->id == $this->active_company_id) {
            $message = trans('companies.error.delete_active');

            throw new \Exception($message);
        }

        // Check if user can access company
        if (!$this->isUserCompany($this->company->id)) {
            $message = trans('companies.error.not_user_company');

            throw new \Exception($message);
        }
    }
}
