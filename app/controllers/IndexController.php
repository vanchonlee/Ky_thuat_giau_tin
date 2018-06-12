<?php

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $test = Audio::find();
        $res = array();
        foreach ($test as $data){
            $active = $data->id;
            $check = Signature::count([
                "audio = :audio: AND user = :user:",
                "bind" => array(
                    "audio"=>$active,
                    "user"=>$this->session->get('auth')['id']
                )
            ]);
            if ($check>0){
                //set active
                $data->signature = 'actived';
            }else{
                $data->signature = 'none active';
            }
            $res[] = $data;
        }
        $this->view->data = $res;
    }

}

