<?php
class Rate_limiter extends Trongate {

    private $max_num_attempts = 3;
	private $timeout_value = 10800; // Three hours
	private $timeout_url = BASE_URL.'members/login_locked';
	private $table_name = 'login_rate_limiter';

	/**
	 * Class constructor.
	 *
	 * Prevents direct URL invocation of the module while allowing
	 * safe internal usage via application code.
	 *
	 * @param string|null $module_name The module name (auto-provided by framework)
	 */
	public function __construct(?string $module_name = null) {
	    parent::__construct($module_name);
	    block_url($this->module_name);
	}

	/**
	 * Ensure the current IP is allowed to attempt login.
	 *
	 * This method:
	 * 1. Checks whether login attempts are allowed using `is_login_attempt_allowed()`.
	 * 2. If not allowed, it immediately blocks the login attempt via `block_login_attempt()`.
	 *
	 * @return void
	 */
	public function make_sure_login_attempt_allowed(): void {
		$this->clean_up_table();
		$is_login_attempt_allowed = $this->is_login_attempt_allowed();

		if ($is_login_attempt_allowed !== true) {
			$this->block_login_attempt();
		}
	}

	/**
	 * Register a failed login attempt for the current IP address.
	 *
	 * This method performs the following actions:
	 * 1. Retrieves any existing rate limiter record for the current IP.
	 * 2. If no record exists, it creates a new record with 1 failed attempt
	 *    and sets `next_attempt_allowed` to the current time.
	 * 3. If a record exists, it increments the `num_failed_attempts`.
	 * 4. If the maximum number of attempts is reached, it updates
	 *    `next_attempt_allowed` to enforce a timeout.
	 *
	 * @return void
	 */
	public function register_failed_login_attempt(): void {
		$ip_address = ip_address();

		$record_obj = $this->db->get_one_where('ip_address', $ip_address, $this->table_name);

		if ($record_obj === false) {
			// No record exists, create a new one
			$data = [
				'ip_address'          => $ip_address,
				'num_failed_attempts' => 1,
				'next_attempt_allowed'=> time()
			];

			$this->db->insert($data, $this->table_name);
			return;
		}

		// Record exists, increment failed attempts
		$num_failed_attempts = (int) $record_obj->num_failed_attempts;
		$update_id = (int) $record_obj->id;

		$data = [
			'num_failed_attempts' => $num_failed_attempts + 1
		];

		// Only set a new timeout if max attempts reached
		if ($data['num_failed_attempts'] >= $this->max_num_attempts) {
			$data['next_attempt_allowed'] = time() + $this->timeout_value;
		}

		$this->db->update($update_id, $data, $this->table_name);
	}

	/**
	 * Handle actions to perform after a successful login.
	 *
	 * This method clears any existing login rate limiter records for the
	 * current IP address, effectively resetting the failed attempt counter.
	 *
	 * @return void
	 */
	public function login_success(): void {
		$this->clear_login_rate_limiter();
	}

	/**
	 * Determine whether the current IP address is allowed to attempt a login.
	 *
	 * This method performs the following checks:
	 * 1. Blocks requests made via JavaScript immediately with a 403 Forbidden response.
	 * 2. If no record exists for the IP address in the login rate limiter table,
	 *    the IP is allowed to attempt login.
	 * 3. If a record exists, it checks:
	 *    - Whether the number of failed attempts has reached or exceeded the maximum allowed.
	 *    - Whether the current time is before the next allowed attempt time.
	 *
	 * @return bool True if the IP address is allowed to attempt login, false otherwise.
	 */
	private function is_login_attempt_allowed(): bool {
		$ip_address = ip_address();

		// Block JavaScript-based requests immediately
		$is_javascript_request = $this->is_javascript_request();
		if ($is_javascript_request === true) {
			http_response_code(403);
			die(); // Login attempts via JavaScript are not allowed.
		}

		// Retrieve login rate limiter record for this IP
		$record_obj = $this->db->get_one_where('ip_address', $ip_address, $this->table_name);
		if ($record_obj === false) {
			return true; // No previous attempts recorded
		}

		$num_failed_attempts = (int) $record_obj->num_failed_attempts;
		$next_attempt_allowed = (int) $record_obj->next_attempt_allowed;

		// Block if max attempts exceeded or timeout not yet passed
		if ($num_failed_attempts >= $this->max_num_attempts || $next_attempt_allowed > time()) {
			return false;
		}

		return true;
	}

	/**
	 * Block a login attempt due to exceeding allowed attempts or timeout.
	 *
	 * This method performs the following actions:
	 * 1. If the request is made via JavaScript, it immediately responds
	 *    with HTTP 429 Too Many Requests and terminates execution.
	 * 2. Otherwise, it redirects the user to the configured timeout URL.
	 *
	 * @return void
	 */
	private function block_login_attempt(): void {
		$is_javascript_request = $this->is_javascript_request();
		if ($is_javascript_request === true) {
			http_response_code(429); // Too Many Requests.
			die();
		}

		redirect($this->timeout_url);
	}

	/**
	 * Clear expired or specific login rate limiter records.
	 *
	 * This method performs the following actions:
	 * 1. Deletes the rate limiter record for the current IP address.
	 * 2. Deletes any records where the next allowed login attempt time has passed.
	 *
	 * @return void
	 */
	private function clear_login_rate_limiter(): void {
		$params = [
			'ip_address' => ip_address(),
			'nowtime'    => time()
		];

		$sql = 'DELETE FROM '.$this->table_name.' WHERE ip_address = :ip_address OR next_attempt_allowed < :nowtime';
		$this->db->query_bind($sql, $params);
		$this->reset_table_id_if_empty();
	}

	/**
	 * Clean up old login rate limiter records.
	 *
	 * This method removes records where:
	 * 1. The next allowed login attempt time has already passed, AND
	 * 2. The number of failed attempts has reached or exceeded the maximum allowed.
	 *
	 * @return void
	 */
	public function clean_up_table(): void {
		$params = [
			'nowtime' => time(),
			'max_attempts' => $this->max_num_attempts
		];

		$sql = 'DELETE FROM '.$this->table_name.'
		        WHERE next_attempt_allowed < :nowtime
		          AND num_failed_attempts >= :max_attempts';
		$this->db->query_bind($sql, $params);
		$this->reset_table_id_if_empty();
	}

	/**
	 * Reset the auto-increment ID of the login_rate_limiter table.
	 *
	 * This method checks if the table is empty, and if so, resets
	 * the auto-increment value back to 1. Useful for cleanup after
	 * all records have been removed.
	 *
	 * @return void
	 */
	private function reset_table_id_if_empty(): void {
		// Check if there are any rows in the table
		$row_count = $this->db->count($this->table_name);

		if ($row_count === 0) {
			// Table is empty, reset auto-increment
			$sql = 'ALTER TABLE '.$this->table_name.' AUTO_INCREMENT = 1';
			$this->db->query($sql);
		}
	}

    /**
    * Determine whether the current request is being made by JavaScript.
    * Checks for Trongate MX, traditional AJAX (X-Requested-With), and JSON requests (fetch/Axios).
    * Note: Headers can be spoofed → this is not a security boundary.
    * @return bool
    */
    private function is_javascript_request(): bool {
        if (from_trongate_mx()) {
            return true;
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            return true;
        }

        if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            return true;
        }

        return false;
    }

}