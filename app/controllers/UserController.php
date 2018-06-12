<?php

class UserController extends ControllerBase
{

    private function _registerSession($user)
    {
        $this->session->set(
            "auth",
            [
                "id"   => $user->id,
                "email" => $user->email,
            ]
        );
    }

    public function loadinfoAction(){
        $this->view->disable();
        $email = $this->session->get('auth');
        print_r(json_encode($email));
    }

    public function indexAction()
    {

    }

    public function logoutAction(){
        $this->session->destroy('auth');
        $this->dispatcher->forward([
            "controller"=>"index",
            "action"=>"index"
        ]);
    }

    public function loginAction(){
        $message = '';
        if ($this->request->isPost()){
            $email = $this->request->getPost("email");
            $password = $this->request->getPost("password");
            //check user
            $user = User::count(array(
                "email = :email:",
                "bind" => array(
                    "email"=>$email
                )
            ));

            if ($user===0){
                //user khong ton tai
                $message = 'user khong ton tai';
            }else{
                //check password and email
                $user_check = User::count(array(
                    "email = :email: AND password = :password:",
                    "bind" => array(
                        "email"=>$email,
                        "password"=>$password
                    )
                ));
                if ($user_check===0){
                    //email and password khong khop
                    $message="password hoac email sai roi";
                }else{
                    $message="dang nhap thanh cong";
                    //email and password khop
                    //set session
                    $user1 = User::findFirst(array(
                        "email = :email:",
                        "bind" => array(
                            "email"=>$email
                        )
                    ));
                    $this->_registerSession($user1);
                }
            }
        }
        $this->view->message=$message;
    }


    public function signAction(){
        $message = '';
        if ($this->request->isPost()){
            // Get the data from the user
            $email    = $this->request->getPost("email");
            $password = $this->request->getPost("password");

            //check user
            $user = User::count(array(
                "email = :email:",
                "bind" => array(
                    "email"=>$email
                )
            ));

            //khong tim thay user
            if ($user === 0){
                //dang ky
                $user = new User();
                $check = $user->save([
                   "email"    =>  $email,
                   "password" =>  $password
                ]);
                if ($check!==false){
                    $this->_registerSession($user);
                    $message = "dang ky thanh cong";
                }
            }else{
                $message = "tai khoan da ton tai";
            }
        }
        $this->view->message = $message;
    }
}

