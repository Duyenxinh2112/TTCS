<?php
    namespace app\controllers;
    use app\core\Controller;
    use app\models\User;
    use BaseController;
    use Model;

    class statisticalController extends Controller{
        function __construct()
        {
            parent::__construct();
        }
        public function index(){
           $this->render('view_admin/statistical');
        }
        
    }
?>