<?php



class SignatureController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

    }

    public function getsignatureAction()
    {
        $message = array(
            'message' => '',
            'active'  => false
        );
        set_time_limit(500);
        ini_set('memory_limit', '-1');
        if($this->request->hasFiles() && $this->request->isPost()){
            $uploads = $this->request->getUploadedFiles();
            foreach ($uploads as $upload){
                $wavFile = new WavFile;
                $tmp = $wavFile->ReadFile($upload->getTempName());
                unlink($upload->getTempName());

                //Get binary code of signature

                function BintoText($bin){
                    $text = "";
                    for($i = 0; $i < strlen($bin)/8 ; $i++)
                        $text .= chr(bindec(substr($bin, $i*8, 8)));
                    return $text;
                }

                $subchunk3data = unpack("H*", $tmp['subchunk3']['data']);

                $signature = "";
                for($i = 0; $i < 80; $i++){
                    $signature .= substr(str_pad(base_convert(substr($subchunk3data[1], $i*2, 2), 16, 2), 8, '0', STR_PAD_LEFT), 7, 1);
                }
                $lenofsigndat = BintoText(substr($signature, 0, 80));
                if (is_numeric($lenofsigndat)){
                    for($i = 80; $i < 80+$lenofsigndat*8; $i++){
                        $signature .= substr(str_pad(base_convert(substr($subchunk3data[1], $i*2, 2), 16, 2), 8, '0', STR_PAD_LEFT), 7, 1);
                    }
                    $signdat = BintoText(substr($signature, 80, $lenofsigndat*8));
                    $message['message'] = $signdat;
                    $message['active'] = true;
                }
            }
        }

        $this->view->message = $message;
    }




}

