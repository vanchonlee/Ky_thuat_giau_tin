<?php
	ini_set('max_execution_time', 300);
	ini_set('memory_limit', '-1');
	error_reporting(0);
	date_default_timezone_set('asia/ho_chi_minh');
	if (!isset($_SESSION)) session_start();
	include "connectdb.php";

	class WavFile{
		private static $HEADER_LENGTH = 44;

		public static function ReadFile($filename) {
            $filesize = filesize($filename);
            if ($filesize<self::$HEADER_LENGTH)
                return false;           
            $handle = fopen($filename, 'rb');
            $wav = array(
                'header'    => array(
                    'chunkid'       => self::readString($handle, 4),
                    'chunksize'     => self::readLong($handle),
                    'format'        => self::readString($handle, 4)
                    ),
                'subchunk1' => array(
                    'id'            => self::readString($handle, 4),
                    'size'          => self::readLong($handle),
                    'audioformat'   => self::readWord($handle),
                    'numchannels'   => self::readWord($handle),
                    'samplerate'    => self::readLong($handle),
                    'byterate'      => self::readLong($handle),
                    'blockalign'    => self::readWord($handle),
                    'bitspersample' => self::readWord($handle)
                    ),
                'subchunk2' => array( //INFO chunk is optional, but I need it for this project's audio file
                    'id'            => self::readString($handle, 4),
                    'size'			=> self::readLong($handle),
                    'data'          => null
                    ),
                'subchunk3' => array(
                	'id'			=> null,
                	'size'			=> null,
                    'data'          => null
                    )
                );
            $wav['subchunk2']['data'] = fread($handle, $wav['subchunk2']['size']);
            $wav['subchunk3']['id'] = self::readString($handle, 4);
            $wav['subchunk3']['size'] = self::readLong($handle);
			$wav['subchunk3']['data'] = fread($handle, $wav['subchunk3']['size']);
            fclose($handle);
            return $wav;
	    }
	    public static function WriteFile($wav, $filename) {
            $handle = fopen($filename, 'wb');
            self::writeString($handle, $wav['header']['chunkid']);
            self::writeLong($handle, $wav['header']['chunksize']);
            self::writeString($handle, $wav['header']['format']);
            self::writeString($handle, $wav['subchunk1']['id']);
            self::writeLong($handle, $wav['subchunk1']['size']);
            self::writeWord($handle, $wav['subchunk1']['audioformat']);
            self::writeWord($handle, $wav['subchunk1']['numchannels']);
            self::writeLong($handle, $wav['subchunk1']['samplerate']);
            self::writeLong($handle, $wav['subchunk1']['byterate']);
            self::writeWord($handle, $wav['subchunk1']['blockalign']);
            self::writeWord($handle, $wav['subchunk1']['bitspersample']);
            self::writeString($handle, $wav['subchunk2']['id']);
            self::writeLong($handle, $wav['subchunk2']['size']);
            fwrite($handle, $wav['subchunk2']['data']);
            self::writeString($handle, $wav['subchunk3']['id']);
            self::writeLong($handle, $wav['subchunk3']['size']);
            fwrite($handle, $wav['subchunk3']['data']);
            fclose($handle);
	    }
	    
	    private static function readString($handle, $length) {
	        return self::readUnpacked($handle, 'a*', $length);
	    }

	    private static function readLong($handle) {
	        return self::readUnpacked($handle, 'V', 4);
	    }

	    private static function readWord($handle) {
	        return self::readUnpacked($handle, 'v', 2);
	    }

	    private static function readUnpacked($handle, $type, $length) {
	        $r = unpack($type, fread($handle, $length));
	        return array_pop($r);
	    }

	    private static function writeString($handle, $string) {
	        self::writePacked($handle, 'a*', $string);
	    }

	    private static function writeLong($handle, $string) {
	        self::writePacked($handle, 'V', $string);
	    }

	    private static function writeWord($handle, $string) {
	        self::writePacked($handle, 'v', $string);
	    }

	    private static function writePacked($handle, $type, $string) {
	        fwrite($handle, pack($type, $string));
	    }
	}

	if (isset($_SESSION['user'])){
		require_once 'google-api-php-client-2.2.1/vendor/autoload.php';
		$client = new Google_Client();
		putenv('GOOGLE_APPLICATION_CREDENTIALS=google-api-php-client-2.2.1/service_account_keys.json');
		$client = new Google_Client();
		$client->addScope(Google_Service_Drive::DRIVE);
		$client->useApplicationDefaultCredentials();
		$service = new Google_Service_Drive($client);

		// Download audio file from Google Drive

		$fileId = $_POST["buysongid"];  // Google File ID
		$qr = $conn->prepare("select song, singer from multimedia where id = :id limit 1;");
		$qr->bindParam(":id", $fileId, PDO::PARAM_STR);
		$qr->execute();
		$rs_songandsinger = $qr->fetch();
		$fileName = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $rs_songandsinger['singer'] . ' - ' . $rs_songandsinger['song'] . ' - ' . bin2hex(random_bytes(16)) . '.wav');
		$content = $service->files->get($fileId, array("alt" => "media"));
		if (!is_dir("music/")){
    		mkdir("music/", 0777);
		}
		$outHandle = fopen("music/" . $fileName, "w+");
		while (!$content->getBody()->eof()) {
		    fwrite($outHandle, $content->getBody()->read(1024));
		}
		fclose($outHandle);

		if (file_exists("music/" . $fileName)){ //If audio file is existed

		//Read audio file
			
			$wavFile = new WavFile;
			$tmp = $wavFile->ReadFile("music/" . $fileName);
			unlink("music/" . $fileName);

			//Get binary code of signature

			function TexttoBin($text){
				$bin = "";
			    for($i = 0; $i < strlen($text); $i++)
			    	$bin .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
			    return $bin;
			}

			//Create signature

			$signature = TexttoBin(str_pad(strlen($_SESSION['user']), 10, '0', STR_PAD_LEFT) . $_SESSION['user']);

			//Change bit

			$subchunk3data = unpack("H*", $tmp['subchunk3']['data']);
			if (strlen($subchunk3data[1]) >= strlen($signature)){
				for($i = 0; $i < strlen($signature); $i++){
					$newhex = str_pad(dechex(bindec(substr_replace(str_pad(hex2bin(substr($subchunk3data[1], $i*2, 2)), 8, '0', STR_PAD_LEFT), substr($signature, $i, 1), 7, 1))), 2, '0', STR_PAD_LEFT);
					$subchunk3data[1] = substr_replace($subchunk3data[1], $newhex, $i*2, 2);
				}

				$tmp['subchunk3']['data'] = pack("H*", $subchunk3data[1]);

				//Write new audio file
		
				$newFileName = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $rs_songandsinger['singer'] . ' - ' . $rs_songandsinger['song'] . ' - ' . $_SESSION['user'] . '.wav');
				$wavFile->WriteFile($tmp, "music/" . $newFileName);

				if (file_exists("music/" . $newFileName)){

					// Upload audio file to Google Drive

					$newContent = file_get_contents("music/" . $newFileName);
					$fileMetadata = new Google_Service_Drive_DriveFile(array('name' => $newFileName));
					$file = $service->files->create($fileMetadata, array(
					    'data' => $newContent,
					    'mimeType' => 'audio/wav',
					    'uploadType' => 'multipart',
					    'fields' => 'id'));
					$newFileId = $file->id;
					unlink("music/" . $newFileName);

					//Share file to anyone

					$service->getClient()->setUseBatch(true);
					$batch = $service->createBatch();
					$newFilePermission = new Google_Service_Drive_Permission(array(
				    	'type' => 'anyone',
				    	'role' => 'reader',
					));
				    $request = $service->permissions->create($newFileId, $newFilePermission, array('fields' => 'id'));
				    $batch->add($request, 'anyone');
				    $results = $batch->execute();
					$service->getClient()->setUseBatch(false);
					$newFileUrl = "https://drive.google.com/file/d/" . $newFileId . "/view?usp=sharing";
					
					// Record license to Database

					$qr = $conn->prepare("insert into multimedia (id, parentid, song, singer, url, type, owner) values (:id, :parentid, :song, :singer, :url, 'music', :owner);");
					$qr->bindParam(":id", $newFileId, PDO::PARAM_STR);
					$qr->bindParam(":parentid", $fileId, PDO::PARAM_STR);
					$qr->bindParam(":song", $rs_songandsinger['song'], PDO::PARAM_STR);
					$qr->bindParam(":singer", $rs_songandsinger['singer'], PDO::PARAM_STR);
					$qr->bindParam(":url", $newFileUrl, PDO::PARAM_STR);
					$qr->bindParam(":owner", $_SESSION['user'], PDO::PARAM_STR);
					$qr->execute();
					echo "buy success";
				}
				else
					echo "buy failure";
			}
			else
				echo "buy failure";
		}
		else
			echo "buy failure";
	}
	else
		echo "buy failure";
?>