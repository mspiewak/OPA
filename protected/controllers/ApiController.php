<?php
class ApiController extends Controller
{

    /**
     * Key which has to be in HTTP USERNAME and PASSWORD headers 
     */
    Const APPLICATION_ID = 'ASCCPE';
 
    private $format = 'json';

    public function filters()
    {
            return array();
    }
 
    // Actions
    public function actionList()
    {
        $model= $this->_getModel();
        $models=$model::model()->findAll();

        if(empty($models)) {
            $this->_sendResponse(200, 
                sprintf('No items where found for model <b>%s</b>', $_GET['model']) );
        } 
        else 
        {
            $rows = array();
            foreach($models as $model)
                $rows[] = $model->attributes;
            $this->_sendResponse(200, CJSON::encode($rows));
        }
    }
    
    public function actionView()
    {
        $model=$this->_getModel();
        $record=$model::model()->findByPk($_GET['id']);

        if(is_null($record))
            $this->_sendResponse(404, 'No Item found with id '.$_GET['id']);
        else
            $this->_sendResponse(200, CJSON::encode($record));
    }
    
    public function actionCreate()
    {
        $model=$this->_getModel();

        $post = file_get_contents("php://input");

        $data = CJSON::decode($post, true);        
        
        foreach($data as $var=>$value) {
            if($model->hasAttribute($var))
                $model->$var = $value;
            else
                $this->_sendResponse(500, 
                    sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>', $var,
                    $_GET['model']) );
        }

        if($model->save())
            $this->_sendResponse(200, CJSON::encode($model));
        else 
        {
            $msg = "<h1>Error</h1>";
            $msg .= sprintf("Couldn't create model <b>%s</b>", $_GET['model']);
            $msg .= "<ul>";
            foreach($model->errors as $attribute=>$attr_errors) {
                $msg .= "<li>Attribute: $attribute</li>";
                $msg .= "<ul>";
                foreach($attr_errors as $attr_error)
                    $msg .= "<li>$attr_error</li>";
                $msg .= "</ul>";
            }
            $msg .= "</ul>";
            $this->_sendResponse(500, $msg );
        }
    }
    
    public function actionUpdate()
    {
        $json = file_get_contents('php://input');
        $put_vars = CJSON::decode($json,true); 

        $model=$this->_getModel();
        $record=$model::model()->findByPk($_GET['id']);  

        if($record === null)
            $this->_sendResponse(400, 
                    sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.",
                    $_GET['model'], $_GET['id']) );

        foreach($put_vars as $var=>$value) {
            if($record->hasAttribute($var))
                $record->$var = $value;
            else {
                $this->_sendResponse(500, 
                    sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>',
                    $var, $_GET['model']) );
            }
        }

        if($record->save())
            $this->_sendResponse(200, CJSON::encode($record));
        else
        {
            $msg = "<h1>Error</h1>";
            $msg .= sprintf("Couldn't update model <b>%s</b>", $_GET['model']);
            $msg .= "<ul>";
            foreach($record->errors as $attribute=>$attr_errors) {
                $msg .= "<li>Attribute: $attribute</li>";
                $msg .= "<ul>";
                foreach($attr_errors as $attr_error)
                    $msg .= "<li>$attr_error</li>";
                $msg .= "</ul>";
            }
            $msg .= "</ul>";
            $this->_sendResponse(500, $msg);
        }
    }
    
    public function actionDelete()
    {
        $model=$this->_getModel();
        $record=$model::model()->findByPk($_GET['id']);

        if($record === null)
            $this->_sendResponse(400, 
                    sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.",
                    $_GET['model'], $_GET['id']) );

        // Delete the model
        $num = $record->delete();
        if($num>0)
            $this->_sendResponse(200, $num);    //this is the only way to work with backbone
        else
            $this->_sendResponse(500, 
                    sprintf("Error: Couldn't delete model <b>%s</b> with ID <b>%s</b>.",
                    $_GET['model'], $_GET['id']) );
    }
    
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);

        header('Content-type: ' . $content_type);

        if($body != '')
        {
            echo $body;
        }

        else
        {
            $message = '';

            switch($status)
            {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            $body = '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
    </head>
    <body>
        <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
        <p>' . $message . '</p>
        <hr />
        <address>' . $signature . '</address>
    </body>
    </html>';

            echo $body;
        }
        Yii::app()->end();
    }
    
    private function _getStatusCodeMessage($status)
    {
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
    
    private function _checkAuth()
    {
        if(!(isset($_SERVER['HTTP_X_USERNAME']) and isset($_SERVER['HTTP_X_PASSWORD']))) {
            $this->_sendResponse(401);
        }
        $username = $_SERVER['HTTP_X_USERNAME'];
        $password = $_SERVER['HTTP_X_PASSWORD'];

        $user=User::model()->find('LOWER(username)=?',array(strtolower($username)));
        if($user===null) {
            $this->_sendResponse(401, 'Error: User Name is invalid');
        } else if(!$user->validatePassword($password)) {
            $this->_sendResponse(401, 'Error: User Password is invalid');
        }
    }
    
    private function _getModel()
    {
        $modelName=ucfirst(substr($_GET['model'],0,-1));
        
        if (!file_exists(Yii::getPathOfAlias('application.models').DIRECTORY_SEPARATOR.$modelName.'.php'))
        {
            $this->_sendResponse(404, 'Page Not Found');
            Yii::app()->end();
        }
        else
            return new $modelName;
    }
}