<?php


class AdminController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $test = Audio::find();
        $this->view->data = $test;
    }
    //config google drive api
    public function uploadGoogleDrive($path,$name){
        require_once __DIR__.'/../library/google-api-php-client-2.2.1/vendor/autoload.php';
        $client = new Google_Client();
        $client_secretjson = __DIR__.'/../library/google-api-php-client-2.2.1/client_secrets2.json';
        $client->setAuthConfig($client_secretjson);
        $client->addScope(Google_Service_Drive::DRIVE);
        $client->useApplicationDefaultCredentials();
        $client->setHttpClient(new \GuzzleHttp\Client([
            'verify' => __DIR__.'/../library/cacert.pem',
        ]));
        $service = new Google_Service_Drive($client);
        $content = file_get_contents($path);
        $fileMetadata = new Google_Service_Drive_DriveFile(
            array(
                'name' => $name)
        );
        $file = $service->files->create($fileMetadata, array(
            'data' => $content,
            'mimeType' => 'audio/wav',
            'uploadType' => 'multipart',
            'fields' => 'id')
        );

        $fileId = $file->id;
        //
        //Share file to anyone

        $service->getClient()->setUseBatch(true);
        $batch = $service->createBatch();
        $filePermission = new Google_Service_Drive_Permission(array(
            'type' => 'anyone',
            'role' => 'reader',
        ));
        $request = $service->permissions->create($fileId, $filePermission, array('fields' => 'id'));
        $batch->add($request, 'anyone');
        $batch->execute();
        $service->getClient()->setUseBatch(false);
        $fileUrl = "https://drive.google.com/file/d/" . $fileId . "/view?usp=sharing";

        return array(
            'link'=>$fileUrl,
            'id'=>$fileId
        );
    }

    //upload media to google drive
    public function uploadAction(){
        set_time_limit(500);
        ini_set('memory_limit', '-1');
        if($this->request->hasFiles() && $this->request->isPost()){
            $uploads = $this->request->getUploadedFiles();
            foreach ($uploads as $upload){
                $wavFile = new WavFile;
                $tmp = $wavFile->ReadFile($upload->getTempName());

                //Get binary code of signature

                function TexttoBin($text){
                    $bin = "";
                    for($i = 0; $i < strlen($text); $i++)
                        $bin .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
                    return $bin;
                }

                //Create signature

                $signature = TexttoBin(str_pad(strlen('levanchon'), 10, '0', STR_PAD_LEFT) . 'levanchon');

                //Change bit

                $subchunk3data = unpack("H*", $tmp['subchunk3']['data']);

                if (strlen($subchunk3data[1]) >= strlen($signature)){
                    for($i = 0; $i < strlen($signature); $i++){
                        $newhex = str_pad(dechex(bindec(substr_replace(str_pad(hex2bin(substr($subchunk3data[1], $i*2, 2)), 8, '0', STR_PAD_LEFT), substr($signature, $i, 1), 7, 1))), 2, '0', STR_PAD_LEFT);
                        $subchunk3data[1] = substr_replace($subchunk3data[1], $newhex, $i*2, 2);
                    }

                    $tmp['subchunk3']['data'] = pack("H*", $subchunk3data[1]);

                    //Write new audio file
                    $wavFile->WriteFile($tmp, "music/" . $upload->getName());

                }

                $idfile = $this->uploadGoogleDrive("music/" . $upload->getName(),$upload->getName());
                unlink("music/" . $upload->getName());

//                 save info audio
                $audioTemp = new Audio();
                $audioTemp->id          =   $idfile['id'];
                $audioTemp->type        =   $upload->getType();
                $audioTemp->name        =   $upload->getName();
                $audioTemp->url         =   $idfile['link'];
                $audioTemp->signature   =   "levanchon";
                $audioTemp->save();
            }
            $this->dispatcher->forward([
                "controller" =>  "admin",
                "action"     =>  "index"
            ]);
        }else{
            print_r($this->request->getUploadedFiles());
            if ($this->request->getUploadedFiles()===true){
                print_r("file is uploaded");
            }else{
                print_r("file wav <= 50Mb");
            }
            die("You must choose at least one file to send, Please try again!");
        }
    }

    public function actionAction(){
        if ($this->request->has('btn_delete')){
            //delete in google drive
            require_once __DIR__.'/../library/google-api-php-client-2.2.1/vendor/autoload.php';
            $client = new Google_Client();
            $client_secretjson = __DIR__.'/../library/google-api-php-client-2.2.1/client_secrets2.json';
            $client->setAuthConfig($client_secretjson);
            $client->addScope(Google_Service_Drive::DRIVE);
            $client->useApplicationDefaultCredentials();
            $client->setHttpClient(new \GuzzleHttp\Client([
                'verify' => __DIR__.'/../library/cacert.pem',
            ]));
            $service = new Google_Service_Drive($client);
            $service->files->delete($this->request->get('btn_delete'));
            $audio = new Audio();
            $audio->id = $this->request->get('btn_delete');
            $audio->delete();
        }
        $this->dispatcher->forward([
            "controller" =>  "admin",
            "action"     =>  "index"
        ]);
    }

    public function activeAction(){
        if ($this->request->has('active')){

            $idaudio = $this->request->get('active');
            //add id for user
            $signature = new Signature();
            $signature->user  = $this->session->get('auth')['id'];
            $signature->audio = $idaudio;
            $signature->save();
            $this->dispatcher->forward([
                "controller" =>  "index",
                "action"     =>  "index"
            ]);
        }
    }

}

