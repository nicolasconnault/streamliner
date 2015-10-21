<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */

class Cron extends MY_Controller {
    public $restricted = false;
    function __construct() {
        parent::__construct();
    }

    public function run_tasks() {
        $class = new ReflectionClass('Cron');
        $methods = $class->getMethods(ReflectionMethod::IS_PRIVATE);

        foreach ($methods as $method) {
            echo "Starting task {$method->name}...\n";
            $this->{$method->name}();
            echo "Completed task {$method->name}\n";
        }
    }

    private function generate_maintenance_orders() {
        if ($contracts = $this->maintenance_contract_model->get()) {

            foreach ($contracts as $contract) {
                if ($this->maintenance_contract_model->needs_new_order($contract->id)) {
                    $this->maintenance_contract_model->generate_order($contract->id);
                }
            }
        }
    }
}
