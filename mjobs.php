<?php

class Mjobs extends \CDetectedModel {

    public static $db;
    public $_table_name  = 'jobs';

    private $_mod_access = true;
    private $_type;

    public function init() {
        self::$db = \init::app()->getDBConnector();

        if (!$this->_mod_access) {
            throw new \CException(\init::t('init', 'Access denied!'));
        }

        $this->_type       = \init::app()->_getPanel();
        $this->_pk         = 'JobsID';
        $this->_table_name = 'jobs';
    }

    public function attributeNames() {

    }

    public function getCountJobs() {
        $_query = self::$db
                ->query("SELECT COUNT(*) AS count FROM jobs ;", array('target' => 'main'), array())
                ->fetchAssoc();
        return $_query['count'];
    }

    public function getCountJobsByDate($date_start, $date_stop) {
        $_query = self::$db
                ->query("SELECT COUNT(*) AS count "
                        . "FROM jobs "
                        . "WHERE TimeCreated BETWEEN STR_TO_DATE('".$date_start."', '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('".$date_stop."', '%Y-%m-%d %H:%i:%s');", array('target' => 'main'), array())
                ->fetchAssoc();
        return $_query['count'];
    }

    public function getCountStatusJobs() {
        $_query = self::$db
                ->query("SELECT status, COUNT(*) AS count "
                        . "FROM jobs "
                        . "GROUP BY status;", array('target' => 'main'), array())
                ->fetchAll();
        return $_query;
    }

    public function getCountPlacementJobs() {
        $_query = self::$db
                ->query("SELECT job_placement, COUNT(*) AS count "
                        . "FROM jobs "
                        . "GROUP BY job_placement;", array('target' => 'main'), array())
                ->fetchAll();
        return $_query;
    }

    public function getCountSelectedProfessionJobs() {
        $_query = self::$db
                ->query("SELECT COUNT(*) AS count "
                        . "FROM jobs "
                        . "WHERE profession != '';", array('target' => 'main'), array())
                ->fetchAssoc();
        return $_query['count'];
    }

    public function getCountIndexedJobs() {
        $_query = self::$db
                ->query("SELECT COUNT(*) AS count "
                        . "FROM jobs "
                        . "WHERE indexed = 1;", array('target' => 'main'), array())
                ->fetchAssoc();
        return $_query['count'];
    }

    public function getCountAutoIndexedJobs() {
        $_query = self::$db
                ->query("SELECT COUNT(*) AS count "
                        . "FROM jobs "
                        . "WHERE indexed = 0 AND similar_words_index != '';", array('target' => 'main'), array())
                ->fetchAssoc();
        return $_query['count'];
    }

    public function getJobsID($_id) {
        $_jobs = false;

        if ((int) $_id) {
            $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
                'JobsID',
                'jobs_title',
                'jobs_url',
                'similar_words_index',
                'indexed',
                'catalogs',
                'profession',
                'UserID',
                'CompanyID',
                'company_name',
                'company_logo',
                'company_website',
                'TimeCreated',
                'TimeSaved',
                'jobs_citi',
                'business_trip',
                'emigration',
                'employment_type_full_time',
                'employment_type_part_time',
                'employment_type_telecommuting	',
                'salary',
                'phone',
                'hide_phone',
                'jobs_coment_salary',
                'jobs_experience',
                'jobs_education',
                'invite_student',
                'jobs_text',
                'contact',
                'email',
                'job_placement',
                'status',
                'jobs_work_url',
                'new_reviews_count'
            ));

            $sql->condition('JobsID', (int) $_id, '=');
            $_jobs = $sql->execute()->fetchAssoc();
        }

        return $_jobs;
    }

    public function getJobsStatusInfo($_id) {
        $_jobs = false;

        if ((int) $_id) {
            $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
                'JobsID',
                'profession',
                'jobs_citi',
                'job_placement'
            ));

            $sql->condition('JobsID', (int) $_id, '=');
            $_jobs = $sql->execute()->fetchAssoc();
        }

        return $_jobs;
    }

    public function getJobs() {
        $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
            'JobsID',
            'jobs_title',
            'jobs_url',
            'similar_words_index',
            'indexed',
            'catalogs',
            'profession',
            'UserID',
            'CompanyID',
            'company_name',
            'company_logo',
            'company_website',
            'TimeCreated',
            'TimeSaved',
            'jobs_citi',
            'employment_type_full_time',
            'employment_type_part_time',
            'employment_type_telecommuting',
            'salary',
            'jobs_coment_salary',
            'jobs_experience',
            'jobs_education',
            'jobs_text',
            'contact',
            'email',
            'phone',
            'job_placement',
            'jobs_work_url',
            'new_reviews_count'
        ));

        $sql->orderBy('job_placement', 'DESC');
        $sql->orderBy('JobsID', 'DESC');
        $_jobs = $sql->execute()->fetchAll();

        return $_jobs;
    }

    public function getPaginatedJobs($page = 1, $limit = 20) {
        $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
            'JobsID'
        ));

        $result = $sql->execute()->fetchAll();

        $count      = count($result);
        $page_count = intval(ceil($count / $limit));
        $start      = ($page - 1) * $limit;

        $paginated_sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
            'JobsID',
            'jobs_title',
            'jobs_url',
            'similar_words_index',
            'indexed',
            'catalogs',
            'profession',
            'UserID',
            'CompanyID',
            'company_name',
            'company_logo',
            'company_website',
            'TimeCreated',
            'TimeSaved',
            'jobs_citi',
            'employment_type_full_time',
            'employment_type_part_time',
            'employment_type_telecommuting',
            'salary',
            'jobs_coment_salary',
            'jobs_experience',
            'jobs_education',
            'jobs_text',
            'contact',
            'email',
            'phone',
            'job_placement',
            'jobs_work_url',
            'new_reviews_count'
        ));
        $paginated_sql->condition('status', 'published', '!=');
        $paginated_sql->orderBy('TimeSaved', 'desc');
        $paginated_sql->orderBy('JobsID', 'desc');
        $paginated_sql->range($start, $limit);

        $paginated_result = array(
            'data'       => $paginated_sql->execute()->fetchAll(),
            'count'      => $count,
            'page_count' => $page_count,
            'limit'      => $limit
        );

        return $paginated_result;
    }

    public function getActualJobs() {
        $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
            'JobsID',
            'jobs_title',
            'jobs_url',
            'similar_words_index',
            'indexed',
            'catalogs',
            'profession',
            'UserID',
            'CompanyID',
            'company_name',
            'company_logo',
            'company_website',
            'TimeCreated',
            'TimeSaved',
            'jobs_citi',
            'employment_type_full_time',
            'employment_type_part_time',
            'employment_type_telecommuting',
            'salary',
            'jobs_coment_salary',
            'jobs_experience',
            'jobs_education',
            'jobs_text',
            'contact',
            'email',
            'phone',
            'job_placement',
            'jobs_work_url',
            'new_reviews_count'
        ));

        $sql->condition(db_and()
                ->condition('job_placement', 'standard', '=')
        );

        $sql->orderBy('job_placement', 'desc');
        $sql->orderBy('JobsID',        'desc');

        return $sql->execute()->fetchAll();
    }

    public function getNoIndexedJobs() {
        $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
            'JobsID',
            'jobs_title',
            'jobs_url',
            'similar_words_index',
            'indexed',
            'job_placement',
            'jobs_work_url'
        ));

        $sql->condition('indexed', 0, '=');
        $sql->orderBy('job_placement', 'DESC');
        $sql->orderBy('JobsID', 'DESC');
        $sql->range(0, 20);
        $_jobs = $sql->execute()->fetchAll();

        return $_jobs;
    }

    public function getUserJobs($user_id) {
        $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
            'JobsID',
            'jobs_title',
            'jobs_url',
            'similar_words_index',
            'indexed',
            'catalogs',
            'profession',
            'UserID',
            'CompanyID',
            'company_name',
            'company_logo',
            'company_website',
            'TimeCreated',
            'TimeSaved',
            'jobs_citi',
            'employment_type_full_time',
            'employment_type_part_time',
            'employment_type_telecommuting',
            'salary',
            'jobs_coment_salary',
            'jobs_experience',
            'jobs_education',
            'jobs_text',
            'contact',
            'email',
            'phone',
            'job_placement',
            'jobs_work_url',
            'new_reviews_count'
        ));

        $sql->condition('UserID', (int) $user_id, '=');
        $_jobs = $sql->execute()->fetchAll();

        return $_jobs;
    }

    public function getEmployersJobs($user_id, $employers_ids = array()) {
        if (is_array($employers_ids) && count($employers_ids) > 0) {
            $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
                'JobsID',
                'jobs_title',
                'jobs_url',
                'similar_words_index',
                'indexed',
                'catalogs',
                'profession',
                'UserID',
                'CompanyID',
                'company_name',
                'company_logo',
                'company_website',
                'TimeCreated',
                'TimeSaved',
                'jobs_citi',
                'employment_type_full_time',
                'employment_type_part_time',
                'employment_type_telecommuting',
                'salary',
                'jobs_coment_salary',
                'jobs_experience',
                'jobs_education',
                'jobs_text',
                'contact',
                'email',
                'phone',
                'job_placement',
                'jobs_work_url',
                'new_reviews_count'
            ));

            $sql->condition(db_and()
                    ->condition('UserID',    $user_id,    '!=')
                    ->condition('UserID', $employers_ids, 'IN')
            );

            $_jobs = $sql->execute()->fetchAll();

            return $_jobs;
        }

        return false;
    }

    public function getCompanyJobs($company_id) {
        $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
            'JobsID',
            'jobs_title',
            'jobs_url',
            'similar_words_index',
            'indexed',
            'catalogs',
            'profession',
            'UserID',
            'CompanyID',
            'company_name',
            'company_logo',
            'company_website',
            'TimeCreated',
            'TimeSaved',
            'jobs_citi',
            'employment_type_full_time',
            'employment_type_part_time',
            'employment_type_telecommuting',
            'salary',
            'jobs_coment_salary',
            'jobs_experience',
            'jobs_education',
            'jobs_text',
            'contact',
            'email',
            'phone',
            'job_placement',
            'jobs_work_url',
            'new_reviews_count'
        ));

        $sql->condition('CompanyID', (int) $company_id, '=');
        $sql->condition('job_placement', 'standard', '=');
        $_jobs = $sql->execute()->fetchAll();

        return $_jobs;
    }

    public function findUserByJobID($job_id) {
        if ((int) $job_id) {
            $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
                'JobsID',
                'UserID'
            ));

            $sql->condition('JobsID', (int) $job_id, '=');
            $data = $sql->execute()->fetchAssoc();

            return $data['UserID'];
        }

        return false;
    }

    public function getJobByIDs($job_ids) {
        $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
            'JobsID',
            'jobs_title',
            'jobs_url',
            'similar_words_index',
            'indexed',
            'catalogs',
            'profession',
            'UserID',
            'CompanyID',
            'company_name',
            'company_logo',
            'company_website',
            'TimeCreated',
            'TimeSaved',
            'jobs_citi',
            'employment_type_full_time',
            'employment_type_part_time',
            'employment_type_telecommuting',
            'salary',
            'jobs_coment_salary',
            'jobs_experience',
            'jobs_education',
            'jobs_text',
            'contact',
            'email',
            'phone',
            'job_placement',
            'jobs_work_url',
            'new_reviews_count'
        ));

        if (is_array($job_ids) && count($job_ids) > 0) {
            $sql->condition('JobsID', $job_ids, 'IN');
            $jobs = $sql->execute()->fetchAll();

            return $jobs;
        } elseif ((int) $job_ids) {
            $sql->condition('JobsID', $job_ids, '=');
            $job = $sql->execute()->fetchAssoc();

            return $job;
        }

        return false;
    }

    public function getJobsForLandingPage($page = 1, $limit = 10, $job_ids = array(), $catalog_id = null, $user_id = false) {
        $paginated_result = false;

        if (is_array($job_ids) && count($job_ids) > 0 && (int) $catalog_id) {
            $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
                'JobsID',
                'job_placement'
            ));

            $sql->condition(db_and()
                ->condition('jobs.JobsID',        $job_ids,     'IN')
                ->condition('jobs.job_placement', 'hidden',     '!=')
                ->condition('jobs.status',        'moderation', '!=')
            );

            $sql->join('jobs_catalog', 'jc', 'jc.CatalogID = ' . (int) $catalog_id . ' AND jc.JobsID = jobs.JobsID');

            $result = $sql->execute()->fetchAll();

            $count      = count($result);
            $page_count = intval(ceil($count / $limit));
            $start      = ($page - 1) * $limit;

            $paginated_sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
                'JobsID',
                'jobs_title',
                'jobs_url',
                'similar_words_index',
                'indexed',
                'catalogs',
                'profession',
                'UserID',
                'CompanyID',
                'company_name',
                'company_logo',
                'company_website',
                'TimeCreated',
                'TimeSaved',
                'jobs_citi',
                'employment_type_full_time',
                'employment_type_part_time',
                'employment_type_telecommuting',
                'salary',
                'jobs_coment_salary',
                'jobs_experience',
                'jobs_education',
                'jobs_text',
                'contact',
                'email',
                'phone',
                'job_placement',
                'jobs_work_url',
                'new_reviews_count'
            ));

            $paginated_sql->condition(db_and()
                ->condition('jobs.JobsID',        $job_ids,     'IN')
                ->condition('jobs.job_placement', 'hidden',     '!=')
                ->condition('jobs.status',        'moderation', '!=')
            );

            $paginated_sql->join('jobs_catalog', 'jc', 'jc.CatalogID = ' . (int) $catalog_id . ' AND jc.JobsID = jobs.JobsID');

            if ($user_id){
                $paginated_sql->leftJoin('interesting_jobs', 'i_jobs', 'i_jobs.JobsID=jobs.JobsID AND i_jobs.UserID=' . $user_id);
                $paginated_sql->fields('i_jobs', array('InterestingJobsID'));
            }
            
            $paginated_sql->orderBy('job_placement', 'desc');
            $paginated_sql->orderBy('TimeSaved',     'desc');
            $paginated_sql->orderBy('JobsID',        'desc');

            $paginated_sql->range($start, $limit);

            $paginated_result = array(
                'data'       => $paginated_sql->execute()->fetchAll(),
                'count'      => $count,
                'page_count' => $page_count,
                'limit'      => $limit
            );
        }

        return $paginated_result;
    }

    public function getJobsForLandingPageByProfession($page = 1, $limit = 10, $profession = null, $city_id = null, $user_id = false) {
        $paginated_result = false;

        if (!empty($profession)) {
            $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
                'JobsID'
            ));

            $sql->condition(db_and()
                ->condition('jobs.profession', $profession, '=')
                ->condition('job_placement', 'hidden', '!=')
                ->condition('jobs.status',     'moderation', '!=')
            );

            if ((int) $city_id) {
                $sql->join('jobs_city', 'jc', 'jc.CityID = ' . (int) $city_id . ' AND jc.JobsID = jobs.JobsID');
            }

            $result = $sql->execute()->fetchAll();

            $count      = count($result);
            $page_count = intval(ceil($count / $limit));
            $start      = ($page - 1) * $limit;

            $paginated_sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
                'JobsID',
                'jobs_title',
                'jobs_url',
                'similar_words_index',
                'indexed',
                'catalogs',
                'profession',
                'UserID',
                'CompanyID',
                'company_name',
                'company_logo',
                'company_website',
                'TimeCreated',
                'TimeSaved',
                'jobs_citi',
                'employment_type_full_time',
                'employment_type_part_time',
                'employment_type_telecommuting',
                'salary',
                'jobs_coment_salary',
                'jobs_experience',
                'jobs_education',
                'jobs_text',
                'contact',
                'email',
                'phone',
                'job_placement',
                'jobs_work_url',
                'new_reviews_count'
            ));

            $paginated_sql->condition(db_and()
                ->condition('jobs.profession', $profession,  '=')
                ->condition('job_placement',   'hidden',     '!=')
                ->condition('jobs.status',     'moderation', '!=')
            );

            if ((int) $city_id) {
                $paginated_sql->join('jobs_city', 'jc', 'jc.CityID = ' . (int) $city_id . ' AND jc.JobsID = jobs.JobsID');
            }
            
            if ($user_id){
                $paginated_sql->leftJoin('interesting_jobs', 'i_jobs', 'i_jobs.JobsID=jobs.JobsID AND i_jobs.UserID=' . $user_id);
                $paginated_sql->fields('i_jobs', array('InterestingJobsID'));
            }

            $paginated_sql->orderBy('TimeSaved', 'desc');
            $paginated_sql->orderBy('JobsID', 'desc');
            $paginated_sql->range($start, $limit);

            $paginated_result = array(
                'data'       => $paginated_sql->execute()->fetchAll(),
                'count'      => $count,
                'page_count' => $page_count,
                'limit'      => $limit
            );
        }

        return $paginated_result;
    }

    public function getJobsByStatus($user_id = null, $status = null) {
        $job = false;

        if ((int) $user_id && (int) $status) {
            $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
                'JobsID',
                'jobs_title',
                'jobs_url',
                'similar_words_index',
                'indexed',
                'catalogs',
                'profession',
                'UserID',
                'CompanyID',
                'company_name',
                'company_logo',
                'company_website',
                'TimeCreated',
                'TimeSaved',
                'jobs_citi',
                'employment_type_full_time',
                'employment_type_part_time',
                'employment_type_telecommuting',
                'salary',
                'jobs_coment_salary',
                'jobs_experience',
                'jobs_education',
                'jobs_text',
                'contact',
                'email',
                'phone',
                'job_placement',
                'jobs_work_url',
                'new_reviews_count'
            ));

            if ($status == 1) {
                $sql->condition(db_and()
                        ->condition('UserID', $user_id, '=')
                        ->condition(db_or()
                                ->condition('job_placement', 'standard', '=')
                                ->condition('job_placement', 'hot',      '='))
                );
            } elseif ($status == 2) {
                $sql->condition(db_and()
                        ->condition('UserID', $user_id, '=')
                        ->condition('job_placement', 'hidden', '=')
                );
            }

            $job = $sql->execute()->fetchAll();
        }

        return $job;
    }

    public function searchByQuery($query){
        $_query = self::$db
                ->query("SELECT * FROM jobs WHERE MATCH (similar_words_index) AGAINST ('".$query."');", array('target' => 'main'), array())
                ->fetchAll();
        return $_query;
    }

    public function search($params, $page, $user_id, $limit) {

        $sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array('JobsID'));
        
        /* ----------------------- Student condition ----------------------- */

        $student = array(0, 1);

        if (!empty($params['student'])) {
            $student = array(1);
        }

        /* ------------------------ Salary condition ----------------------- */

        if (empty($params['salary'])) {
            $params['salary'][0] = 0;
            $params['salary'][1] = 50000;
        }

        /* ------------------------- City condition ------------------------ */

        $city_cond = false;

        if (!empty($params['city'])) {
            $city_cond = true;
        }

        /* ---------------------- Employment condition --------------------- */

        $full_time = $part_time = $telecommuting = array(2);

        if (!empty($params['employment_type'][1])) {
            $full_time = array(1);
        }

        if (!empty($params['employment_type'][2])) {
            $part_time = array(1);
        }

        if (!empty($params['employment_type'][3])) {
            $telecommuting = array(1);
        }

        if (empty($params['employment_type'])) {
            $full_time = $part_time = $telecommuting = array(0, 1);
        }

        /* ---------------------- Education condition ---------------------- */

        $education = array(4, 3, 2, 1, 0);

        if (!empty($params['education'])) {
            $education = $params['education'];
        }

        /* ---------------------- Experience condition --------------------- */

        $experience = array(0, 1, 2, 3, 4);

        if (!empty($params['experience'])) {
            $experience = $params['experience'];
        }

        /* ----------------------------------------------------------------- */

        $or_cond = db_or()
                ->condition('employment_type_full_time',     $full_time,     'IN')
                ->condition('employment_type_part_time',     $part_time,     'IN')
                ->condition('employment_type_telecommuting', $telecommuting, 'IN');

        if ($city_cond) {
            $sql->condition(db_and()
                            ->condition($or_cond)
                            ->condition('salary', array($params['salary'][0], $params['salary'][1]), 'BETWEEN')
                            ->condition('jobs_education',  $education,   'IN')
                            ->condition('jobs_experience', $experience,  'IN')
                            ->condition('invite_student',  $student,     'IN')
                            ->condition('job_placement',   'hidden',     '!=')
                            ->condition('jobs.status',          'moderation', '!=')
            );

            if (!empty($params['query'])) {
                $sql->where("MATCH (similar_words_index) AGAINST ('".$params['query']."' IN BOOLEAN MODE)");
            }

            $cities = implode(', ', $params['city']);

            $sql->join('jobs_city', 'jc', 'jc.CityID IN (' . $cities . ') AND jc.JobsID = jobs.JobsID');

            if ($user_id){
                $sql->leftJoin('interesting_jobs', 'i_jobs', 'i_jobs.JobsID=jobs.JobsID AND i_jobs.UserID=' . $user_id);
                $sql->fields('i_jobs', array('InterestingJobsID'));
            }
        } else {
            $sql->condition(db_and()
                            ->condition($or_cond)
                            ->condition('salary', array($params['salary'][0], $params['salary'][1]), 'BETWEEN')
                            ->condition('jobs_education',  $education,   'IN')
                            ->condition('jobs_experience', $experience,  'IN')
                            ->condition('invite_student',  $student,     'IN')
                            ->condition('job_placement',   'hidden',     '!=')
                            ->condition('jobs.status',          'moderation', '!=')
            );

            if (!empty($params['query'])) {
                $sql->where("MATCH (similar_words_index) AGAINST ('".$params['query']."' IN BOOLEAN MODE)");
            }

            if ($user_id) {
                $sql->leftJoin('interesting_jobs', 'i_jobs', 'i_jobs.JobsID=jobs.JobsID AND i_jobs.UserID=' . $user_id);
                $sql->fields('i_jobs', array('InterestingJobsID'));
            }
        }

        if (!empty($params['catalog'][0])) {
            $sql->join('jobs_catalog', 'j_catalog', 'j_catalog.CatalogID IN (' . $params['catalog'][0] . ') AND j_catalog.JobsID = jobs.JobsID');
        }

        //$result = $sql->distinct()->execute()->fetchAll();
        $result = $sql->execute()->fetchAll();

        /* ------------------------ Paginate results ----------------------- */

        $count      = count($result);
        $page_count = intval(ceil($count / $limit));
        $start      = ($page - 1) * $limit;

        $paginated_sql = self::$db->select($this->_table_name, 'jobs', array('target' => 'main'))->fields('jobs', array(
            'JobsID',
            'jobs_title',
            'jobs_url',
            'similar_words_index',
            'indexed',
            'catalogs',
            'profession',
            'UserID',
            'CompanyID',
            'company_name',
            'company_logo',
            'company_website',
            'TimeCreated',
            'TimeSaved',
            'jobs_citi',
            'employment_type_full_time',
            'employment_type_part_time',
            'employment_type_telecommuting',
            'salary',
            'jobs_coment_salary',
            'jobs_experience',
            'jobs_education',
            'jobs_text',
            'contact',
            'email',
            'phone',
            'job_placement',
            'jobs_work_url'
        ));


        $paginated_or_cond = db_or()
                ->condition('employment_type_full_time',     $full_time,     'IN')
                ->condition('employment_type_part_time',     $part_time,     'IN')
                ->condition('employment_type_telecommuting', $telecommuting, 'IN');

        if ($city_cond) {
            $paginated_sql->condition(db_and()
                            ->condition($paginated_or_cond)
                            ->condition('salary', array($params['salary'][0], $params['salary'][1]), 'BETWEEN')
                            ->condition('jobs_education',  $education,   'IN')
                            ->condition('jobs_experience', $experience,  'IN')
                            ->condition('invite_student',  $student,     'IN')
                            ->condition('job_placement',   'hidden',     '!=')
                            ->condition('jobs.status',          'moderation', '!=')
            );

            if (!empty($params['query'])) {
                $paginated_sql->where("MATCH (similar_words_index) AGAINST ('".$params['query']."' IN BOOLEAN MODE)");
            }

            $cities = implode(', ', $params['city']);

            $paginated_sql->join('jobs_city', 'jc', 'jc.CityID IN (' . $cities . ') AND jc.JobsID = jobs.JobsID');

            if ($user_id) {
                $paginated_sql->leftJoin('interesting_jobs', 'i_jobs', 'i_jobs.JobsID=jobs.JobsID AND i_jobs.UserID=' . $user_id);
                $paginated_sql->fields('i_jobs', array('InterestingJobsID'));
            }
        } else {
            $paginated_sql->condition(db_and()
                            ->condition($paginated_or_cond)
                            ->condition('salary', array($params['salary'][0], $params['salary'][1]), 'BETWEEN')
                            ->condition('jobs_education',  $education,   'IN')
                            ->condition('jobs_experience', $experience,  'IN')
                            ->condition('invite_student',  $student,     'IN')
                            ->condition('job_placement',   'hidden',     '!=')
                            ->condition('jobs.status',          'moderation', '!=')
            );

            if (!empty($params['query'])) {
                $paginated_sql->where("MATCH (similar_words_index) AGAINST ('".$params['query']."' IN BOOLEAN MODE)");
            }

            if ($user_id) {
                $paginated_sql->leftJoin('interesting_jobs', 'i_jobs', 'i_jobs.JobsID=jobs.JobsID AND i_jobs.UserID=' . $user_id);
                $paginated_sql->fields('i_jobs', array('InterestingJobsID'));
            }
        }

        if (!empty($params['catalog'][0])) {
            $paginated_sql->join('jobs_catalog', 'j_catalog', 'j_catalog.CatalogID IN (' . $params['catalog'][0] . ') AND j_catalog.JobsID = jobs.JobsID');
        }

        $paginated_sql->orderBy('TimeSaved', 'DESC');
        $paginated_sql->orderBy('JobsID', 'DESC');
        $paginated_sql->range($start, $limit);

        $paginated_result = array(
            //'data'       => $paginated_sql->distinct()->execute()->fetchAll(),
            'data'       => $paginated_sql->execute()->fetchAll(),
            'count'      => $count,
            'page_count' => $page_count,
            'limit'      => $limit
        );

        return $paginated_result;
    }

}
