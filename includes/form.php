<?php // Form 

class Form {
    private $session;
    private $values;
    private $errors;
    private $numErrors;

    public function __construct($session) {
        $this->session = $session;

        // Initialize values and errors from session, if available
        $this->values = $this->session->get('form-values') ?? [];
        $this->errors = $this->session->get('form-errors') ?? [];
        $this->numErrors = $this->session->get('form-num-errors') ?? 0;
    }

    // Records value typed by user
    public function setValue($field, $value) {
        $this->values[$field] = $value;
        $this->session->set('form-values', $this->values);
    }

    public function getValue($field) {
        return isset($this->values[$field]) ? htmlspecialchars($this->values[$field]) : null;
    }

    // Records form error
    public function setError($field, $errmsg) {
        $this->errors[$field] = $errmsg;
        $this->numErrors = count($this->errors);
        $this->session->set('form-errors', $this->errors);
        $this->session->set('form-num-errors', $this->numErrors);
    }

    public function getError($field) {
        return isset($this->errors[$field]) ? $this->errors[$field] : null;
    }

    public function hasError($field) {
        return isset($this->errors[$field]);
    }

    public function getErrors() {
        // Retrieve errors from the session if not set in the current instance
        if (empty($this->errors)) {
            $this->errors = $this->session->get('form-errors') ?? [];
        }
        return $this->errors;
    }

    public function clearValues() {
        $this->values = [];
        $this->session->set('form-values', $this->errors);
    }

    public function clearErrors() {
        $this->errors = [];
        $this->session->set('form-errors', $this->errors);
        $this->session->set('form-num-errors', 0);
    }    

    public function hasData() {
        return !empty($this->values);
    }
}

$form = new Form($session);
