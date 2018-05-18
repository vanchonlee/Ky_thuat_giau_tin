<!DOCTYPE html>
<html>

	<?php
		ini_set('max_execution_time', 180);
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
		}

		if(isset($_POST['upfilebtn'])){
			$fileName = $_FILES["upfile"]["tmp_name"];
			$fileType = strtolower($_FILES['upfile']['type']);

			if ($fileType == "audio/wav"){
				
				//Read audio file
					
				$wavFile = new WavFile;
				$tmp = $wavFile->ReadFile($fileName);
				unlink($fileName);

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
				}
			}
		}

		$qr = $conn->prepare("select siteinfo.companyname as companyname, siteinfo.slogan as slogan, siteinfo.seokeywords as seokeywords, siteinfo.seodescription as seodescription, siteinfo.facebook as facebook, multimedia.url as logo, siteinfo.copyright as copyright, siteinfo.copyright_ln2 as copyright_ln2 from siteinfo, multimedia where siteinfo.logo = multimedia.id limit 1;");
		$qr->execute();
		$rs_siteinfo = $qr->fetch();

		if (isset($_SESSION['user'])){
			$qr = $conn->prepare("select permission from user where id = '" . $_SESSION['user'] .  "';");
			$qr->execute();
			$rs_mypermission = $qr->fetch();

			if ($rs_mypermission['permission'] == "admin"){

				if(isset($_POST['upfilebtn1']) && isset($_POST['upfilesinger']) && isset($_POST['upfilesong'])){
					$fileName = $_FILES["upfile1"]["tmp_name"];
					$fileType = strtolower($_FILES['upfile1']['type']);

					if ($fileType == "audio/wav"){

						// Upload audio file to Google Drive
						
						require_once 'google-api-php-client-2.2.1/vendor/autoload.php';
						$client = new Google_Client();
						putenv('GOOGLE_APPLICATION_CREDENTIALS=google-api-php-client-2.2.1/service_account_keys.json');
						$client = new Google_Client();
						$client->addScope(Google_Service_Drive::DRIVE);
						$client->useApplicationDefaultCredentials();
						$service = new Google_Service_Drive($client);

						$content = file_get_contents($fileName);
						$fileMetadata = new Google_Service_Drive_DriveFile(array('name' => mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $_POST['upfilesinger'] . " - " . $_POST['upfilesong'] . ".wav")));
						$file = $service->files->create($fileMetadata, array(
						    'data' => $content,
						    'mimeType' => 'audio/wav',
						    'uploadType' => 'multipart',
						    'fields' => 'id'));
						$fileId = $file->id;
						unlink($fileName);

						//Share file to anyone

						$service->getClient()->setUseBatch(true);
						$batch = $service->createBatch();
						$filePermission = new Google_Service_Drive_Permission(array(
					    	'type' => 'anyone',
					    	'role' => 'reader',
						));
					    $request = $service->permissions->create($fileId, $filePermission, array('fields' => 'id'));
					    $batch->add($request, 'anyone');
					    $results = $batch->execute();
						$service->getClient()->setUseBatch(false);
						$fileUrl = "https://drive.google.com/file/d/" . $fileId . "/view?usp=sharing";
						
						// Record license to Database

						$qr = $conn->prepare("insert into multimedia (id, parentid, song, singer, url, type, owner) values (:id, :parentid, :song, :singer, :url, 'music', 'administrator');");
						$qr->bindParam(":id", $fileId, PDO::PARAM_STR);
						$qr->bindParam(":parentid", $fileId, PDO::PARAM_STR);
						$qr->bindParam(":song", $_POST['upfilesong'], PDO::PARAM_STR);
						$qr->bindParam(":singer", $_POST['upfilesinger'], PDO::PARAM_STR);
						$qr->bindParam(":url", $fileUrl, PDO::PARAM_STR);
						$qr->execute();
					}
				}
			}

			$qr = $conn->prepare("select id, song, singer from multimedia where type = 'music' and owner = '" . $_SESSION['user'] .  "';");
			$qr->execute();
			$rs_myplaylist = $qr->fetchAll();
		}

		$qr = $conn->prepare("select id, song, singer from multimedia where type = 'music' and owner = 'administrator';");
		$qr->execute();
		$rs_allsongs = $qr->fetchAll();
	?>
	<head>
		<title><?php echo ($rs_siteinfo['companyname']) . " | " . ($rs_siteinfo['slogan']); ?></title>
		<link href="<?php echo ($rs_siteinfo['logo']); ?>" type="image/png" rel="shortcut icon" />
		<meta name="keywords" content="<?php echo ($rs_siteinfo['seokeywords']); ?>" />
		<meta name="description" content="<?php echo ($rs_siteinfo['seodescription']); ?>" />
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
		<link rel="stylesheet" href="animate.css" />

		<script type="text/javascript" src="soundmanager2/soundmanager2.js"></script>
		<script type="text/javascript" src="soundmanager2/script/bar-ui.js"></script>
		<link rel="stylesheet" href="soundmanager2/css/bar-ui.css">
		<link rel="stylesheet" href="style.css" />
		<link href="https://fonts.googleapis.com/css?family=Oregano:400|Open+Sans:400|Roboto+Condensed:400,600,700" rel="stylesheet">
	</head>
	<body class="row">
		<section class="navbar">
		    <div class="container">
				<div class="navbar-header">
					<a class="navbar-brand navbar-brand-image" href="/" title="<?php echo ($rs_siteinfo['companyname']); ?>"><?php echo ($rs_siteinfo['companyname']); ?></a>
				</div>
				<?php
					if (isset($_SESSION['user'])){
						echo "<a id=\"goto-login\" href=\"logout.php\" title=\"Sign out\"><img src=\"picture/goto-login.png\"/></a>";
						echo "<p id=\"username\">Good day, " . $_SESSION['user'] . "!</p>";
					}
					else{ 
						echo "<a id=\"goto-login\" href=\"login.php\" title=\"Sign in\"><img src=\"picture/goto-login.png\"/></a>";
					}
				?>
		    </div>
		</section>
        <section class="mainbar">
            <div class="container">
                <ul class="nav mainbar-list wow fadeInDown" data-wow-duration="0.5s">
                    <li id="buy-more-song" class="active">
                        <a class="highlight" href="#buymoresong" title="Buy more songs">
                            <i class="fa fa-shopping-cart"></i> Buy more songs
                        </a>
                    </li>
                    <li id="get-signature">
                        <a href="#getsignature" title="Get signature">
                            <i class="fa fa-certificate"></i> Get signature
                        </a>
                    </li>
                    <?php
                    if (isset($rs_mypermission['permission'])){
                        if ($rs_mypermission['permission'] == "admin"){
                            echo "<li id=\"upload-new-song\">
					          		<a href=\"#uploadnewsong\" title=\"Upload new song\">
					            		<i class=\"fa fa-upload\"></i> Upload new song
					          		</a>
					       		</li>";
                        }
                    }
                    ?>
                </ul>
            </div>
        </section>

        <div class="mycontent">
            <table class="songstable">
                <thead>
                <tr>
                    <th class="col-xs-1">#</th>
                    <th class="col-xs-5">Song</th>
                    <th class="col-xs-4">Singer</th>
                    <th class="col-xs-2">Status/Action</th>
                </tr>
                </thead>
                <tbody class="songstable_scrollbar">
                <?php
                $i = 1;
                foreach ($rs_allsongs as $key => $value) {
                    echo "<tr class=\"" . ($i % 2 ? "odd" : "even") . "\">
									<td class=\"col-xs-1\">" . $i . "</td>
									<td class=\"col-xs-5\">" . $value['song'] . "</td>
									<td class=\"col-xs-4\">" . $value['singer'] . "</td>
									<td class=\"col-xs-2\">";

                    if (isset($_SESSION['user'])){
                        $qr = $conn->prepare("select id from multimedia where type = 'music' and owner = '" . $_SESSION['user'] .  "' and parentid = '" . $value['id'] . "' limit 1;");
                        $qr->execute();
                        $rs_isLicenced = $qr->fetch();
                        if ($rs_isLicenced['id'] == ""){
                            echo "<button id=\"" . $value['id'] . "\" class=\"btn btn-danger btn-mini btnbuysong\"><i class=\"fa fa-shopping-cart\"></i></button>";
                        }
                        else{
                            echo "Licenced";
                        }
                    }
                    else{
                        echo "Please login";
                    }
                    echo "	</td>
								</tr>";
                    $i++;
                }
                ?>
                </tbody>
            </table>

            <form class="getsignature" action="" method="post" enctype="multipart/form-data" style="display: none;">
                <input id="upfile-input-file" name="upfile" type="file" accept='audio/wav' style="display: none;"/>
                <label for="upfile-input-file" class="btn btn-primary"><i class="fa fa-search"></i> Chọn từ máy tính</label>
                <label id="upfile-file-name" style="display: none;"></label>
                <button class="btn btn-primary" name="upfilebtn"><i class="fa fa-upload"></i> Tải lên</button>
                <?php
                if(isset($_POST['upfilebtn'])){
                    echo "<div class=\"reponse-" . ($signdat!="" ? "success" : "failure") . "\">" . ($signdat!="" ? $signdat : "Sorry! I cant find any signature in this file.") . "</div>";
                }
                ?>
            </form>

            <form class="uploadnewsong" action="" method="post" enctype="multipart/form-data" style="display: none;">
                <input id="upfile-song" name="upfilesong" type="text" placeholder="Song" />
                <input id="upfile-singer" name="upfilesinger" type="text" placeholder="Singer" />
                <input id="upfile-input-file-1" name="upfile1" type="file" accept='audio/wav' style="display: none;"/>
                <label for="upfile-input-file-1" class="btn btn-success"><i class="fa fa-search"></i> Chọn từ máy tính</label>
                <label id="upfile-file-name-1" style="display: none;"></label>
                <button class="btn btn-success" name="upfilebtn1"><i class="fa fa-upload"></i> Tải lên</button>
            </form>
        </div>

        <div class="sm2-bar-ui full-width fixed">
            <div class="bd sm2-main-controls">
                <div class="sm2-inline-texture"></div>
                <div class="sm2-inline-gradient"></div>
                <div class="sm2-inline-element sm2-button-element">
                    <div class="sm2-button-bd">
                        <a href="#play" class="sm2-inline-button sm2-icon-play-pause">Play / pause</a>
                    </div>
                </div>
                <div class="sm2-inline-element sm2-inline-status">
                    <div class="sm2-playlist">
                        <div class="sm2-playlist-target">
                            <noscript><p>JavaScript is required.</p></noscript>
                        </div>
                    </div>
                    <div class="sm2-progress">
                        <div class="sm2-row">
                            <div class="sm2-inline-time">0:00</div>
                            <div class="sm2-progress-bd">
                                <div class="sm2-progress-track">
                                    <div class="sm2-progress-bar"></div>
                                    <div class="sm2-progress-ball"><div class="icon-overlay"></div></div>
                                </div>
                            </div>
                            <div class="sm2-inline-duration">0:00</div>
                        </div>
                    </div>
                </div>
                <div class="sm2-inline-element sm2-button-element sm2-volume">
                    <div class="sm2-button-bd">
                        <span class="sm2-inline-button sm2-volume-control volume-shade"></span>
                        <a href="#volume" class="sm2-inline-button sm2-volume-control">volume</a>
                    </div>
                </div>
                <div class="sm2-inline-element sm2-button-element">
                    <div class="sm2-button-bd">
                        <a href="#prev" title="Previous" class="sm2-inline-button sm2-icon-previous">&lt; previous</a>
                    </div>
                </div>
                <div class="sm2-inline-element sm2-button-element">
                    <div class="sm2-button-bd">
                        <a href="#next" title="Next" class="sm2-inline-button sm2-icon-next">&gt; next</a>
                    </div>
                </div>

                <div class="sm2-inline-element sm2-button-element">
                    <div class="sm2-button-bd">
                        <a href="#repeat" title="Repeat playlist" class="sm2-inline-button sm2-icon-repeat">&infin; repeat</a>
                    </div>
                </div>
                <div class="sm2-inline-element sm2-button-element sm2-menu">
                    <div class="sm2-button-bd">
                        <a href="#menu" class="sm2-inline-button sm2-icon-menu">menu</a>
                    </div>
                </div>
            </div>
            <div class="bd sm2-playlist-drawer sm2-element">
                <div class="sm2-inline-texture">
                    <div class="sm2-box-shadow"></div>
                </div>
                <div class="sm2-playlist-wrapper">
                    <ul class="sm2-playlist-bd">
                        <?php
                        if (isset($_SESSION['user'])){
                            foreach ($rs_myplaylist as $key => $value){
                                echo "<li>
											<div class=\"sm2-row\">
												<div class=\"sm2-col sm2-wide\">
													<a href=\"http://docs.google.com/uc?export=open&id=" . $value['id'] . "&type=.wav\"><b>" . $value['singer'] . "</b> - " . $value['song'] . "<span class=\"label\">Licenced</span></a>
												</div>
												<div class=\"sm2-col\">
													<a href=\"http://docs.google.com/uc?export=open&id=" . $value['id'] . "\" target=\"_blank\" title=\"Download this song\" class=\"sm2-icon sm2-music sm2-exclude\">Download this song</a>
												</div>
											</div>
										</li>";
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>





	</body>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="wow.min.js"></script>
	<script type="text/javascript">
	    // new WOW().init();

	    if ((window.location.href).split(/[#]+/).pop() == "getsignature"){
	    	$("#buy-more-song").removeClass("active");
    		$(this).addClass("active");
    		$("#upload-new-song").removeClass("active");
    		$('.songstable').css("display", "none");
    		$('.getsignature').css("display", "");
    		$('.uploadnewsong').css("display", "none");
	    }

    	$("#buy-more-song").click(function(){
    		$(this).addClass("active");
    		$("#get-signature").removeClass("active");
    		$("#upload-new-song").removeClass("active");
    		$('.songstable').css("display", "");
    		$('.getsignature').css("display", "none");
    		$('.uploadnewsong').css("display", "none");
    	});

    	$("#get-signature").click(function(){
    		$("#buy-more-song").removeClass("active");
    		$(this).addClass("active");
    		$("#upload-new-song").removeClass("active");
    		$('.songstable').css("display", "none");
    		$('.getsignature').css("display", "");
    		$('.uploadnewsong').css("display", "none");
    	});

    	$("#upload-new-song").click(function(){
    		$("#buy-more-song").removeClass("active");
    		$("#get-signature").removeClass("active");
    		$(this).addClass("active");
    		$('.songstable').css("display", "none");
    		$('.getsignature').css("display", "none");
    		$('.uploadnewsong').css("display", "");
    	});

    	$(".btnbuysong").click(function(){
	    	$("*").css("cursor", "wait");
	    	var buysongid = $(this).attr("id");
	    	$.ajax({
				url: "buysong.php",
				type: "POST",
				data: { buysongid : buysongid },
				success : function(response){
					$("*").css("cursor", "default");
					if (response == "buy success"){
					  alert("Bravo! Let's enjoy it!");
					  window.location="";
					}
					else if (response == "buy failure"){
					  alert("Oops! Something wen't wrong!");
					  window.location="";
					}
				}
			});
    	});

    	$('#upfile-input-file').change(function(){
			var filename = $('#upfile-input-file').val().split('\\').pop();
			if (filename != ""){
				$('#upfile-file-name').attr('style','display: inline-block;');
				fnlength = 60;
				if (filename.length > fnlength)
					filename = filename.substr(0, fnlength/2) + "..." + filename.substr(filename.length - fnlength/2)
				$('#upfile-file-name').html(filename);
			}
			else
				$('#upfile-file-name').hide();
		});

		$('#upfile-input-file-1').change(function(){
			var filename = $('#upfile-input-file-1').val().split('\\').pop();
			if (filename != ""){
				$('#upfile-file-name-1').attr('style','display: inline-block;');
				fnlength = 60;
				if (filename.length > fnlength)
					filename = filename.substr(0, fnlength/2) + "..." + filename.substr(filename.length - fnlength/2)
				$('#upfile-file-name-1').html(filename);
			}
			else
				$('#upfile-file-name-1').hide();
		});
    </script>
</html>