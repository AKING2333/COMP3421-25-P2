<?php
require_once(dirname(__FILE__) . '/ServerError.php');
class View {
    private $viewName, $pageTitle, $viewVars;

    function __construct($viewName, $pageTitle="") {
        $this->viewName=$viewName;
        $this->pageTitle=$pageTitle;
        $this->viewVars=array();

        if(!is_file(dirname(__FILE__) . '/../views/' . $this->viewName . '.view.php')) {
            ServerError::throwError(500, 'Unable to load view: ' . $viewName);
        }

    }

    function render($vars = array()) {
        $currentView = $this;
        $pageTitle=$this->pageTitle;

        extract($vars);
        include(dirname(__FILE__) . '/../views/' . $this->viewName . '.view.php');
    }

    function addVar($varName, $varValue) {
        $this->viewVars[(string)$varName]=$varValue;
    }

    function getViewName() {
        return $this->viewName;
    }
}
?>