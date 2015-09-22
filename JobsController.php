<?php

require_once(PATH . '/tcpdf/tcpdf.php');

class JobsController extends \Controller {

    public $layout = 'home';
    private $_lang               = false;
    private $_url                = false;
    private $_url_no_lang        = false;
    private $_locale             = false;

    private $_users;
    private $_mjobs             = false;
    private $_mresume           = false;
    private $_mjobs_catalog     = false;
    private $_musers            = false;
    private $_session           = false;
    private $_auth              = false;
    private $_validate          = false;
    private $_mcompany          = false;
    private $_mmenu_item        = false;
    private $_mcatalog          = false;
    private $_mciti             = false;
    private $_mprofessions      = false;
    private $_mprofessions_jobs_city_count = false;
    private $_mjobs_city        = false;
    private $_mjob_resume       = false;
    private $_mjobs_deleted     = false;

    private $_minteresting_jobs = false;

    private $_mdictinary_prefix    = false;
    private $_mdictinary_word      = false;
    private $_msearch_word_group   = false;
    private $_msearch_stop_word    = false;

    private $_mstatistics_user_events = false;

    /*
     * 3 - звичайний роботодавець
     * 2 - роботодавець ROOT
     * 1 - шукач
     * 0 - гість
     */
    private $allow = array(
        0 => array(
            'search', 'index', 'bycity', 'view', 'jobresumewindow', 'jobresumefilewindow', 'bycatalog', 'byregion', 'student', 'settingsmailingjobs', 'welcomecreatejobs'
        ),
        1 => array(
            'search', 'index', 'bycity', 'view', 'jobresumewindow', 'jobresumefilewindow', 'bycatalog', 'byregion', 'student', 'settingsmailingjobs', 'welcomecreatejobs'
        ),
        2 => array(
            '*'
        ),
        3 => array(
            '*'
        ),
        4 => array(
            '*'
        )
    );

    private $conjugations_regions = array(
        9     => 'Киевской',
        2     => 'Луцкой',
        3     => 'Днепропетровской',
        20    => 'Харьковской',
        21    => 'Херсонской',
        15    => 'Одесской',
        13    => 'Львовской',
        7     => 'Запорожской',
        1     => 'Винницкой',
        14    => 'Николаевской',
        22    => 'Хмельницкой',
        8     => 'Ивано-Франковской',
        16    => 'Полтавской',
        5     => 'Житомирской',
        18    => 'Сумской',
        23    => 'Черкасской',
        4     => 'Донецкой',
        17    => 'Ровенской',
        19    => 'Тернопольской',
        25    => 'Черниговской',
        10    => 'Кировоградской',
        24    => 'Черновицкой',
        12    => 'Луганской',
        6     => 'Закарпатской',
        11    => 'Крым'
    );

    private $_TRANSLITERATION = ['А'=>'A', 'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Ґ'=>'G', 'Д'=>'D', 'Е'=>'E', 'Є'=>'E', 'Ж'=>'J', 'З'=>'Z','И'=>'YI', 'І'=>'I',
    'Ї'=>'I', 'Й'=>'Y', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R', 'С'=>'S', 'Т'=>'T', 'У'=>'U',
    'Ф'=>'F', 'Х'=>'H', 'Ц'=>'TS', 'Ч'=>'CH', 'Ш'=>'SH', 'Щ'=>'SCH', 'Ъ'=>'', 'Ы'=>'YI', 'Ь'=>'', 'Э'=>'E', 'Ю'=>'YU', 'Я'=>'YA',
    'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'ґ'=>'g', 'д'=>'d', 'е'=>'e', 'є'=>'e', 'ж'=>'j', 'з'=>'z','и'=>'yi', 'і'=>'i',
    'ї'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u',
    'ф'=>'f', 'х'=>'h', 'ц'=>'ts', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'sch', 'ъ'=>'', 'ы'=>'yi', 'ь'=>'', 'э'=>'e', 'ю'=>'yu', 'я'=>'ya',
    ' '=>'-', ','=>'', '.'=>'', '('=>'', ')'=>'', '['=>'', ']'=>'', '{'=>'','}'=>'', '!'=>'', '@'=>'', '#'=>'', '$'=>'', '%'=>'',
    '^'=>'', '&'=>'', '*'=>'', '+'=>'', '='=>'', ';'=>'', ':'=>'', "'"=>"", '"'=>'', '~'=>'', '`'=>'', '?'=>'', '/'=>'-', '|'=>'-' ];

    private $_STOP_SIMVOL         = [
        ',' => '', '(' => '', ')' => '', '[' => '', ']' => '', '.' => '', '-' => ' ', '/' => ' ', '\\' => ' ',
        '|' => ' ', "'" => '', '"' => '', '{' => '', '}' => '', '?' => '', '!'=> '', '%' => '', '$' => '', '#'=>'',
        '@' => '', '^' => '', ':' => '', ';' => '', '~' => '', '`' => '', '=' => ''
    ];

    public function init() {

        $this->_url = \init::app()->getUrl();

        if($this->_url_no_lang = explode('/', $this->_url) and is_array($this->_url_no_lang) and count($this->_url_no_lang) > 0) {
            if(\init::app() -> getCLanguage() -> issetLanguage( (string)$this->_url_no_lang[0] )){
                unset($this->_url_no_lang[0]);
            }
            $this->_url_no_lang =  implode('/', $this->_url_no_lang);
        }

        $this->_lang    = \init::app()->getLanguage();

        $this->_users = \init::app()->getModels('auth/users');

        if (empty($this->_musers)) {
            $this->_musers = \init::app()->getModels('users/musers');
        }

        if (empty($this->_mjobs)) {
            $this->_mjobs = \init::app()->getModels('jobs/mjobs');
        }

        if (empty($this->_mjobs_catalog)) {
            $this->_mjobs_catalog = \init::app()->getModels('jobs_catalog/mjobs_catalog');
        }

        if (empty($this->_mcompany)) {
            $this->_mcompany = \init::app()->getModels('company/mcompany');
        }

        if (empty($this->_mmenu_item)) {
            $this->_mmenu_item = \init::app()->getModels('menu_item/mmenu_item');
        }

        if (empty($this->_mcatalog)) {
            $this->_mcatalog = \init::app()->getModels('catalog/mcatalog');
        }

        if (empty($this->_mciti)) {
            $this->_mciti = \init::app()->getModels('citi/mciti');
        }

        if (empty($this->_mjobs_city)) {
            $this->_mjobs_city = \init::app()->getModels('jobs_city/mjobs_city');
        }

        if (empty($this->_mdictinary_prefix)) {
            $this->_mdictinary_prefix = \init::app()->getModels('dictinary_prefix/mdictinary_prefix');
        }

        if (empty($this->_mjobs_deleted)) {
            $this->_mjobs_deleted = \init::app()->getModels('jobs_deleted/mjobs_deleted');
        }

        if (empty($this->_mprofessions)) {
            $this->_mprofessions = \init::app()->getModels('professions/mprofessions');
        }

        if (empty($this->_mstatistics_user_events)) {
            $this->_mstatistics_user_events = \init::app()->getModels('statistics_user_events/mstatistics_user_events');
        }

        $this->_session = \init::app()->getSession()->all_userdata();
        $this->_auth    = $this->_users->getValidate()->getSession();

        $RoleID = (isset($this->_auth['RoleID'])) ? $this->_auth['RoleID'] : 0;

        $check = $this->checkRights($this->allow, $RoleID);

        if (!$check) {
            $this->redirect('/no-function');
        }

        $this->_locale = $this->load_json_utf8(PATH_LOCALE.DIRECTORY_SEPARATOR.$this->_lang.'.json');
    }

    private function load_json_utf8($filename)
    {
	$bom = chr(0xEF).chr(0xBB).chr(0xBF);
	$content_str = file_get_contents($filename);
	if(substr_compare($bom, $content_str, 0, strlen($bom)) == 0)
		$content_str = substr($content_str, strlen($bom));
	return json_decode($content_str, true);
    }

    public function jobCityName() {
        $jobs   = $this->_mjobs->getJobs();
        $cities = $this->_mciti->getCiti();

        $ua_ru = array();

        foreach ($cities as $city) {
            $ua_ru[$city->name_ua] = $city->name;
        }

        foreach ($jobs as $job) {
            try {
                if (isset($ua_ru[$job->jobs_citi])) {
                    $this->_mjobs->save(true, array(
                        'JobsID'    => $job->JobsID,
                        'jobs_citi' => $ua_ru[$job->jobs_citi]
                    ));
                }
            } catch (Exception $ex) {

            }
        }
    }

    public function actionIndex() {

        $this->layout('jobs');

        $city   = $this->_mciti->getCiti();
        $params = \init::app()->getRequest()->getParam('url');
        $params = explode('/', $params);

        $home_url = HOST.'/';
        if($this->_lang === 'ua')
            $home_url.='ua/';

        $this->breadcrumbs = [
            0 => [
                'title' => $this->_lang === 'ua' ? 'Головна' : 'Главная',
                'url' => $home_url
            ],
            1 => [
                'title' => $this->_lang === 'ua' ? 'вакансії': 'вакансии',
                'url' => $home_url.'вакансии'
            ]
        ];

        if (!empty($params[0]) && empty($params[1]) && empty($params[2])) {
            $this->actionIndexVacancies($city);
        } elseif (!empty($params[0]) && !empty($params[1]) && !empty($params[2])) {
            // Get catalog from params and check url
            $catalogs         = $this->_mcatalog->getCatalog();
            $catalog_id_url   = array();
            $catalog_url_name = array();
            $limit            = 10;

            foreach ($catalogs as $catalog) {
                // Key => catalog id; value => catalog url
                $catalog_id_url[$catalog->CatalogID] = $catalog->url;

                // Key => catalog url; value => catalog name
                $catalog_url_name[$catalog->url] = $this->_lang === 'ua' ? $catalog->name_ua : $catalog->name;
            }

            if (!in_array($params[1], $catalog_id_url)) {
                throw new CHttpException(404, \init::t('init', 'Page not found'));
            } else {
                // Get city from params and check url
                $city_id_url = array();

                foreach ($city as $value) {
                    $city_id_url[$value->CitiID] = $value->name;
                }

                // Example : Желтые Воды | Желтые_Воды
                if (strpos($params[2], '_') !== false) {
                    $params[2] = str_replace('_', ' ', $params[2]);
                }

                $this->breadcrumbs[] = [
                    'title' => $catalog_url_name[$params[1]],
                    'url' => $home_url."вакансии/$params[1]/Украина"
                ];

                $user_id = false;
                if($this->_auth) {
                    $user_id = $this->_auth['id'];
                }

                if ($params[2] === 'Украина') {
                    $this->actionIndexVacanciesUkraine($params, $catalog_id_url, $catalog_url_name, $limit, $user_id);
                } elseif (!in_array($params[2], $city_id_url)) {
                    throw new CHttpException(404, \init::t('init', 'Page not found'));
                } else {
                    // Here we have our existing catalog and city
                    // in params array
                    $catalog   = $params[1];
                    $city_name = $params[2];

                    $city_data = $this->_mciti->getCityDataByCityName($city_name);

                    // Set meta tags
                    if (!isset ($params[3])) {
                        $this->title       = 'Работа ' . $catalog_url_name[$catalog] . ' в ' . $city_data['in_name'] . ', свежие вакансии в Украине | vrabote.ua';
                        $this->description = 'Актуальные вакансии ' . $catalog_url_name[$catalog] . ' в ' . $city_data['in_name'] . ' от проверенных работодателей.';
                        $this->keywords    = 'Актуальные, свежие, вакансии, ' . $catalog_url_name[$catalog] . ', ' . $city_data['in_name'] . ', от, проверенных, работодателей, vrabote.ua';
                    } elseif ((int) $params[3]) {
                        $this->title       = 'Работа ' . $catalog_url_name[$catalog] . ' в ' . $city_data['in_name'] . ', свежие вакансии в Украине - страница ' . (int) $params[3] . ' | vrabote.ua';
                    }

                    $this->breadcrumbs[] = [
                        'title' => $this->_lang === 'ua' ? $city_data['name_ua'] : $city_data['name'],
                        'url'   => $home_url . "вакансии/$catalog/$city_name"
                    ];

                    // Each catalog url has unique value so we can safely
                    // search key value by url
                    $catalog_id  = array_search($catalog, $catalog_id_url);
                    $city_id     = $this->_mciti->getCityIdByName($city_name);
                    $jobs_cities = $this->_mjobs_city->getJobsIdsByCityID($city_id['CitiID']);
                    $jobs_ids    = array();

                    foreach ($jobs_cities as $job_city) {
                        $jobs_ids[] = $job_city->JobsID;
                    }

                    if (isset($params[3]) && (int) $params[3]) {
                        $page  = (int) $params[3];
                        $query = $params;

                        unset($query[3]);
                    } elseif (!isset ($params[3])) {
                        $page  = 1;
                        $query = $params;
                    }

                    $companies = $this->_mcompany->getCompanyID($jobs_ids);

                    $companies_list = array();

                    if (!empty($companies)) {
                        foreach ($companies as $company) {
                            $companies_list[$company->CompanyID] = $company;
                        }
                    }

                    $jobs     = $this->_mjobs->getJobsForLandingPage($page, $limit, $jobs_ids, $catalog_id, $user_id);
                    $page_nav = $this->generatePageNav($page, $limit, $jobs['page_count']);

                    $this->paging_links = $this->setPagingLinks($page_nav['count'], $params);
                    $this->canonical    = '<link rel="canonical" href="http://vrabote.ua/вакансии/ит/' . $city_name . '" />';

                    if (empty($jobs['data']) && $page_nav['page'] > 1) {
                        throw new CHttpException(404, \init::t('init', 'Page not found'));
                    } else {
                        $this->render('index_catalog_city', array(
                            'search_results' => $jobs['data'],
                            'count'          => $jobs['count'],
                            'query'          => implode('/', $query),
                            'start'          => $page_nav['start'],
                            'end'            => $page_nav['end'],
                            'page'           => $page_nav['page'],
                            'page_count'     => $page_nav['count'],
                            'catalog'        => $catalog_url_name[$catalog],
                            'city'           => $city_name,
                            'city_id'        => $city_data['CitiID'],
                            'conjugation'    => $city_data['in_name'],
                            '_auth'          => $this->_auth,
                            '_locale'        => $this->_locale,
                            'companies'      => $companies_list
                        ));
                    }
                }
            }
        } else {
            throw new CHttpException(404, \init::t('init', 'Page not found'));
        }
    }

    public function actionIndexVacancies($city = array()) {

        if (empty($this->_mprofessions)) {
            $this->_mprofessions = \init::app()->getModels('professions/mprofessions');
        }

        $professions = $this->_mprofessions->getProfessions();

        // Catalogs block
        $catalogs                = $this->_mcatalog->getCatalog();
        $catalogs_count          = count($catalogs);
        $catalogs_list_one_count = (int)($catalogs_count / 2);

        // Get all jobs ids (param : Ukraine)
        $jobs_cities = $this->_mjobs_city->getJobsIdsByCityID(null);
        $jobs_ids    = array();

        foreach ($jobs_cities as $job_city) {
            $jobs_ids[] = $job_city->JobsID;
        }

        // Jobs count in catalogs block
        $jobs_catalogs = $this->_mjobs_catalog->getJobsCatalogsByJobsIds($jobs_ids);
        $jobs_counter  = array();

        foreach ($jobs_catalogs as $job_catalog) {
            $jobs_counter[$job_catalog->CatalogID][] = $job_catalog->JobsID;
        }

        // Set meta tags
        $this->title       = 'Вакансии в Украине. Быстро найти работу на vrabote.ua';
        $this->description = 'Всегда самые свежие вакансии от проверенных работодателей. Поиск вакансий по разделам и городам.';
        $this->keywords    = 'Найти работу, поиск работы, вакансии, работа, профессиям, рубрикам, компаниям, регионам, Украина, Киев, vrabote.ua';

        $this->render('index', array(
            'city'                   => $city,
            'professions'            => $professions,
            'professions_count'      => count($professions),
            'city_count'             => count($city),
            'catalog'                => $catalogs,
            'catalog_count'          => $catalogs_count,
            'catalog_list_one_count' => $catalogs_list_one_count,
            'jobs_counter'           => $jobs_counter,
            '_locale'                => $this->_locale
        ));
    }

    public function actionIndexVacanciesUkraine($params = array(), $catalog_id_url = array(), $catalog_url_name = array(), $limit = 10, $user_id = false) {
        $catalog = $params[1];

        // Each catalog url has unique value so we can safely
        // search key value by url
        $catalog_id  = array_search($catalog, $catalog_id_url);

        if (isset($params[3]) && (int) $params[3]) {
            $page  = (int) $params[3];
            $query = $params;

            unset($query[3]);
        } elseif (!isset ($params[3])) {
            $page  = 1;
            $query = $params;
        }

        // Get all jobs ids (param : Ukraine)
        $jobs_cities = $this->_mjobs_city->getJobsIdsByCityID(null);
        $jobs_ids    = array();

        foreach ($jobs_cities as $job_city) {
            $jobs_ids[] = $job_city->JobsID;
        }

        $jobs = $this->_mjobs->getJobsForLandingPage($page, $limit, $jobs_ids, $catalog_id, $user_id);
        $nav  = $this->generatePageNav($page, $limit, $jobs['page_count']);

        if (empty($jobs['data']) && $nav['page'] > 1) {
            throw new CHttpException(404, \init::t('init', 'Page not found'));
        } else {
            // Set meta tags
            if (!isset ($params[3])) {
                $this->title       = 'Работа ' . $catalog_url_name[$catalog] . ' в Украине, свежие вакансии 2015 | vrabote.ua';
                $this->description = 'Актуальные вакансии ' . $catalog_url_name[$catalog] . ' в Украине от проверенных работодателей.';
                $this->keywords    = 'Актуальные, свежие, вакансии, ' . $catalog_url_name[$catalog] . ', Украине, от, проверенных, работодателей, vrabote.ua';
            } elseif ((int) $params[3]) {
                $this->title       = 'Работа ' . $catalog_url_name[$catalog] . ' в Украине, свежие вакансии 2015 - страница ' . (int) $params[3] . ' | vrabote.ua';
            }

            $this->paging_links = $this->setPagingLinks($nav['count'], $params);
            $this->canonical    = '<link rel="canonical" href="http://vrabote.ua/вакансии/ит/Украина" />';

            $companies = $this->_mcompany->getCompanyID($jobs_ids);

            $companies_list = array();

            if (!empty($companies)) {
                foreach ($companies as $company) {
                    $companies_list[$company->CompanyID] = $company;
                }
            }

            $this->render('index_catalog_city', array(
                'search_results' => $jobs['data'],
                'count'          => $jobs['count'],
                'query'          => implode('/', $query),
                'start'          => $nav['start'],
                'end'            => $nav['end'],
                'page'           => $nav['page'],
                'page_count'     => $nav['count'],
                'catalog'        => $catalog_url_name[$catalog],
                'city'           => 'Украина',
                'conjugation'    => 'Украине',
                '_auth'          => $this->_auth,
                '_locale'        => $this->_locale,
                'companies'      => $companies_list
            ));
        }
    }

    public function actionByCity() {
        $this->layout('main_city');

        $city_name = \init::app()->getRequest()->getParam('url');
        $city_name = CDefender::verifStr($city_name);

        if (!empty($city_name)) {

            if (strpos($city_name, '_') !== false) {
                $city_name = str_replace('_', ' ', $city_name);
            }
            $city = $this->_mciti->getCityDataByCityName($city_name);

            $home_url = HOST.'/';
            if($this->_lang === 'ua')
                $home_url.='ua/';

            $this->breadcrumbs = [
                0 => [
                    'title' => $this->_lang === 'ua' ? 'Головна' : 'Главная',
                    'url' => $home_url
                ],
                1 => [
                    'title' => $this->_lang === 'ua' ? 'вакансії': 'вакансии',
                    'url' => $home_url.'вакансии'
                ],
                2 => [
                    'title' => $this->_lang === 'ua' ? $city['name_ua']: $city['name'],
                    'url' => $home_url.$city_name
                ]
            ];


            $jobs_cities = $this->_mjobs_city->getJobsIdsByCityID($city['CitiID']);
            $jobs_ids    = array();

            foreach ($jobs_cities as $job_city) {
                $jobs_ids[] = $job_city->JobsID;
            }

            // Count all jobs in current city
            $jobs_count = count($jobs_ids);

            // Set meta tags
            $this->title       = 'Работа в ' . $city['in_name'] . ', свежие вакансии в ' . $city['in_name'] . ' и ' . $this->conjugations_regions[$city['RegionID']] . ' области от проверенных компаний – vrabote.ua';
            $this->description = 'Поиск работы в ' . $city['in_name'] . ' свежие вакансии от проверенных работодателей! Быстрый поиск работы на сайте vrabote.ua';
            $this->keywords    = 'Работа, ' . $city['in_name'] . ', Ищу, работу, вакансии, ' . $city['name'] . ', Поиск, работы, vrabote.ua';

            $this->render('view_by_city', array(
                'jobs_count'             => $jobs_count,
                'city_name'              => $city_name,
                'city_id'                => $city['CitiID'],
                '_lang'                  => $this->_lang,
                '_locale'                => $this->_locale
            ));
        } else {
            throw new CHttpException(404, \init::t('init', 'Page not found'));
        }
    }

    public function setPagingLinks($count = null, $params = array()) {
        $result = '';

        if (!empty($count) && !empty($params)) {
            $page = (isset($params[3])) ? (int) $params[3] : 1;

            if ($page == 1) {
                $result  = '<link rel="next" href="http://vrabote.ua/' . $params[0] . '/' . $params[1] . '/' . $params[2] . '/2">';
            } elseif ($page == 2) {
                $result  = '<link rel="prev" href="http://vrabote.ua/' . $params[0] . '/' . $params[1] . '/' . $params[2] . '">';
                $result .= '<link rel="next" href="http://vrabote.ua/' . $params[0] . '/' . $params[1] . '/' . $params[2] . '/3">';
            } else {
                $prev = $page - 1;
                $next = $page + 1;

                $result  = '<link rel="prev" href="http://vrabote.ua/' . $params[0] . '/' . $params[1] . '/' . $params[2] . '/' . $prev . '">';

                if ($next <= $count) {
                    $result .= '<link rel="next" href="http://vrabote.ua/' . $params[0] . '/' . $params[1] . '/' . $params[2] . '/' . $next . '">';
                }
            }
        }

        return $result;
    }

    public function actionGetActiveJobs() {
        if ($this->_auth) {
            $sub_user_id = \init::app()->getRequest()->getParam('user_id');
            $active_jobs = $job_id_list = $reviews = array();

            if ($this->_auth['RoleID'] == 2 && !empty($sub_user_id)) {
                $user     = $this->_musers->getUserID($this->_auth['id']);
                $sub_user = $this->_musers->getUserID($sub_user_id);

                if ($user['CompanyID'] == $sub_user['CompanyID']) {
                    $active_jobs = $this->_mjobs->getJobsByStatus($sub_user_id, 1);
                }
            } else {
                $active_jobs = $this->_mjobs->getJobsByStatus($this->_auth['id'], 1);
            }

            foreach ($active_jobs as $job) {
                $job_id_list[] = $job->JobsID;
            }

            if ($this->_auth['RoleID'] == 2) {
                if (empty($this->_mjob_resume)) {
                    $this->_mjob_resume = \init::app()->getModels('job_resume/mjob_resume');
                }

                $raw_reviews = $this->_mjob_resume->getAllReviews($job_id_list);

                if (!empty($raw_reviews)) {
                    foreach ($raw_reviews as $review) {
                        $reviews[$review->JobID][] = $review;
                    }
                }
            }

            $this->render('active_jobs', array(
                'active_jobs' => $active_jobs,
                'reviews'     => $reviews,
                '_locale'     => $this->_locale
            ));
        }
    }

    public function actionGetInactiveJobs() {
        if ($this->_auth) {
            $sub_user_id   = \init::app()->getRequest()->getParam('user_id');
            $inactive_jobs = $job_id_list = $reviews = array();

            if ($this->_auth['RoleID'] == 2 && !empty($sub_user_id)) {
                $user     = $this->_musers->getUserID($this->_auth['id']);
                $sub_user = $this->_musers->getUserID($sub_user_id);

                if ($user['CompanyID'] == $sub_user['CompanyID']) {
                    $inactive_jobs = $this->_mjobs->getJobsByStatus($sub_user_id, 2);
                }
            } else {
                $inactive_jobs = $this->_mjobs->getJobsByStatus($this->_auth['id'], 2);
            }

            foreach ($inactive_jobs as $job) {
                $job_id_list[] = $job->JobsID;
            }

            if ($this->_auth['RoleID'] == 2) {
                if (empty($this->_mjob_resume)) {
                    $this->_mjob_resume = \init::app()->getModels('job_resume/mjob_resume');
                }

                $raw_reviews = $this->_mjob_resume->getAllReviews($job_id_list);
                $reviews     = array();

                if (!empty($raw_reviews)) {
                    foreach ($raw_reviews as $review) {
                        $reviews[$review->JobID][] = $review;
                    }
                }
            }

            $this->render('inactive_jobs', array(
                'inactive_jobs' => $inactive_jobs,
                'reviews'       => $reviews,
                '_locale'       => $this->_locale
            ));
        }
    }

    public function actionGetArchiveJobs() {
        if ($this->_auth) {
            $sub_user_id  = \init::app()->getRequest()->getParam('user_id');
            $archive_jobs = $job_id_list = $reviews = array();

            if ($this->_auth['RoleID'] == 2 && !empty($sub_user_id)) {
                $user     = $this->_musers->getUserID($this->_auth['id']);
                $sub_user = $this->_musers->getUserID($sub_user_id);

                if ($user['CompanyID'] == $sub_user['CompanyID']) {
                    $archive_jobs = $this->_mjobs_deleted->getJobsByUserID($sub_user_id);
                }
            } else {
                $archive_jobs = $this->_mjobs_deleted->getJobsByUserID($this->_auth['id']);
            }

            foreach ($archive_jobs as $job) {
                $job_id_list[] = $job->JobsID;
            }

            if ($this->_auth['RoleID'] == 2) {
                if (empty($this->_mjob_resume)) {
                    $this->_mjob_resume = \init::app()->getModels('job_resume/mjob_resume');
                }

                $raw_reviews = $this->_mjob_resume->getAllReviews($job_id_list);
                $reviews     = array();

                if (!empty($raw_reviews)) {
                    foreach ($raw_reviews as $review) {
                        $reviews[$review->JobID][] = $review;
                    }
                }
            }

            $this->render('archive_jobs', array(
                'archive_jobs' => $archive_jobs,
                'reviews'      => $reviews,
                '_locale'      => $this->_locale
            ));
        }
    }

    public function actionChangeJobType() {
        $data     = \init::app()->getRequest()->getParams();
        $job_id   = $data['id'];
        $job_type = $data['type'];

        $job_status_info = $this->_mjobs->getJobsStatusInfo($job_id);

        if ($job_type === 'hidden' AND  !empty($job_status_info['profession'])) {
            $this->changeProfessionJobsCount($job_status_info['profession'], false);
            $this->decrementProfessionJobsCityCount($job_status_info['profession'], $job_status_info['jobs_citi']);
        }

        if ($job_status_info['job_placement'] === 'hidden' AND  $job_type !== 'hidden' AND  !empty($job_status_info['profession'])) {
            $this->changeProfessionJobsCount($job_status_info['profession']);
            $this->incrementProfessionJobsCityCount($job_status_info['profession'], $job_status_info['jobs_citi']);
        }

        if ($job_id) {
            $this->_mjobs->save(true, array(
                'JobsID'        => $job_id,
                'TimeSaved'     => date('Y-m-d H:m:s'),
                'job_placement' => $job_type
            ));

            $this->addUserEventInSatistics('change_job_type');
        }
    }

    public function actionUpdateJobDate() {
        $job_id = \init::app()->getRequest()->getParam('id');

        if ($job_id) {
            $date   = date('Y-m-d H:i:s');

            $result = array();

            $this->_mjobs->save(true, array('JobsID' => $job_id, 'TimeSaved' => $date));
            $this->addUserEventInSatistics('update_job_date');

            $result['date_start'] = date('d.m.y', strtotime($date));
            $result['date_stop']  = date('d.m.y', strtotime($date . ' + 1 month'));

            echo json_encode($result);
        }
    }

    public function actionJobSetHot() {
        $this->layout('home_employer');

        if ($this->_auth) {
            $user    = $this->_musers->getUserID($this->_auth['id']);
            $company = $this->_mcompany->getCompanyID($user['CompanyID']);

            $purchases         = \init::app()->getModels('purchases/mpurchases');
            $company_purchases = $purchases->getCompanyPurchases($user['CompanyID'], 'hot_vacancy');

            $data   = \init::app()->getRequest()->getParam('jobs');
            $job_id = \init::app()->getRequest()->getParam('id');

            if (empty($data)) {
                if (!empty($company_purchases) && $company_purchases['amount'] > 0 && $company_purchases['status']) {
                    /*
                     * If user already has hot_vacancy record in purchases
                     * table and amount of available hot vacancies is > 0
                     * and payment was made
                     */
                    $this->actionActivateJobAsHot($purchases, $company_purchases, $job_id);
                } elseif (!empty($company_purchases) && $company_purchases['amount'] > 0) {
                    /*
                     * If user already has hot_vacancy record in purchases
                     * table and amount of available hot vacancies is > 0
                     * and payment wasn't made
                     */
                    $this->render('job_order_thanks', array(
                        'user'    => $user,
                        'message' => 'Ваша заявка в ожидании оплаты'
                    ));
                } else {
                    /*
                     * Create record in purchases table for current user if
                     * record doesn't exists
                     */
                    if (empty($company_purchases)) {
                        try {
                            $purchases->save(true, array(
                                'object'     => 'hot_vacancy',
                                'user_id'    => $user['userID'],
                                'company_id' => $user['CompanyID'],
                                'email'      => $user['email']
                            ));
                        } catch (Exception $ex) {

                        }
                    }

                    $this->render('job_order_hot', array(
                        'user'    => $user,
                        'company' => $company
                    ));
                }
            } else {
                /*
                 * If form data is not empty and user has hot_vacancy record
                 * in purchases table
                 */
                $this->actionHotJobsOrderThanks($data, $user, $company, $purchases);
            }
        }
    }

    private function actionActivateJobAsHot($purchases = null, $company_purchases = array(), $job_id = null) {
        if ((int) $job_id && $this->_auth['CompanyID'] == $company_purchases['company_id']) {
            try {
                if (($company_purchases['amount'] - 1) == 0) {
                    $cost   = 0;
                    $status = 0;
                } else {
                    $cost   = $company_purchases['cost'];
                    $status = $company_purchases['status'];
                }

                $count = $company_purchases['amount'] - 1;

                $purchases->save(true, array(
                    'id'         => $company_purchases['id'],
                    'amount'     => $count,
                    'cost'       => $cost,
                    'total_cost' => $cost * $count,
                    'status'     => $status
                ));

                $this->_mjobs->save(true, array(
                    'JobsID'        => (int) $job_id,
                    'job_placement' => 'hot'
                ));
            } catch (Exception $ex) {
                // echo $ex->getMessage();
            }
        }

        $this->redirect('/employer/notepad/jobs');
    }

    private function actionHotJobsOrderThanks($data, $user, $company, $purchases) {
        $this->actionHotJobsEmail($data, $user, $company);

        $data['cost'] = 750;

        $hot_vacancy = $purchases->getCompanyPurchases($user['CompanyID'], 'hot_vacancy');

        try {
            $purchases->save(true, array(
                'id'         => $hot_vacancy['id'],
                'cost'       => $data['cost'],
                'amount'     => $data['count'],
                'total_cost' => $data['cost'] * $data['count']
            ));
        } catch (Exception $ex) {
            // echo $ex->getMessage();
        }

        $this->render('job_order_thanks', array(
            'data'    => $data,
            'user'    => $user,
            'company' => $company
        ));
    }

    public function actionHtmlToPDF() {
        $count   = \init::app()->getRequest()->getParam('count');
        $cost    = \init::app()->getRequest()->getParam('cost');
        $company = \init::app()->getRequest()->getParam('company');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        // $pdf->SetCreator(PDF_CREATOR);
        // $pdf->SetAuthor('Nicola Asuni');
        // $pdf->SetTitle('TCPDF Example 006');
        // $pdf->SetSubject('TCPDF Tutorial');
        // $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

        // set header and footer fonts
        // $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        // $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        // $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');

            $pdf->setLanguageArray($l);
        }

        // set font
        $pdf->SetFont('dejavusans', '', 10);

        // add a page
        $pdf->AddPage();

        // create some HTML content
        $html = ''
                . '<html>'
                . '<body>'
                . '<h1>' . $company . '</h1>'
                . '<div>шт. ' . $count . '</div>'
                . '<div>стоимость. ' . $count * $cost . '</div>'
                . '</body>'
                . '</html>';

        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // close and output PDF document
        $pdf->Output('test', 'I');
    }

    private function actionHotJobsEmail($data, $user, $company) {
        if (!empty($user)) {
            $subject  = '';
            $message  = '';

            $email = 'VigoAlexandr@gmail.com';

            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            $headers .= 'To: vRabote <' . $email . '>' . "\r\n";
            $headers .= 'From: vRabote <vRabote@gmail.com>' . "\r\n";

//            if (mail($email, $subject, $message, $headers)) {
//                return true;
//            }

            return false;
        }
    }

    public function actionDeleteJob() {
        $job_id = \init::app()->getRequest()->getParam('id');

        if ((int) $job_id) {
            $job = $this->_mjobs->getJobsID($job_id);

            if ($this->_mjobs_deleted->copyIntoAnotherTable($job)) {
                $this->_mjobs->delete(array('JobsID' => $job_id));
                $this->decrementCompanyJobsCount($job['CompanyID']);

                if(!empty($job['profession'])){
                   $this->changeProfessionJobsCount($job['profession'], false);
                   $this->decrementProfessionJobsCityCount($job['profession'], $job['jobs_citi']);
                }

                $this->addUserEventInSatistics('delete_job');
            }

        }
    }

    public function actionDeleteJobs() {
        $data = \init::app()->getRequest()->getParam('ids');

        $result = false;

        if (!empty($data) && is_array($data) && count($data) > 0) {
            $ids    = array_filter($data);
            $result = true;
        }

        echo json_encode($result);
    }

    public function actionRestoreJob() {
        $job_id = \init::app()->getRequest()->getParam('id');

        if ((int) $job_id) {
            $job = $this->_mjobs_deleted->getJobByID($job_id);

            if ($this->_mjobs->copyIntoAnotherTable($job)) {
                $this->_mjobs_deleted->delete(array('JobsID' => $job_id));
                $this->incrementCompanyJobsCount($job['CompanyID']);


                if(!empty($job['profession'])){
                   $this->changeProfessionJobsCount($job['profession']);
                   $this->incrementProfessionJobsCityCount($job['profession'], $job['jobs_citi']);
                }

                $this->addUserEventInSatistics('restore_job');
            }
        }
    }

    public function actionSearchJobs() {
        echo 'actionSearchJobs->JobsController';
    }

    public function actionEdit() {
        $this->layout('home_employer');

        if (!$this->_auth) {
            $this->redirect('/employer/register');
        } elseif ($this->_auth['user_type'] !== '2') {
            $this->redirect('/no-function');
        } else {

            $_data  = json_decode(file_get_contents('php://input'), true);

            if (is_array($_data) and count($_data) > 0 ) {

                $result['error'] = false;

                $verif_result = $this->verificationJobs($_data);
                $_verif_jobs =  false;

                if(!$verif_result['error']){
                    $_verif_jobs = $verif_result['data'];
                }

                if(!$verif_result['error']){

                    $user = $this->_musers->getUserID($this->_session['id']);

                    $_verif_jobs['info']['TimeSaved'] = date('Y-m-d H:i:s');
                    $new_jobs = $this -> initJobs($_verif_jobs, $user);
                    $result['JobsID'] = $this->_mjobs->save(true, $new_jobs);

                    $this->updateCatalogsJobsCount($_verif_jobs['JobsID'], $_verif_jobs['catalogs']);
                    $this->updateCitiesForJobs($_verif_jobs['JobsID'], $_verif_jobs['cities']);
                    $this->updateCatalogsForJobs($_verif_jobs['JobsID'], $_verif_jobs['catalogs']);

                    $this->addUserEventInSatistics('edit_job');

                } else {
                    $result['error'] = true;
                    $result['error_array'] = $verif_result['error_array'];
                }

                echo json_encode($result);

            } else {
                $job_id        = \init::app()->getRequest()->getParam('id');
                $catalog       = $this->_mcatalog->getCatalog();
                $catalog_count = count($catalog);
                $catalog_list_one_count = (int)($catalog_count / 2);

                if ($catalog_count % 2) {
                    $catalog_list_one_count++;
                }

                $jobs_catalog = $this->_mjobs_catalog->getCatalogsByJobID($job_id);
                $jobs_catalog_result = new stdClass();
                foreach ($jobs_catalog as $jobs_catalog_item){
                    $jobs_catalog_result->{$jobs_catalog_item->CatalogID} = $jobs_catalog_item->CatalogID;
                }

                $jobs_city = $this->_mjobs_city->getCitiesByJobID($job_id);
                $jobs_city_result = new stdClass();
                foreach ($jobs_city as $key => $jobs_city_item){
                    $jobs_city_result->{$key} = $jobs_city_item->CityID;
                }

                $this->render('edit', array(
                    'jobs'                  => $this->_mjobs->getJobsID($job_id),
                    'catalog'              => $catalog,
                    'catalog_count'        => $catalog_count,
                    'catalog_list_one_count' => $catalog_list_one_count,
                    'city'               => $this->_mciti->getCitiByAmount(2),
                    'cities_id'            => $this->_mjobs_city->getCitiesByJobID($job_id),
                    'jobs_city'        => json_encode($jobs_city_result),
                    'jobs_catalog'     => $jobs_catalog_result,
                    '_locale'                => $this->_locale
                ));
            }
        }
    }

    function actionGetJobCatalogs() {
        $job_id             = json_decode(\init::app()->getRequest()->getParam('job_id'));
        $job_catalogs_array = $this->_mjobs_catalog->getCatalogsByJobID($job_id);

        $job_catalogs = array();

        foreach ($job_catalogs_array as $item) {
            $job_catalogs[] = $item->CatalogID;
        }

        echo json_encode($job_catalogs);
    }

    public function actionView() {
        $this->layout('main_light_jobs');

        if (\init::app()->getRequest()->getParam('date')) {
            // We don't want to index pages with get param date
            $this->block = true;
        }

        $url       = \init::app()->getRequest()->getParam('url');
        $parse_url = explode('-', $url, 2);
        $job_id    = $parse_url[0];

        if ($job_id) {
            $job = $this->_mjobs->getJobsID($job_id);

            if($this->_auth){

                if (empty($this->_minteresting_jobs)) {
                    $this->_minteresting_jobs = \init::app()->getModels('interesting_jobs/minteresting_jobs');
                }

                $interesting_job = $this->_minteresting_jobs->checkInterestedJobByUser($job_id, $this->_auth['id']);
                $job['InterestingJobsID'] = $interesting_job ? $interesting_job['InterestingJobsID'] : 0;
            }

            if (!$job) {
                $deleted = $this->_mjobs_deleted->getJobByID($job_id);

                if ($deleted) {
                    $this->render('view_deleted', array(
                        '_auth' => $this->_auth,
                        '_locale' => $this->_locale
                    ));
                }

                return;
            }

            if ($parse_url[1] !== $job['jobs_url']) {
                $this->redirect('/jobs/jobs-not-found');

                throw new CHttpException(404, \init::t('init', 'Page not found'));
            }

            $company      = $this->_mcompany->getCompanyID($job['CompanyID']);
            $user         = $this->_musers->getUserID($job['UserID']);
            $resume_count = 0;

            if ($this->_auth) {
                $user_view    = $this->_musers->getUserID($this->_auth['id']);
                $resume_count = $user_view['resume_count'];
            }

            $job_cities = $this->_mjobs_city->getCitiesByJobID($job_id);
            
            // Get similar jobs
            $params = array(
                'job' => array(
                    'text_query' => $job['jobs_title'],
                    'query'      => $this->getSearchQueryByText($job['jobs_title']),
                    'city'       => [
                        $job_cities[0]->CityID => $job_cities[0]->CityID
                    ]
                )
            );

            $similar_query = http_build_query($params);
            $similar_jobs  = $this->_mjobs->search($params['job'], 1, NULL, 7);

            foreach ($similar_jobs['data'] as $key => $value) {
                if ($similar_jobs['data'][$key]->JobsID == $job['JobsID']) {
                    unset($similar_jobs['data'][$key]);

                    break;
                }
            }

            $this->params['suitable_jobs_url'] = '/jobs/search?'.$similar_query;

            // Set meta tags
            $job_city  = explode(',', $job['jobs_citi']);
            $city_name = $job_city[0];
            $city_data = $this->_mciti->getCityDataByCityName($city_name);

            $this->title       = $job['company_name'] . ': работа ' . $job['jobs_title'] . ' в ' . $city_data['in_name'] . '. Вакансии ' . $job['jobs_title'] . ' - vrabote.ua';
            $this->description = 'Работа ' . $job['jobs_title'] . ' в ' . $city_data['in_name'] . ' в компании ' . $job['company_name'] . ', вакансия ' . $job['jobs_title'] . ' в ' . $job['company_name'] . '. Поиск работы в ' . $job['company_name'] . ' в ' . $city_data['in_name'] . ' через портал vrabote.ua';
            $this->keywords    = $job['company_name'] . ', работа, ' . $job['jobs_title'] . ', ' . $city_data['in_name'] . ', вакансии, vrabote.ua';

            $this->auth = $this->_auth;

            if($this->_auth){
                $this->params['InterestingJobsID'] = $job['InterestingJobsID'];
                $this->params['JobsID'] = $job['JobsID'];
            }
            $this->params['locale']['Add interesting'] = $this->_locale['Add interesting'];
            $this->params['locale']['In interesting'] = $this->_locale['In interesting'];

            $this->breadcrumbs = [
                0 => [
                    'title' => $this->_locale['main'],
                    'url' => '/'
                ],
                1 => [
                    'title' => $this->_locale['Careers'],
                    'url' => '/вакансии'
                ]
            ];

            $prof_item = false;
            if($job['profession']){
                $prof_item = $this->_mprofessions->getProfessionByUrl($job['profession']);

                $this->breadcrumbs[] = [
                    'title' => $prof_item['name'],
                    'url' => '/professions/'.$prof_item['url'].'/'.$job['jobs_citi']
                ];
            }

            $this->breadcrumbs[] = [
                'title' => $job['jobs_title'],
                'url' => $job['jobs_url']
            ];

            $this->render('view', array(
                'job'           => $job,
                'similar_query' => '/jobs/search?' . $similar_query,
                'similar_jobs'  => $similar_jobs['data'],
                'user'          => $user,
                '_auth'         => $this->_auth,
                'company'       => $company,
                'jobs_city'     => explode(', ', $job['jobs_citi']),
                'resume_count'  => $resume_count,
                '_auth'         => $this->_auth,
                '_locale'       => $this->_locale
            ));
        }
    }

    public function actionJobResumeWindow() {
        if ($this->_auth) {
            $job_id = file_get_contents('php://input');

            if ((int) $job_id) {
                $job  = $this->_mjobs->getJobsID($job_id, 0);
                $user = $this->_musers->getUserID($this->_auth['id']);

                if (empty($this->_mresume)) {
                    $this->_mresume = \init::app()->getModels('resume/mresume');
                }

                // $param 0 in getResumeUserID is set to get positions with no draft
                $resume = $this->_mresume->getResumeUserID($this->_session['id'], 1);

                if (!empty($resume)) {
                    $this->render('job_resume_window', array(
                        'user_id'      => $this->_session['id'],
                        'user_name'    => $this->_session['firstName'] . ' ' . $this->_session['lastName'],
                        'job_id'       => $job_id,
                        'jobs_url'     => $job['jobs_url'],
                        'employer_id'  => $job['UserID'],
                        'job_title'    => $job['jobs_title'],
                        'company_name' => $job['company_name'],
                        'salary'       => $job['salary'],
                        'resume'       => $resume,
                        'job'          => $job,
                        'user'         => $user,
                        '_locale'      => $this->_locale
                    ));
                } else {
                    $this->actionJobResumeFileWindow();
                }
            }
        } else {
            $this->actionJobResumeFileWindow();
        }
    }

    public function actionJobResumeFileWindow() {
        $job_id = file_get_contents('php://input');

        if ((int) $job_id) {
            $job  = $this->_mjobs->getJobsID($job_id);
            $user = $this->_musers->getUserID($this->_auth['id']);

            $this->render('job_resume_window_file', array(
                'job_id'       => $job_id,
                'employer_id'  => $job['UserID'],
                'jobs_url'     => $job['jobs_url'],
                'job_title'    => $job['jobs_title'],
                'company_name' => $job['company_name'],
                'salary'       => $job['salary'],
                'job'          => $job,
                'user'         => $user,
                '_locale'      => $this->_locale
            ));
        }
    }

    public function actionByCatalog() {
        $this->layout('main_and_menu_line');

        $this->auth = $this->_auth;
        $this->active_section = \init::app()->getTreeSection();

        $this->breadcrumbs = [
            0 => [
                'title' => $this->_locale['main'],
                'url' => '/'
            ],
            1 => [
                'title' => $this->_locale['Careers'],
                'url' => '/вакансии'
            ],
            2 => [
                'title' => $this->_locale['jobs directories'],
                'url' => '/jobs/by-catalog'
            ]
        ];

        $this->menu_child = [
            0 => [
                'title' => $this->_locale['For sections'],
                'url' => '/jobs/by-catalog'
            ],
            1 => [
                'title' => $this->_locale['For cities'],
                'url' => '/jobs/by-region'
            ]
        ];

        $this->render('by_catalog', array(
            '_locale'                => $this->_locale
        ));
    }

    public function actionWelcomeCreateJobs() {
        $this->layout('main_light');

        $this->render('welcome_create_jobs', array(
            '_locale'   => $this->_locale
        ));

    }

    public function actionByRegion() {
        $this->layout('main_and_menu_line');

        $this->breadcrumbs = [
            0 => [
                'title' => $this->_locale['main'],
                'url' => '/'
            ],
            1 => [
                'title' => $this->_locale['Careers'],
                'url' => '/вакансии'
            ],
            2 => [
                'title' => $this->_locale['Jobs by cities'],
                'url' => '/jobs/by-region'
            ]
        ];

        $this->menu_child = [
            0 => [
                'title' => $this->_locale['For sections'],
                'url' => '/jobs/by-catalog'
            ],
            1 => [
                'title' => $this->_locale['For cities'],
                'url' => '/jobs/by-region'
            ]
        ];

        $citi = array();
        $actual_region = false;

        $citi_list = $this->_mciti->getCitiByRegion();

        if ($citi_list) {
            foreach ($citi_list as $citi_list_item) {
                if (strpos($citi_list_item->name, ' ') !== false) {
                    $citi_list_item->url = str_replace(' ', '_', $citi_list_item->name);
                } else {
                    $citi_list_item->url = $citi_list_item->name;
                }

                if ($actual_region != $citi_list_item->RegionID) {
                    $actual_region = $citi_list_item->RegionID;
                    $citi[$actual_region]['region_name'] = $citi_list_item->region_name;
                }
                $citi[$actual_region]['citis'][] = $citi_list_item;
            }

            foreach ($citi as $key => $citi_item) {
                $citi_count          = count($citi_item['citis']);
                $citi[$key]['count'] = $citi_count;
                $citi_list_count     = (int) ($citi_count / 3);

                if ($citi_count % 3) {
                    $citi_list_count++;
                }

                $citi[$key]['count_list'] = $citi_list_count;
            }
        }

        $this->render('by_region', array(
            'citi'     => $citi,
            '_lang'    => $this->_lang,
            '_locale'  => $this->_locale
        ));
    }

    public function actionStudent() {
        $this->layout('home');

        $catalog = $this->_mcatalog->getCatalog();
        $catalog_count = count($catalog);

        $catalog_list_one_count = (int) ($catalog_count / 2);

        if ($catalog_count % 2) {
            $catalog_list_one_count++;
        }

        $this->render('student', array(
            'catalog'                => $catalog,
            'catalog_count'          => $catalog_count,
            'catalog_list_one_count' => $catalog_list_one_count,
            '_auth'                  => $this->_auth,
            '_locale'                => $this->_locale
        ));
    }

    public function actionCreate() {
        $this->layout('home_employer');

        if (!$this->_auth) {
            $this->redirect('/employer/register');
        } elseif ($this->_auth['user_type'] !== '2') {
            $this->redirect('/no-function');
        } else {

            $_data  = json_decode(file_get_contents('php://input'), true);
            $user = $this->_musers->getUserID($this->_session['id']);

            if (is_array($_data) and count($_data) > 0 ) {

                $result['error'] = false;

                $verif_result = $this->verificationJobs($_data);
                $_verif_jobs =  false;

                if(!$verif_result['error']){
                    $_verif_jobs = $verif_result['data'];
                }

                if(!$verif_result['error']){

                    $new_jobs = $this -> initJobs($_verif_jobs, $user);
                    $new_jobs = $this -> autoIndexingJobs($new_jobs);

                    try{
                        $result['JobsID'] = $this->_mjobs->save(true, $new_jobs);
                        $this->addUserEventInSatistics('create_job');
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }

                    $this->incrementCitiesJobsCount($_verif_jobs['cities']);
                    $this->incrementCatalogsJobsCount($_verif_jobs['catalogs']);
                    $this->incrementCompanyJobsCount($new_jobs['CompanyID']);

                    $this->addCatalogsForJobs($result['JobsID'], $_verif_jobs['catalogs']);
                    $this->addCitiesForJobs($result['JobsID'], $_verif_jobs['cities']);

                } else {
                    $result['error'] = true;
                    $result['error_array'] = $verif_result['error_array'];
                }

                echo json_encode($result);
            } else {
                $catalog = $this->_mcatalog->getCatalog();

                $catalog_count = count($catalog);
                $catalog_list_one_count = (int)($catalog_count / 2);

                if ($catalog_count % 2) {
                    $catalog_list_one_count++;
                }

                $this->render('create', array(
                    'user'                   => $user,
                    'catalog'                => $catalog,
                    'catalog_count'          => $catalog_count,
                    'catalog_list_one_count' => $catalog_list_one_count,
                    'citi'                   => $this->_mciti->getCitiByAmount(2),
                    'menu'                   => $this->_mmenu_item->getMenuGroupID(6),
                    'sections_actual'        => \init::app()->getTreeSection(),
                    '_locale'                => $this->_locale
                ));
            }
        }
    }

    public function changeProfessionJobsCount($profession, $increase = true){
        $professions_item = $this->_mprofessions->getProfessionByUrl($profession);

        try{
            $professions_update = [];
            $professions_update['ProfessionID'] = $professions_item['ProfessionID'];

            if($increase)
                $professions_update['jobs_count'] = (int)$professions_item['jobs_count'] + 1;
            else
                $professions_update['jobs_count'] = (int)$professions_item['jobs_count'] - 1;

            $this->_mprofessions->save(true, $professions_update);
        } catch (Exception $e) {
            echo '{ERROR} -> '.$e->getMessage();
        }
    }

    public function incrementProfessionJobsCityCount($profession, $city_str){
        $citys = explode(',', $city_str);
        foreach ($citys as $citys_item){

            $model_professions_jobs_city_count = false;
            if(empty($model_professions_jobs_city_count)){
                $model_professions_jobs_city_count = \init::app()->getModels('professions_jobs_city_count/mprofessions_jobs_city_count');
            }
            $prof_city_count = $model_professions_jobs_city_count->getCountForCityByProfession($citys_item, $profession);

            $prof_city_count_save = [];

            if(!empty($prof_city_count)){
                $prof_city_count_save['ID'] = $prof_city_count['ID'];
                $prof_city_count_save['count'] = (int)$prof_city_count['count'] + 1;
            } else{
                $prof_city_count_save['count'] = 1;
                $prof_city_count_save['city'] = $citys_item;
                $prof_city_count_save['profession'] = $profession;
            }

            try{
                $model_professions_jobs_city_count->save(true, $prof_city_count_save );
            } catch (Exception $e) {
                echo '{ERROR} -> '.$e->getMessage();
            }
        }
    }

    public function decrementProfessionJobsCityCount($profession, $city_str){
        $citys = explode(',', $city_str);
        foreach ($citys as $citys_item){
            $model_professions_jobs_city_count = false;
            if(empty($model_professions_jobs_city_count)){
                $model_professions_jobs_city_count = \init::app()->getModels('professions_jobs_city_count/mprofessions_jobs_city_count');
            }

            $prof_city_count = $model_professions_jobs_city_count->getCountForCityByProfession($citys_item, $profession);

            $prof_city_count_update = [];
            $prof_city_count_update['ID'] = $prof_city_count['ID'];
            $prof_city_count_update['count'] = (int)$prof_city_count['count'] - 1;

            try{
                $model_professions_jobs_city_count->save(true, $prof_city_count_update );
            } catch (Exception $e) {
                echo '{ERROR} -> '.$e->getMessage();
            }
        }
    }

    public function autoIndexingJobs($jobs){
        $analyzed_title_array = $this->analyzeJobsTitle($jobs['jobs_title']);

        if(!isset($analyzed_title_array['clear'])){
            $jobs['indexed'] = 1;
        }

        $jobs['similar_words_index'] = $analyzed_title_array['str_index'];

        return $jobs;
    }


    public function analyzeJobsTitle($title){

        if (empty($this->_msearch_stop_word)) {
            $this->_msearch_stop_word = \init::app()->getModels('search_stop_word/msearch_stop_word');
        }

        $jobs_analyzis = [];

        $title = strtr($title, $this->_STOP_SIMVOL);
        $query_array = explode(' ', $title);

        $stop_words_array = $this->_msearch_stop_word->getStopWord(); //["с", "з", "в", "по", "на"];
        foreach ($query_array as $key => $query_array_item) {
            foreach ($stop_words_array as $stop_words_array_item) {
                if (mb_strtolower($query_array_item, 'UTF-8') === $stop_words_array_item->word) {
                    $jobs_analyzis['stop'][] = $query_array_item;
                    unset($query_array[$key]);
                    break;
                }
            }
        }

        $search_query = '';
        foreach ($query_array as $query_array_item) {
            $search_query = $search_query . "'" . mb_strtolower($query_array_item, 'UTF-8') . "', ";
        }
        $search_query = substr($search_query, 0, strlen($search_query) - 2);
        $search_result = $this->_mdictinary_prefix->getWordsByQueryNotLang($search_query);

        $str_index = '';

        foreach ($query_array as $query_array_item) {
            $flag_index = false;
            foreach ($search_result as $search_result_item) {
                if (mb_strtolower($query_array_item, 'UTF-8') === $search_result_item->prefix) {
                    $jobs_analyzis['index'][] = $query_array_item;
                    $str_index = $str_index . $search_result_item->word_index . ' ';
                    $flag_index = true;
                    break;
                }
            }
            if (!$flag_index) {
                $jobs_analyzis['clear'][] = $query_array_item;
            }
        }

        if(strlen($str_index) > 0){
            $str_index =  substr($str_index, 0, strlen($str_index) - 1);
        }
        $jobs_analyzis['str_index'] = $str_index;

        return $jobs_analyzis;
    }

    public function initJobs($jobs, $user){

        $job_catalog_name = '';
        $job_city_name    = '';

        $catalog_name = $this->_mcatalog->getCatalogName($jobs['catalogs']);
        $city_name    = $this->_mciti->getCityName($jobs['cities']);

        if ($catalog_name) {
            foreach ($catalog_name as $catalog) {
                $job_catalog_name .= $catalog->name . ', ';
            }
        }
        $job_catalog_name = substr($job_catalog_name, 0, strlen($job_catalog_name) - 2);

        if ($city_name) {
            foreach ($city_name as $city) {
                $job_city_name .= $city->name . ', ';
            }
        }
        $job_city_name = substr($job_city_name, 0, strlen($job_city_name) - 2);

        $company = $this->_mcompany->getCompanyID($user['CompanyID']);

        if ($company['checked'] == 1 || $company['phoned'] == 1) {
            $jobs['info']['status'] = 'revise';
        }

        $translite_text = strtr($jobs['info']['jobs_title'], $this->_TRANSLITERATION);
        $jobs['info']['jobs_url'] = strtolower($translite_text);

        if(isset($jobs['info']['employment_type_full_time']))
            $jobs['info']['employment_type_full_time'] = (int)$jobs['info']['employment_type_full_time'];
        if(isset($jobs['info']['employment_type_part_time']))
            $jobs['info']['employment_type_part_time'] = (int)$jobs['info']['employment_type_part_time'];
        if(isset($jobs['info']['employment_type_telecommuting']))
            $jobs['info']['employment_type_telecommuting'] = (int)$jobs['info']['employment_type_telecommuting'];

        if(isset($jobs['info']['business_trip']))
            $jobs['info']['business_trip'] = (int)$jobs['info']['business_trip'];
        if(isset($jobs['info']['emigration']))
            $jobs['info']['emigration'] = (int)$jobs['info']['emigration'];
        if(isset($jobs['info']['business_trip']))
            $jobs['info']['invite_student'] = (int)$jobs['info']['invite_student'];
        if(isset($jobs['info']['hide_phone']))
            $jobs['info']['hide_phone'] = (int)$jobs['info']['hide_phone'];

        $jobs['info']['catalogs']        = $job_catalog_name;
        $jobs['info']['UserID']          = $user['userID'];
        $jobs['info']['CompanyID']       = $user['CompanyID'];
        $jobs['info']['company_name']    = $user['company_name'];
        $jobs['info']['company_logo']    = $company['company_logo'];
        $jobs['info']['company_website'] = $company['company_website'];
        $jobs['info']['TimeCreated']     = date('Y-m-d H:i:s');
        $jobs['info']['TimeSaved']       = $jobs['info']['TimeCreated'];
        $jobs['info']['jobs_citi']       = $job_city_name;
        $jobs['info']['jobs_text']       =  htmlspecialchars($jobs['info']['jobs_text']);

        $new_jobs = $jobs['info'];

        if(isset($jobs['JobsID']))
            $new_jobs['JobsID'] = $jobs['JobsID'];

        return $new_jobs;
    }

    public function verificationJobs($_data){
        $result['error'] = false;

        $verif_info = CDefender::arrayValidateForOption($_data['info'], [
            'required' => ['jobs_title', 'jobs_text', 'contact', 'email', 'phone'],
            'text' => ['jobs_title', 'jobs_coment_salary', 'contact', 'email', 'phone', 'job_placement'],
            'number' => ['salary', 'jobs_experience', 'jobs_education'],
            'checkbox' => ['business_trip', 'emigration', 'invite_student', 'employment_type_full_time', 'employment_type_part_time', 'employment_type_telecommuting', 'hide_phone']
        ]);

        if(!$verif_info['error']){
           $_data['info'] = $verif_info['data'];
        } else {
            $result['error'] = true;
            $result['error_array']['info'] = $verif_info['error_array'];
        }

        $verif_catalog = CDefender::verifCheckboxArray($_data["catalogs"]);
        if(count($verif_catalog) > 3){
            $result['error'] = true;
            $result['error_array']['catalogs']['count'] = true;
        } else {
            foreach ($verif_catalog as $verif_catalog_key => $verif_catalog_item) {
                if($verif_catalog_key !== $verif_catalog_item){
                    $verif_catalog[$verif_catalog_key] = $verif_catalog_key;
                }
            }
            $_data["catalogs"] = $verif_catalog;
        }

        $verif_citys = CDefender::verifCheckboxArray($_data["cities"]);
        if(count($verif_citys) > 3){
            $result['error'] = true;
            $result['error_array']['cities']['count'] = true;
        } else {
            foreach ($verif_citys as $verif_citys_key => $verif_citys_item) {
                if($verif_citys_item === 0){
                    $result['error'] = true;
                    $result['error_array']['cities'] = true;
                }
            }
            $_data["cities"] = $verif_citys;
        }

        if(!$result['error']){
            $result['data'] = $_data;
        }

        return $result;
    }

    public function actionSearch() {
        $this->layout('jobs');

        $params   = \init::app()->getRequest()->getParam('job');
        $page     = \init::app()->getRequest()->getParam('page');
        $catalogs = $this->_mcatalog->getCatalog();
        $limit    = 10;
        $query    = '';

        $selected_city = false;
        //if(isset($params['city']) AND !empty(current($params['city']))){
            //$selected_city = current($params['city']);
        //}

        $txt_labels = array(
            'text_query' => array(),
            'catalog'  => array(), 'salary' => array(), 'query' => array(),
            'title'    => array(0 => array('title',          'Шукати будь-яке з слів')),
            'any_word' => array(0 => array('any_word',       'Шукати будь-яке з слів')),
            'student'  => array(0 => array('invite_student', 'Для студентів')),
            'synonym'  => array(0 => array('synonym',        'Включаючи синоніми')),
            'city' => array(
                158 => array('city_one',   'Київ'),
                24  => array('city_two',   'Дніпропетровськ'),
                63  => array('city_three', 'Донецьк'),
                261 => array('city_four',  'Одеса'),
                326 => array('city_five',  'Харків')
            ),
            'employment_type' => array(
                1 => array('employment_type_full_time',     'Повна зайнятість'),
                2 => array('employment_type_part_time',     'Часткова зайнятість'),
                3 => array('employment_type_telecommuting', 'Дистанційна робота')
            ),
            'education' => array(
                4 => array('higher_education',            'Вища'),
                3 => array('incomplete_higher_education', 'Незакінчена вища'),
                2 => array('secondary_special_education', 'Середня спеціальна'),
                1 => array('secondary_education',         'Середня')
            ),
            'experience' => array(
                1 => array('no_exp',     'Без досвіду'),
                2 => array('one_year',   'Від 1 року'),
                3 => array('two_years',  'Від 2 років'),
                4 => array('five_years', 'Більше 5 років')
            )
        );

        if (!$page) {
            $page = 1;
        }

        if (!empty($params)) {
            $query = http_build_query(array('job' => $params));

            if($this->_auth) {
                $user_id = $this->_auth['id'];
            } else {
                $user_id = false;
            }

            if (isset($params['text_query'])) {
                $params['query'] = $this->getSearchQueryByText($params['text_query']);
            }

            $result   = $this->_mjobs->search($params, $page, $user_id, $limit);
            $page_nav = $this->generatePageNav($page, 10, $result['page_count']);

            $jobs_ids = $companies_list = array();

            foreach ($result['data'] as $job) {
                $jobs_ids[] = $job->CompanyID;
            }

            $companies = $this->_mcompany->getCompanyID($jobs_ids);

            if (!empty($companies)) {
                foreach ($companies as $company) {
                    $companies_list[$company->CompanyID] = $company;
                }
            }

            $first_city = false;

            if(isset($params['city']) AND !empty($params['city'])){
                foreach ($params['city'] as $city_item){
                    $first_city = $city_item;
                    break;
                }
            }

            $search_option = [
                'city_id'     => $first_city,
                'text_query' => isset($params['text_query'])?$params['text_query']:'',
                'query' => isset($params['query'])?$params['query']:''
            ];

            // We don't want to index pages with filter
            $this->noindex = true;

            $this->render('search', array(
                'search_results' => $result['data'],
                'start'          => $page_nav['start'],
                'end'            => $page_nav['end'],
                'page'           => $page_nav['page'],
                'page_count'     => $page_nav['count'],
                'count'          => $result['count'],
                'params'         => $params,
                'query'          => $query,
                'catalogs'       => $catalogs,
                'companies'      => $companies_list,
                'txt_labels'     => $txt_labels,
                '_auth'          => $this->_auth,
                '_locale'        => $this->_locale,
                'selected_city'  => $selected_city,
                'search_option'  => $search_option
            ));
        } else {
            $paginator      = new Paginator('jobs', 'JobsID', array('limit' => $limit));
            $paginated_data = $paginator->paginateJobsInSearch($this->_auth['id']);
            $page_nav       = $this->generatePageNav($page, 10, $paginated_data['params']['page_count']);

            $jobs_ids = $companies_list = array();

            foreach ($paginated_data['data'][0] as $job) {
                $jobs_ids[] = $job->CompanyID;
            }

            $companies = $this->_mcompany->getCompanyID($jobs_ids);

            if (!empty($companies)) {
                foreach ($companies as $company) {
                    $companies_list[$company->CompanyID] = $company;
                }
            }

            // We don't want to index pages with filter
            $this->noindex = true;

            $this->render('search', array(
                'search_results' => $paginated_data['data'][0],
                'start'          => $page_nav['start'],
                'end'            => $page_nav['end'],
                'page'           => $page_nav['page'],
                'page_count'     => $page_nav['count'],
                'count'          => $paginated_data['params']['count'],
                'catalogs'       => $catalogs,
                'companies'      => $companies_list,
                'txt_labels'     => $txt_labels,
                'query'          => '',
                '_auth'          => $this->_auth,
                '_locale'        => $this->_locale,
                'selected_city'  => $selected_city
            ));
        }
    }

    public function actionJobsNotFound() {
        $this->layout('home');

        $this->render('jobs_not_found', array(
            '_locale'            => $this->_locale
        ));
    }

    public function actionSettingsMailingJobs() {
        $this->layout('main_light');

        $this->render('settings_mailing_jobs', array(
            '_auth'  => $this->_auth,
            'cities' => $this->_mciti->getCitiByAmount(2),
            '_locale'                => $this->_locale
        ));
    }

    public function actionCatalogBloc() {
        $catalog = false;

        if(isset($_POST['params']['limit']))
            $catalog = $this->_mcatalog->getCatalogLimit(0, $_POST['params']['limit']);
        else
            $catalog = $this->_mcatalog->getCatalog();

        $catalog_count = count($catalog);
        $catalog_list_one_count = (int) ($catalog_count / 2);
        if ($catalog_count % 2)
            $catalog_list_one_count++;

        $city = 'Украина';
        if(isset($_POST['params']['city'])){
            $city = $_POST['params']['city'];

            $city_id     = $this->_mciti->getCityIdByName($city);
            $jobs_cities = $this->_mjobs_city->getJobsIdsByCityID($city_id['CitiID']);
            $jobs_ids    = array();

            foreach ($jobs_cities as $job_city) {
                $jobs_ids[] = $job_city->JobsID;
            }

            // Count all jobs in current city
            $jobs_count = count($jobs_ids);

            // Jobs count in catalogs block
            $jobs_catalogs = $this->_mjobs_catalog->getJobsCatalogsByJobsIds($jobs_ids);
            $jobs_counter  = array();

            foreach ($jobs_catalogs as $job_catalog) {
                $jobs_counter[$job_catalog->CatalogID][] = $job_catalog->JobsID;
            }

            foreach ($catalog as $key => $catalog_item) {
                if(isset($jobs_counter[$catalog_item->CatalogID]))
                    $catalog[$key]->jobs_count = count($jobs_counter[$catalog_item->CatalogID]);
                else
                    $catalog[$key]->jobs_count = 0;
            }
        }

        if (strpos($city, ' ') !== false) {
            $city = str_replace(' ', '_', $city);
        }

        $this->render('catalog_bloc', array(
            'catalog'                => $catalog,
            'catalog_count'          => $catalog_count,
            'catalog_list_one_count' => $catalog_list_one_count,
            'city'                   => $city,
            '_locale'                => $this->_locale,
            '_lang'                  => $this->_lang
        ));
    }

    public function actionProfessionsBloc(){
        $city = 'Украина';
        if(isset($_POST['params']['city']))
            $city = $_POST['params']['city'];

        $link_professions_all = true;
        if(isset($_POST['params']['link_professions_all']))
            $link_professions_all = $_POST['params']['link_professions_all'];

        if (strpos($city, ' ') !== false) {
            $city = str_replace(' ', '_', $city);
        }

        // Professions block
        if (empty($this->_mprofessions)) {
            $this->_mprofessions = \init::app()->getModels('professions/mprofessions');
        }

        $professions = [];
        if($city !== 'Украина'){
            $professions = $this->_mprofessions->getTopProfessionsForCityByLimit($city);
        } else {
            $professions = $this->_mprofessions->getTopProfessionsByLimit();
        }

        $this->render('professions_bloc', array(
            'link_professions_all' => $link_professions_all,
            'city'                 => $city,
            'professions'          => $professions,
            'professions_count'    => count($professions),
            '_locale'              => $this->_locale,
            '_lang'                => $this->_lang
        ));
    }

    public function actionCityByRegionBloc(){

        $city_name = '';

        if(isset($_POST['params']['city']))
            $city_name = $_POST['params']['city'];

        $city_id = $this->_mciti->getCityIdByName($city_name);
        $city = $this->_mciti->getCitiID($city_id['CitiID']);
        $city_by_region = $this->_mciti->getCitysByRegion($city['RegionID']);

        foreach ($city_by_region as $value) {
            if (strpos($value->name, ' ') !== false) {
                $value->url = str_replace(' ', '_', $value->name);
            } else {
                $value->url = $value->name;
            }
        }

        $city_by_region_count = count($city_by_region);
        $count_column = $city_by_region_count / 3;
        if ($city_by_region_count % 3)
            $count_column++;
        $count_column_double = $count_column * 2;

        $this->render('city_by_region_bloc', array(
            'city_by_region'       => $city_by_region,
            'city_by_region_count' => $city_by_region_count,
            'count_column'         => $count_column,
            'count_column_double'  => $count_column_double,
            '_locale'              => $this->_locale
        ));
    }

    public function actionFilter(){

        $catalogs = $this->_mcatalog->getCatalog();

        $this->render('filter', array(
            'catalogs'             => $catalogs,
            '_locale'              => $this->_locale
        ));
    }

    public function getSearchQueryByText($text){

        if (empty($this->_mdictinary_word)) {
            $this->_mdictinary_word = \init::app()->getModels('dictinary_word/mdictinary_word');
        }
        if (empty($this->_msearch_word_group)) {
            $this->_msearch_word_group = \init::app()->getModels('search_word_group/msearch_word_group');
        }
        if (empty($this->_msearch_stop_word)) {
            $this->_msearch_stop_word = \init::app()->getModels('search_stop_word/msearch_stop_word');
        }
        if (empty($this->_mdictinary_prefix)) {
            $this->_mdictinary_prefix = \init::app()->getModels('dictinary_prefix/mdictinary_prefix');
        }

        $search_query = false;

//      Фільтрація від STOP-символів ["/", "-", "\", ")", "}"];
        $query_text = strtr($text, $this->_STOP_SIMVOL);

//      Фільтрація від STOP-слів ["с", "з", "в", "по", "на"];
        $stop_words = [];
        $stop_words_array = $this->_msearch_stop_word->getStopWord();
        foreach ($stop_words_array as $stop_words_array_item) {
            $stop_words[' '.$stop_words_array_item->word.' '] = ' ';
        }
        $query_text = strtr($query_text, $stop_words);

        $query_text = trim($query_text); // Видалення зайвих пробілів

//      Виділення масиву слів для пошуку шляхом розділення строки по пробілу
        $query_array = explode(' ', $query_text);

        $search_query = '';
        foreach ($query_array as $query_array_item) {
            $search_query = $search_query."'".$query_array_item."', ";
        }
        $search_query = substr($search_query, 0, strlen($search_query) - 2);
        $search_result = $this->_mdictinary_prefix->getWordsByQueryNotLang($search_query);

        $search_group_query = '';
        $result['query_index'] = '';

        $unique_group_index = [];
        $unique_word_index = [];
        foreach ($search_result as $search_result_item) {
            if(!isset($unique_group_index[$search_result_item->WordGroupID])){
                $unique_group_index[$search_result_item->WordGroupID] = $search_result_item->WordGroupID;
                $search_group_query = $search_group_query.$search_result_item->WordGroupID.", ";
            }
            if(!isset($unique_word_index[$search_result_item->word_index])){
                $unique_word_index[$search_result_item->word_index] = $search_result_item->word_index;
                $result['query_index'] = $result['query_index'].'+'.$search_result_item->word_index.' ';
            }
        }

        if($result['query_index'])
             $result['query_index'] = substr($result['query_index'], 0, strlen($result['query_index']) - 1);
        if($search_group_query)
             $search_group_query = substr($search_group_query, 0, strlen($search_group_query) - 2);



        return $result['query_index'];
    }

    function uniord($ch) {

         $n = ord($ch{0});

         if ($n < 128) {
             return $n; // no conversion required
         }

         if ($n < 192 || $n > 253) {
             return false; // bad first byte || out of range
         }

         $arr = array(1 => 192, // byte position => range from
                      2 => 224,
                      3 => 240,
                      4 => 248,
                      5 => 252,
                      );

         foreach ($arr as $key => $val) {
             if ($n >= $val) { // add byte to the 'char' array
                 $char[] = ord($ch{$key}) - 128;
                 $range  = $val;
             } else {
                 break; // save some e-trees
             }
         }

         $retval = ($n - $range) * pow(64, sizeof($char));

         foreach ($char as $key => $val) {
             $pow = sizeof($char) - ($key + 1); // invert key
             $retval += $val * pow(64, $pow);   // dark magic
         }

         return $retval;
    }

    function addCatalogsForJobs($jobs_id, $catalogs) {
        foreach ($catalogs as $catalog) {
            if (!empty($catalog)) {
                $new_record['JobsID']    = $jobs_id;
                $new_record['CatalogID'] = $catalog;

                $this->_mjobs_catalog->save(true, $new_record);
            }
        }
    }

    function deleteCatalogsForJobs($jobs_id) {
        $jobs_catalog_array = $this->_mjobs_catalog->getCatalogsByJobID($jobs_id);

        foreach ($jobs_catalog_array as $jobs_catalog_array_item) {
            try{
                $this->_mjobs_catalog->delete(array('JobsCatalogID' => $jobs_catalog_array_item->JobsCatalogID));
            } catch (Exception $e) {
                echo 'ERROR DELETE' . $e->getMessage();
            }
        }
    }

    function addCitiesForJobs($jobs_id, $cities) {
        foreach ($cities as $city) {
            if (!empty($city)) {
                $new_record['JobsID'] = $jobs_id;
                $new_record['CityID'] = $city;

                $this->_mjobs_city->save(true, $new_record);
            }
        }
    }

    function deleteCitisForJobs($jobs_id) {
        $jobs_city_array = $this->_mjobs_city->getCitiesByJobID($jobs_id);

        foreach ($jobs_city_array as $jobs_city_array_item) {
            try{
                $this->_mjobs_city->delete(array('JobsCityID' => $jobs_city_array_item->JobsCityID));
            } catch (Exception $e) {
                echo 'ERROR DELETE' . $e->getMessage();
            }
        }
    }

    function updateCitiesForJobs($jobs_id, $cities) {
        $this->deleteCitisForJobs($jobs_id);
        $this->addCitiesForJobs($jobs_id, $cities);
    }

    function updateCatalogsForJobs($jobs_id, $catalogs){
        $this->deleteCatalogsForJobs($jobs_id);
        $this->addCatalogsForJobs($jobs_id, $catalogs);
    }

    function incrementCitiesJobsCount($cities) {
        foreach ($cities as $city_id) {
            $count = $this->_mciti->getJobsCountByID($city_id);

            $count++;

            $this->_mciti->save(true, [
                'CitiID'     => $city_id,
                'jobs_count' => $count
            ]);
        }
    }

    function decrementCitiesJobsCount($cities) {
        foreach ($cities as $city_id) {
            $count = $this->_mciti->getJobsCountByID($city_id);

            $count--;

            $this->_mciti->save(true, [
                'CitiID'      => $city_id,
                'jobs_count'  => $count
            ]);
        }
    }

    function incrementCatalogsJobsCount($catalogs){
        foreach ($catalogs as $catalogs_item) {
            $count = $this->_mcatalog->getJobsCountByID($catalogs_item);
            $new_record['CatalogID'] = $catalogs_item;
            $new_record['jobs_count'] = (int)$count + 1;
            $this->_mcatalog->save(true, $new_record);
        }
    }

    function decrementCatalogsJobsCount($catalogs){
        foreach ($catalogs as $catalogs_item) {
            $count = $this->_mcatalog->getJobsCountByID($catalogs_item);
            $new_record['CatalogID'] = $catalogs_item;
            $new_record['jobs_count'] = (int)$count - 1;
            $this->_mcatalog->save(true, $new_record);
        }
    }

    function incrementCompanyJobsCount($company_id){
        $count = $this->_mcompany->getCompanyJobsCount($company_id);
        $update_record['CompanyID'] = $company_id;
        $update_record['jobs_count'] = (int)$count + 1;
        $this->_mcompany->save(true, $update_record);
    }

    function decrementCompanyJobsCount($company_id){
        $count = $this->_mcompany->getCompanyJobsCount($company_id);
        $update_record['CompanyID'] = $company_id;
        $update_record['jobs_count'] = (int)$count - 1;
        $this->_mcompany->save(true, $update_record);
    }

    function updateCatalogsJobsCount($jobs_id, $catalogs){
        $catalog_jobs = $this->_mjobs_catalog->getCatalogsByJobID($jobs_id);

        $catalogs_old = [];
        foreach ($catalog_jobs as $catalog_jobs_item) {
            $catalogs_old[] = $catalog_jobs_item->CatalogID;
        }

        $this->decrementCatalogsJobsCount($catalogs_old);
        $this->incrementCatalogsJobsCount($catalogs);
    }

    public function generatePageNav($page, $visible_pages, $page_count) {
        $visible_pages = 10;
        $start         = '';
        $end           = '';

        if ($page_count > 1) {
            $left  = $page - 1;
            $right = $page_count - $page;

            if ($left < floor($visible_pages / 2)) {
                $start = 1;
            } else {
                $start = $page - floor($visible_pages / 2);
            }

            $end = $start + $visible_pages - 1;

            if ($end > $page_count) {
                $start -= ($end - $page_count);
                $end    = $page_count;

                if ($start < 1) {
                    $start = 1;
                }
            }
        }

        $result = array(
            'start' => $start,
            'end'   => $end,
            'page'  => $page,
            'count' => $page_count
        );

        return $result;
    }

    function addUserEventInSatistics($event_name){
        $new_statistics_user_event = [];
        $new_statistics_user_event['date'] = date('Y-m-d H:i:s');
        $new_statistics_user_event['type_event'] = $event_name;
        $new_statistics_user_event['UserID'] = $this->_auth['id'];
        $new_statistics_user_event['user_type'] = $this->_auth['user_type'];
        $new_statistics_user_event['RoleID'] = $this->_auth['RoleID'];
        $this->_mstatistics_user_events->save(true, $new_statistics_user_event);
    }
}
