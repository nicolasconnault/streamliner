<?php

class unit_test_fixtures {
    public $_ci;
    public $loaded_tables=array();

    public function __construct($ci) {
        $this->_ci = $ci;
    }

    public function reset_table($table) {
        $this->_ci->db->truncate($table);
        if (false !== ($key = array_search($table, $this->loaded_tables))) {
            unset($this->loaded_tables[$key]);
        }
    }

    public function reset_tables($tables=array()) {
        foreach ($tables as $table) {
            $this->reset_table($table);
        }
    }

    public function __call($function_name, $arguments=array()) {
        if (preg_match('/load_([a-z_]*)/', $function_name, $matches) && method_exists($this, "_".$function_name)) {
            $table = $matches[1];
            $this->_ci->db->truncate($table);
            $this->{'_'.$function_name}();
        } else {
            echo "The function $function_name() is not yet supported, edit the tests/models/fixtures.php file to write it.";
            return false;
        }
    }

    public function load_all() {
        $methods = get_class_methods('unit_test_fixtures');
        foreach ($methods as $method) {
            if (preg_match('/^_load_([a-zA-Z0-9\-\_]*)/i', $method, $matches) && $method != 'load_all') {
                $this->reset_table($matches[1]);
                $this->{$method}();
            }
        }
    }

    function _load_companies() {
        $this->_ci->db->query("INSERT INTO companies (name, code, role) VALUES
            ('test company 1', 'C1', ".COMPANY_ROLE_ENQUIRER."),
            ('test company 2', 'C2', ".COMPANY_ROLE_SUPPLIER."),
            ('test company 3', 'C3', ".COMPANY_ROLE_CUSTOMER."),
            ('test company 4', 'C4', ".COMPANY_ROLE_SUPPLIER.")
            ");
    }

    function _load_company_addresses() {
        $this->load_companies();
        $this->load_countries();
        $this->_ci->db->query("INSERT INTO company_addresses (company_id, type, country_id, address1, city, state, postcode, default_address) VALUES
            (1, ".COMPANY_ADDRESS_TYPE_BILLING.", 1, '431 funny street', 'FunnyVille', 'RD', '5934', 1),
            (1, ".COMPANY_ADDRESS_TYPE_CH.", 1, '40 Sad street', 'SadVille', 'SD', '5934', 0),
            (2, ".COMPANY_ADDRESS_TYPE_BILLING.", 3, '34 Cool road', 'ClownFurrow', 'CF', '3049', 1),
            (3, ".COMPANY_ADDRESS_TYPE_BILLING.", 3, '40 Whocares road', 'LewdDerby', 'LD', '9034', 1),
            (4, ".COMPANY_ADDRESS_TYPE_BILLING.", 2, '2B Sometimes road', 'Decentcottage', 'DC', '9340', 1),
            (4, ".COMPANY_ADDRESS_TYPE_SHIPPING.", 2, '30/43 Never road', 'PoxyTown', 'PX', '3904', 0)
            ");
    }

    function _load_countries() {
        $this->_ci->db->query("INSERT INTO countries (country, full_name, country_iso, capital, citizen, adjective, currency, currency_iso, currency_sub_unit, continent, dial_code, idd, ndd) VALUES
                                ('Afghanistan',	'Afghanistan', 'AF', 'Kabul', 'Afghan', 'Afghan', 'afghani', 'AFN', 'pul', '+93', '0', '0', '0'),
                                ('Albania', 'Republic of Albania', 'AL', 'Tirana', 'Albanian', 'Albanian', 'lek', 'ALL', 'qindar (pl. qindarka)', '+355', '0', '0', '0'),
                                ('Algeria', 'People\'s Democratic Republic of Algeria', 'DZ', 'Algiers', 'Algerian', 'Algerian', 'Algerian dinar', 'DZD', 'centime', '+213', '0', '0', '7')");

    }

    function _load_users() {
        $this->load_companies();
        $this->_ci->db->query("INSERT INTO users (username, company_id, password, first_name, salutation, surname) VALUES
            ('test_user1', 1, 'demo_user1', 'First Name 1', 'Mr.', 'Surname 1'),
            ('test_user2', 2, 'demo_user2', 'First Name 2', 'Mr.', 'Surname 2'),
            ('test_user3', 3, 'demo_user3', 'First Name 3', 'Mr.', 'Surname 3'),
            ('test_user4', 4, 'demo_user4', 'First Name 4', 'Mr.', 'Surname 4'),
            ('Staff 1', 5, 'staff1', 'Big', 'Mr.', 'Boss')
            ");
    }

    function _load_enquiries_enquiry_staff() {
        $this->load_users();
        $this->load_enquiries();
        $this->_ci->db->query("INSERT INTO enquiries_enquiry_staff (enquiry_id, user_id) VALUES (1,1),(2,1),(2,2),(2,3)");
    }

    function _load_user_addresses() {
        $this->load_users();
        $this->load_countries();
        $this->_ci->db->query("INSERT INTO user_addresses (user_id, type, country_id, address1, city, state, postcode, default_address) VALUES
            (1, ".USER_ADDRESS_TYPE_BILLING.", 4, '431 funny street', 'FunnyVille', 'RD', '5934', 1),
            (1, ".USER_ADDRESS_TYPE_SHIPPING.", 94, '40 Sad street', 'SadVille', 'SD', '9403', 0),
            (2, ".USER_ADDRESS_TYPE_BILLING.", 9, '94 Cool road', 'CoolVille', 'CD', '9304', 1)");
    }

    function _load_user_options() {
        $this->load_users();
        $this->_ci->db->query("INSERT INTO user_options (user_id, name, value, default_choice) VALUES
            (1, 'first_name_ch', 'value1', 1),
            (1, 'surname_ch', 'value2', 0),
            (1, 'option3', 'value3', 0),
            (2, 'first_name_ch', 'value4', 1),
            (2, 'surname_ch', 'value5', 1),
            (1, 'option1', 'value1', 1),
            (1, 'option1', 'value2', 0),
            (1, 'option1', 'value3', 0),
            (2, 'option1', 'value4', 1),
            (2, 'option2', 'value5', 1)");
    }

    function _load_user_contacts() {
        $this->load_users();
        $this->_ci->db->query("INSERT INTO user_contacts (user_id, type, contact, default_choice) VALUES
            (1, ".USERS_CONTACT_TYPE_EMAIL.", 'email1@test.com', 1),
            (1, ".USERS_CONTACT_TYPE_EMAIL.", 'email2@test.com', 0),
            (1, ".USERS_CONTACT_TYPE_PHONE.", '943709374', 0),
            (1, ".USERS_CONTACT_TYPE_FAX.", '0430742307', 0),
            (2, ".USERS_CONTACT_TYPE_EMAIL.", 'email4@test.com', 1),
            (2, ".USERS_CONTACT_TYPE_PHONE.", '950493048', 1),
            (1, ".USERS_CONTACT_TYPE_EMAIL.", 'email3@test.com', 0)
            ");
    }

    function _load_roles() {
        $this->_ci->db->query("INSERT INTO roles (name) VALUES ('test role 1'), ('test role 2')");
    }

    function _load_capabilities() {
        $this->_ci->db->query("INSERT INTO capabilities (name,type,dependson) VALUES
            ('site:doanything', 'write', NULL),
            ('enquiries:editenquiries', 'write', 1),
            ('enquiries:deleteenquiries', 'write', 1),
            ('enquiries:writeenquiries', 'write', 2),
            ('enquiries:viewenquiries', 'read', 4)");
    }

    function _load_roles_capabilities() {
        $this->load_roles();
        $this->load_capabilities();
        $this->_ci->db->query("INSERT INTO roles_capabilities (capability_id, role_id) VALUES (1,1),(2,2),(3,2)");
    }

    function _load_users_roles() {
        $this->load_users();
        $this->load_roles();
        $this->_ci->db->query("INSERT INTO users_roles (role_id, user_id) VALUES (1,1),(2,2),(1,3),(2, 1)");
    }

    function _load_enquiries() {
        $this->load_users();
        $this->load_enquiries_enquiry_products();
        $this->_ci->db->query("INSERT INTO enquiries (user_id, enquiry_product_id, currency) VALUES (1,1, ".CURRENCY_USD."),(1,2,".CURRENCY_USD."),(2,3, ".CURRENCY_AUD.")");
    }

    function _load_enquiries_enquiry_notes() {
		$this->load_enquiries();
        $this->load_users();
        $this->_ci->db->query("INSERT INTO enquiries_enquiry_notes (enquiry_id, user_id, message, type) VALUES
                (1,1, 'note 1','staff'),
                (1,1,'note 2','staff'),
                (1,2, 'note 3 by user 2','staff'),
                (1,null, 'System message', 'system'),
                (1,null, 'Anonymous message', 'anonymous'),
                (2,1, 'note 1 from user1','staff'),
                (2,1,'note 2 from user1','staff'),
                (2,2, 'note 3 by user 2','staff')");
    }

    function _load_enquiries_enquiry_products() {
        $this->_ci->db->query("INSERT INTO enquiries_enquiry_products (title, description)
                VALUES ('product 1', 'cool product 1'),
                ('product 2', 'cool product 2'),
                ('product 3', 'cool product 3'),
                ('product 3 MK2', 'cool product revised'),
                ('product 3 MK3', 'cool product revised again')
                ");
    }

    function _load_enquiries_outbound_quotations() {
        $this->load_users();
        $this->load_enquiries();
        $this->load_enquiries_enquiry_products();
        $this->_ci->db->query("INSERT INTO enquiries_outbound_quotations
                (staff_id,
                 enquiry_id,
                 product_id,
                 product_lead_time,
                 tool_lead_time,
                 tool_cost,
                 notes,
                 price,
                 unit,
                 currency,
                 min_qty,
                 freight,
                 delivery_terms,
                 delivery_point,
                 country_id,
                 payment_terms,
                 tool_cost_payment_terms,
                 sample_cost,
                 sample_time,
                 sample_payment_terms)
            VALUES (5,1,1,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17),
                   (5,3,4,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17),
                   (5,3,5,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17)");
    }

    function _load_codes_divisions() {
        $this->load_users();
        $this->_ci->db->query("INSERT INTO codes_divisions (name, revision_user_id, code) VALUES
            ('Chinasavvy', 1, 'CS'),
            ('Cridex', 1, 'CR'),
            ('Chinasavvy Projects', 2, 'CP'),
            ('Cridex Projects', 2, 'DV')");
    }

    function _load_codes_projects() {
        $this->load_users();
        $this->load_codes_divisions();
        $this->load_companies();

        $this->_ci->db->query("INSERT INTO codes_projects (company_id, number, division_id, name, revision_user_id, completed, creation_date) VALUES
            (1, 1000, 1, 'Project 1', 1, 1, ".mktime()."),
            (2, 1001, 1, 'Project 2', 1, 0, ".mktime(0,0,0,1,1,2010).")");

    }

    function _load_codes_parts() {
        $this->load_codes_projects();
        $this->load_users();
        $this->_ci->db->query("INSERT INTO codes_parts (project_id, number, name, revision_user_id, completed) VALUES
            (1, 100, 'Part 1', 1, 1),
            (1, 101, 'Part 2', 1, 1),
            (1, 102, 'Part 3', 2, 0),
            (1, 103, 'Part 4', 1, 1),
            (2, 100, 'Part A', 2, 0),
            (2, 101, 'Part B', 2, 1)
            ");
    }

    function _load_qc_projects() {
        $this->load_codes_parts();
        $this->load_users();
        $this->_ci->db->query("INSERT INTO qc_projects
                (part_id,
                sample_size,
                inspection_level,
                defect_critical_limit,
                defect_major_limit,
                defect_minor_limit,
                customer_code,
                batch_size,
                containschanges,
                approved_product_customer,
                approved_qc_customer,
                approved_project_admin,
                approved_product_admin,
                approved_qc_admin,
                result)
            VALUES
                (1, 200, 1, 1, 4, 8, 'TESTPROJECT1', 4000, 0, 1975748745, 1975748745, NULL, NULL, NULL, 1),
                (2, 400, 2, 0, 2, 5, 'TESTPROJECT0', 8000, 1, NULL, NULL, NULL, NULL, 1, 2)
                ");

    }

    /**
     * Files category is id 3 Dimensions is 4
     */
    function _load_qc_spec_categories() {
        $this->_ci->db->query("INSERT INTO qc_spec_categories (name, type) VALUES ('cat1', 1), ('cat2', 1), ('Files', 1), ('Dimensions', 1), ('cat3', 2), ('cat4', 2)");
    }

    function _load_qc_project_files() {
        $this->load_qc_projects();
        $this->_ci->db->query("INSERT INTO qc_project_files (project_id, file, hash) VALUES
                (1, 'testfile1.pdf', '83b1fff672637b8941aec9f58f74340f5123fa42.pdf'),
                (1, 'testfile2.pdf', 'ff6f58f74340f5172637b8941aec983b1f23fa42.pdf'),
                (2, 'testfile3.pdf', 'f743423fa0f583b1fff672637b8941aec9f58142.pdf')");

    }

    function _load_qc_project_parts() {
        $this->load_qc_projects();
        $this->_ci->db->query("INSERT INTO qc_project_parts (project_id, name, length, width, height) VALUES
                (1, 'QC Part 1', 5, 10, 20),
                (1, 'QC Part 2', 5, 10, 20),
                (2, 'QC Part 3', 5, 10, 20)
                ");
    }

    function _load_qc_project_related() {
        $this->load_qc_projects();
        $this->_ci->db->query("INSERT INTO qc_project_related (project_id, related_id) VALUES (1, 2)");
    }

    function _load_qc_specs() {
        $this->load_qc_spec_categories();
        $this->load_qc_project_files();
        $this->load_qc_jobs();
        $this->load_qc_project_parts();
        $this->_ci->db->query("INSERT INTO qc_specs (project_id, category_id, job_id, english_id, part_id, file_id, type, data, language, datatype, importance)
            VALUES
                (1, 1, NULL, NULL, NULL, NULL, 1, 'Text for spec 1', 1, 2, 3), " // Basic English product spec, minor importance
              ."(1, 1, NULL, 1, NULL, NULL, 1, 'Chinese Text for spec 1', 2, 2, 3), " // Basic Chinese product spec, minor importance
              ."(1, 3, NULL, NULL, NULL, 1, 1, 'testfile1.pdf', 1, 2, 3), " // File spec
              ."(1, 4, NULL, NULL, 1, NULL, 0, 'Text dimension 1', 1, 1, 3), " // Dimension spec in English
              ."(1, 4, NULL, 4, 1, NULL, 0, 'Chinese Text dimension 1', 1, 1, 3), " // Dimension spec in Chinese
              ."(2, 5, NULL, NULL, NULL, NULL, 1, 'Text for spec 2', 1, 2, 1), " // Basic QC spec, critical importance
              ."(2, 5, NULL, 6, NULL, NULL, 1, 'Chinese Text for spec 2', 2, 2, 1) " // Basic Chinese QC spec, critical importance
                );
    }

    function _load_qc_jobs() {
        $this->load_companies();
        $this->load_qc_projects();
        $this->load_qc_spec_categories();
        $this->load_users();
        $this->_ci->db->query("INSERT INTO qc_jobs (project_id, category_id, user_id, supplier_id, result)
            VALUES
                (1, 1, 1, 1, 1),
                (1, 2, 2, 2, 3),
                (2, 1, 2, 3, 2),
                (2, 1, 2, 3, 2)
                ");
    }

    function _load_qc_job_photos() {
        $this->load_qc_jobs();
        $this->_ci->db->query("INSERT INTO qc_job_photos (job_id, spec_id, file)
            VALUES
                (1, 1, 'photo1.jpg'),
                (1, 2, 'photo2.jpg'),
                (2, 3, 'photo3.jpg'),
                (2, 3, 'photo4.jpg')
                ");
    }

    function _load_qc_specs_results() {
        $this->load_qc_specs();
        $this->load_qc_jobs();

        $this->_ci->db->query("INSERT INTO qc_specs_results (job_id, specs_id, checked, defects)
            VALUES
                (1, 1, 0, 10),
                (1, 2, 1, 9),
                (2, 3, 0, 0),
                (2, 4, 1, 20),
                (3, 5, 0, 0)
            ");
    }

    function _load_qc_revisions() {
        $this->load_qc_projects();
        $this->load_users();
        $this->_ci->db->query("INSERT INTO qc_revisions (number, project_id, user_id) VALUES (1, 1, 1), (2, 1, 1), (4, 2, 1)");
    }

    function _load_qc_spec_revisions() {
        $this->load_qc_revisions();
        $this->_ci->db->query("INSERT INTO qc_spec_revisions (revision_id, project_id, type, number) VALUES (1, 1, 1, 1), (2, 1, 1, 2), (3, 2, 2, 1)");
    }
}
