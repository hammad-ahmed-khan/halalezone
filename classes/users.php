<?php
class cuser {
	public $db; // Объект подключения к БД
	private static $instance;
	// данные о юзвере
	public $userdata;

    private function __construct ()
	{
		$db = acsessDb::singleton();
		$this->db =  $db->connect();
		//$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	}
	// Метод singleton
	public static function singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    // Предотвращаем клонирование экземпляра пользователем
    private function __clone()
    {
        trigger_error('Это ж singleton!!!', E_USER_ERROR);
    }

	private function checkSession(){
		if($this->login_check() == false) {
			header('HTTP/1.0 200 OK');
			include_once ('pages/login.php');
			exit;
		}
		return;
	}

	private function checkAccess($page){
		if($this->userdata[$page] == 0){
			header('HTTP/1.0 403 Access denied');
			include_once('pages/403.php');
			Exit();
		}
	}

	private function checkExist($page){
		if(!isset($this->userdata[$page])){
			header('HTTP/1.0 404 Page not found');
			include_once('pages/404.php');
			Exit();
		}
	}

	public function getUserData()
	{
		//$sql = "SELECT id, name, email, dashboard, prefix, isclient, clients,  application, audit, canadmin FROM tusers WHERE id=:id";
		$sql = "SELECT id, company_id, parent_id, name, email, prefix, isclient, dashboard, application, calendar, products, ingredients, documents, clients, canadmin, superadmin, clients_audit, sources_audit, company_admin, products_preference, ingredients_preference, qm_documents_preference FROM tusers WHERE id=:id";
		$res = $this->db->prepare($sql);
		$res->bindValue(':id', $_SESSION['halal']['id']);
		$userdata = [];
		if($res->execute()) {
			$userdata = $res->fetch(PDO::FETCH_ASSOC);
			if ($userdata['isclient'] == '1' || $userdata['isclient'] == '0') { // Client
				$userdata['dashboard'] = 1;
				$userdata['application'] = 1;
				$userdata['calendar'] = 1;
				$userdata['products'] = 1;
				$userdata['ingredients'] = 1;
				$userdata['documents'] = 1;
			}
			else if ($userdata['isclient'] == '2') { // Auditor
				$userdata['dashboard'] = 0;
				$userdata['application'] = 1;
				$userdata['products'] = 1;
				$userdata['ingredients'] = 1;
				$userdata['documents'] = 1;			
			}
		}
		$this->userdata = $userdata;
		$res->closeCursor();
	}

	///  --------------  Auth stuff ------------------
	public function sec_session_start() {
		if(session_status() != PHP_SESSION_ACTIVE)
			session_start();            // Start the PHP session
	}

	public function login($email, $password) {
		$sql = "SELECT id, company_id, name, login, pass FROM tusers WHERE deleted=0 AND login =:email";
		$stmt = $this->db->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':email', $email);
		$stmt->execute();    // Execute the prepared query.
		$user = $stmt->fetch();
		if(!empty($user)){
			if ($this->checkbrute($user['id']) == true) {
				// Account is locked
				// Send an email to user saying their account is locked
				return "Attempts number exceeded. Account is blocked";
			} else {
				// Check if the password in the database matches
				// the password the user submitted. We are using
				// the password_verify function to avoid timing attacks.
				if ($password == $user['pass']) {
					// Password is correct!
					// Get the user-agent string of the user.
					$user_browser = $_SERVER['HTTP_USER_AGENT'];
					// XSS protection as we might print this value
					$user_id = preg_replace("/[^0-9]+/", "", $user['id']);
					$_SESSION['halal']['id'] = $user_id;
					$_SESSION['halal']['company_id'] = $user['company_id'];
					$_SESSION['halal']['user'] = $user['name'];
					$_SESSION['halal']['login_string'] = hash('sha512',
						$user['pass'] . $user_browser);
					// Login successful.
					return 0;
				} else {
					// Password is not correct
					// We record this attempt in the database
					/*
					$sql = "INSERT INTO attempts (iduser) VALUES (:id)";
					$stmt = $this->db->prepare($sql);
					$stmt->bindValue(':id', $user['id']);
					$stmt->execute();
					*/
					return "Wrong login or password";
				}
			}
		} else {
			// No user exists.
			return "Wrong login or password";
		}
	}

	public function checkbrute($user_id) {
		return false;
		$sql = "SELECT count(id) as count FROM attempts a WHERE iduser =:id AND a.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
		$stmt = $this->db->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':id', $user_id);
		if(!$stmt->execute()) die($this->db->errorInfo()[2]);    // Execute the prepared query.
		$number = $stmt->fetch();
		if ($number['count'] > 5) {
			return true;
		} else {
			return false;
		}
	}

	public function login_check() {
		// Check if all session variables are set
		if (isset($_SESSION['halal'], $_SESSION['halal']['id'],
			$_SESSION['halal']['user'],
			$_SESSION['halal']['login_string'])) {

				return true;

			$user_id = $_SESSION['halal']['id'];
			$login_string = $_SESSION['halal']['login_string'];

			// Get the user-agent string of the user.
			$user_browser = $_SERVER['HTTP_USER_AGENT'];

			$sql = "SELECT pass FROM tusers WHERE id =:id";
			$stmt = $this->db->prepare($sql);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->bindValue(':id', $user_id);
			$stmt->execute();
			$user = $stmt->fetch();

			if (!empty($user)) {
				// If the user exists get variables from result.
				$login_check = hash('sha512', $user['pass'] . $user_browser);
				if ($login_check == $login_string){
					// Logged In!!!!
					return true;
				} else {
					// Not logged in
					return false;
				}
			} else {
				return false;
			}
		} else {
			// Not logged in
			return false;
		}
	}

    public function showIndex()
    {
		$this->checkSession();
        include_once('pages/dashboard.php');
    }

    public function showUpload()
    {
		if($this->login_check() != false) {
			//include_once ('pages/dashboard.php');
			//exit;
		}
        include_once('pages/upload.php');
    }

    public function showRegister()
    {
		if($this->login_check() != false) {
			include_once ('pages/dashboard.php');
			exit;
		}
        include_once('pages/register.php');
    }

    public function showCalendar()
    {
        $this->checkSession();
        $this->getUserData();
        //$this->checkExist('calendar');
        //$this->checkAccess('calendar');
        include_once('pages/calendar.php');
    }

	public function showApplication()
    {
        $this->checkSession();
        $this->getUserData();
        $this->checkExist('application');
        $this->checkAccess('application');
        include_once('pages/application.php');
    }

	public function showApplication1()
    {
        $this->checkSession();
        $this->getUserData();
        $this->checkExist('application');
        $this->checkAccess('application');
        include_once('pages/application1.php');
    }

    public function showProducts()
    {
		$this->checkSession();
		include_once('pages/products.php');
    }

    public function showIngredients()
    {
		$this->checkSession();
		include_once('pages/ingredients.php');
    }

    public function showQM()
    {
		$this->checkSession();
		include_once('pages/qm.php');
    }

	public function showAudit()
	{
		$this->checkSession();
		$this->getUserData();
		$this->checkExist('audit');
		$this->checkAccess('audit');
		include_once('pages/audit.php');
	}

	public function showAdministration()
	{
		$this->checkSession();
		$this->getUserData();
		$this->checkExist('canadmin');
		$this->checkAccess('canadmin');
		include_once('pages/administration.php');
	}
	
	public function showProcessStatus()
	{
		$this->checkSession();
		$this->getUserData();
		$this->checkExist('canadmin');
		$this->checkAccess('canadmin');
		include_once('pages/process_status.php');
	}

	public function showTickets()
	{
		$this->checkSession();
		$this->getUserData();
		include_once('pages/tickets.php');
	}
	
	public function showCustomerService()
	{
		$this->checkSession();
		$this->getUserData();
		include_once('pages/customerservice.php');
	}

	public function showTasks()
	{
		$this->checkSession();
		$this->getUserData();
		include_once('pages/tasks.php');
	}

	public function showBranches()
	{
		$this->checkSession();
		$this->getUserData();
		if ($this->userdata['isclient'] == "1" && $this->userdata['company_id'] != "" && $this->userdata['company_admin'] == "1") {
			include_once('pages/branches.php');
		}
		else {
			header('HTTP/1.0 403 Access denied');
			include_once('pages/403.php');
			Exit();
		}
	}

	public function showSettings()
	{
		$this->checkSession();
		$this->getUserData();
		$this->checkExist('canadmin');
		$this->checkAccess('canadmin');
		include_once('pages/settings.php');
	}

	public function showCompanies()
    {
		$this->checkSession();
		$this->getUserData();
		$this->checkExist('canadmin');
		$this->checkAccess('canadmin');
		include_once('pages/companies.php');
    }

	public function showPaIngreds()
    {
		$this->checkSession();
		$this->getUserData();
		$this->checkExist('canadmin');
		$this->checkAccess('canadmin');
		include_once('pages/paingreds.php');
    }

	public function showFacilities()
	{
		$this->checkSession();
		$this->getUserData();
		include_once('pages/facilities.php');
	}

	public function showPreferences()
	{
		$this->checkSession();
		$this->getUserData();
		include_once('pages/preferences.php');
	}

	public function showTraining()
	{
		$this->checkSession();
		$this->getUserData();
		include_once('pages/training.php');
	}

	public function showFAQManager()
	{
		$this->checkSession();
		$this->getUserData();
		include_once('pages/faq_manager.php');
	}

	public function showSupport()
	{
		$this->checkSession();
		$this->getUserData();
		include_once('pages/faq.php');
	}	
}
?>