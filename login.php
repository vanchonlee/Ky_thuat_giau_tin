<!DOCTYPE html>
<html lang="vi">
<?php
//  error_reporting(0);
  date_default_timezone_set('asia/ho_chi_minh');
  if (!isset($_SESSION)) session_start();
  if (isset($_SESSION['user'])){
    unset($_SESSION['guesttoken']);
    header('location: index.php');
  }
  else{
    if (!isset($_SESSION['guesttoken'])){
        try {
            $_SESSION['guesttoken'] = bin2hex(openssl_random_pseudo_bytes(16));
        } catch (Exception $e) {
            print_r($e);
        }
    }
    include "connectdb.php";
    $qr = $conn->prepare("select siteinfo.companyname as companyname, siteinfo.slogan as slogan, siteinfo.seokeywords as seokeywords, siteinfo.seodescription as seodescription, multimedia.url as logo from siteinfo, multimedia where siteinfo.logo = multimedia.id limit 1;");
    $qr->execute();
    $rs_siteinfo = $qr->fetch();
  }
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
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,800" rel="stylesheet">
  <style>
	body { font-family: 'Open Sans', sans-serif;}
	html,body{ height: 100%;}
	.center-of-screen{
	  position: fixed;
	  top: 50%;
	  left: 50%;
	  transform: translate(-50%, -50%);
	}
	.center{
		text-align: center;
	}

	#bg-login{
	  position: absolute; z-index: 10;
	  left: 0px; top: 0px;
	  width: 100%; height: 100%;
	  display: none;
	}
	.panel{ padding: 0px; border: none;}
	#loginbox .panel-heading{ background-color: #50B948;}
	#signupbox .panel-heading{ background-color: #DD5347;}
	.panel-title{ font-size: 20px; font-weight: bold; color: white;}
	.panel-body{ padding-top: 30px !important;}
	.btn{ padding: 5px 10px;}
	.btn i{ margin-right: 5px; }
  </style>
</head> 

<body data-spy="scroll" data-target=".navbar" data-offset="60">
	<div id="opacity-screen">
		<img id="bg-login" src="picture/bg-login.jpg">
            <input type="text" id="guest-token" value="<?php echo $_SESSION['guesttoken']; ?>" hidden="true"></input>
	  	<div id="loginbox" class="mainbox center-of-screen col-lg-4 col-lg-offset-0 col-md-6 col-md-offset-0 col-sm-8 col-sm-offset-0 col-xs-12 col-xs-offset-0">
	    	<div class="panel panel-info">
	      		<div class="panel-heading">
	        		<div class="panel-title">SIGN IN</div>
	      		</div>
	      		<div style="padding-top: 30px" class="panel-body">
	        		<div id="loginform" class="form-horizontal" rule="form">
	          			<div style="margin-bottom: 25px;" class="input-group">
	            			<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            				<input id="login-id" type="text" class="form-control" value="" placeholder="Username" required></input>
	          			</div>
	          			<div style="margin-bottom: 25px;" class="input-group">
	            			<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
	            			<input id="login-pw" type="password" class="form-control" placeholder="Password" required></input>
	          			</div>
	          			<div style="margin-top: 10px" class="form-group">
	            			<div class="center col-md-12 col-xs-12 controls">
	              				<button id="login-btn" class="btn btn-success"><i class="fa fa-sign-in"></i> Sign in</button>
	            			</div>
	          			</div>
	          			<div class="form-group">
	            			<div class="col-md-12 col-xs-12 control">
	              				<div style="border-top: 1px solid #888; padding-top: 15px; font-size: 85%" ><a href="#signupbox" onClick="$('#loginbox').hide(); $('#signupbox').show()">Go to SIGN UP</a>
	              				</div>
		            		</div>
		          		</div>
		        	</div>
		      	</div>
		    </div>
		</div>
  		<div id="signupbox" style="display: none;" class="mainbox center-of-screen col-lg-4 col-lg-offset-0 col-md-6 col-md-offset-0 col-sm-8 col-sm-offset-0 col-xs-12 col-xs-offset-0">
	    	<div class="panel panel-info">
	      		<div class="panel-heading">
	        		<div class="panel-title">SIGN UP</div>
	      		</div>
	      		<div class="panel-body" >
	        		<div id="signupform" class="form-horizontal" role="form">
            			<div style="margin-bottom: 25px;" class="input-group">
				            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
				            <input id="signup-id" type="text" class="form-control" value="" placeholder="Username" required></input>
	          			</div>
			            <div style="margin-bottom: 25px;" class="input-group">
				            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
				            <input id="signup-pw" type="password" class="form-control" value="" placeholder="Password" required></input>
		          		</div>
						<div class="form-group">
						  	<div class="center col-md-12 col-xs-12 control">
						      	<button id="signup-btn" type="button" class="btn btn-danger"><i class="glyphicon glyphicon-send"></i> Sign up</button>
		              		</div>
		          		</div>
						<div class="form-group">
							<div class="col-md-12 col-xs-12 control">
								<div style="border-top: 1px solid #888; padding-top: 15px; font-size: 85%" ><a href="#loginbox" onclick="$('#signupbox').hide(); $('#loginbox').show()">Go back to SIGN IN</a>
							  	</div>
							</div>
						</div>
	        		</div>
	      		</div>
	    	</div>
	  	</div>
	</div>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
	(function() {
	  'use strict';

	  var root = typeof window === 'object' ? window : {};
	  var NODE_JS = !root.JS_SHA1_NO_NODE_JS && typeof process === 'object' && process.versions && process.versions.node;
	  if (NODE_JS) {
	    root = global;
	  }
	  var COMMON_JS = !root.JS_SHA1_NO_COMMON_JS && typeof module === 'object' && module.exports;
	  var AMD = typeof define === 'function' && define.amd;
	  var HEX_CHARS = '0123456789abcdef'.split('');
	  var EXTRA = [-2147483648, 8388608, 32768, 128];
	  var SHIFT = [24, 16, 8, 0];
	  var OUTPUT_TYPES = ['hex', 'array', 'digest', 'arrayBuffer'];

	  var blocks = [];

	  var createOutputMethod = function (outputType) {
	    return function (message) {
	      return new Sha1(true).update(message)[outputType]();
	    };
	  };

	  var createMethod = function () {
	    var method = createOutputMethod('hex');
	    if (NODE_JS) {
	      method = nodeWrap(method);
	    }
	    method.create = function () {
	      return new Sha1();
	    };
	    method.update = function (message) {
	      return method.create().update(message);
	    };
	    for (var i = 0; i < OUTPUT_TYPES.length; ++i) {
	      var type = OUTPUT_TYPES[i];
	      method[type] = createOutputMethod(type);
	    }
	    return method;
	  };

	  var nodeWrap = function (method) {
	    var crypto = require('crypto');
	    var Buffer = require('buffer').Buffer;
	    var nodeMethod = function (message) {
	      if (typeof message === 'string') {
	        return crypto.createHash('sha1').update(message, 'utf8').digest('hex');
	      } else if (message.constructor === ArrayBuffer) {
	        message = new Uint8Array(message);
	      } else if (message.length === undefined) {
	        return method(message);
	      }
	      return crypto.createHash('sha1').update(new Buffer(message)).digest('hex');
	    };
	    return nodeMethod;
	  };

	  function Sha1(sharedMemory) {
	    if (sharedMemory) {
	      blocks[0] = blocks[16] = blocks[1] = blocks[2] = blocks[3] =
	      blocks[4] = blocks[5] = blocks[6] = blocks[7] =
	      blocks[8] = blocks[9] = blocks[10] = blocks[11] =
	      blocks[12] = blocks[13] = blocks[14] = blocks[15] = 0;
	      this.blocks = blocks;
	    } else {
	      this.blocks = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	    }

	    this.h0 = 0x67452301;
	    this.h1 = 0xEFCDAB89;
	    this.h2 = 0x98BADCFE;
	    this.h3 = 0x10325476;
	    this.h4 = 0xC3D2E1F0;

	    this.block = this.start = this.bytes = this.hBytes = 0;
	    this.finalized = this.hashed = false;
	    this.first = true;
	  }

	  Sha1.prototype.update = function (message) {
	    if (this.finalized) {
	      return;
	    }
	    var notString = typeof(message) !== 'string';
	    if (notString && message.constructor === root.ArrayBuffer) {
	      message = new Uint8Array(message);
	    }
	    var code, index = 0, i, length = message.length || 0, blocks = this.blocks;

	    while (index < length) {
	      if (this.hashed) {
	        this.hashed = false;
	        blocks[0] = this.block;
	        blocks[16] = blocks[1] = blocks[2] = blocks[3] =
	        blocks[4] = blocks[5] = blocks[6] = blocks[7] =
	        blocks[8] = blocks[9] = blocks[10] = blocks[11] =
	        blocks[12] = blocks[13] = blocks[14] = blocks[15] = 0;
	      }

	      if(notString) {
	        for (i = this.start; index < length && i < 64; ++index) {
	          blocks[i >> 2] |= message[index] << SHIFT[i++ & 3];
	        }
	      } else {
	        for (i = this.start; index < length && i < 64; ++index) {
	          code = message.charCodeAt(index);
	          if (code < 0x80) {
	            blocks[i >> 2] |= code << SHIFT[i++ & 3];
	          } else if (code < 0x800) {
	            blocks[i >> 2] |= (0xc0 | (code >> 6)) << SHIFT[i++ & 3];
	            blocks[i >> 2] |= (0x80 | (code & 0x3f)) << SHIFT[i++ & 3];
	          } else if (code < 0xd800 || code >= 0xe000) {
	            blocks[i >> 2] |= (0xe0 | (code >> 12)) << SHIFT[i++ & 3];
	            blocks[i >> 2] |= (0x80 | ((code >> 6) & 0x3f)) << SHIFT[i++ & 3];
	            blocks[i >> 2] |= (0x80 | (code & 0x3f)) << SHIFT[i++ & 3];
	          } else {
	            code = 0x10000 + (((code & 0x3ff) << 10) | (message.charCodeAt(++index) & 0x3ff));
	            blocks[i >> 2] |= (0xf0 | (code >> 18)) << SHIFT[i++ & 3];
	            blocks[i >> 2] |= (0x80 | ((code >> 12) & 0x3f)) << SHIFT[i++ & 3];
	            blocks[i >> 2] |= (0x80 | ((code >> 6) & 0x3f)) << SHIFT[i++ & 3];
	            blocks[i >> 2] |= (0x80 | (code & 0x3f)) << SHIFT[i++ & 3];
	          }
	        }
	      }

	      this.lastByteIndex = i;
	      this.bytes += i - this.start;
	      if (i >= 64) {
	        this.block = blocks[16];
	        this.start = i - 64;
	        this.hash();
	        this.hashed = true;
	      } else {
	        this.start = i;
	      }
	    }
	    if (this.bytes > 4294967295) {
	      this.hBytes += this.bytes / 4294967296 << 0;
	      this.bytes = this.bytes % 4294967296;
	    }
	    return this;
	  };

	  Sha1.prototype.finalize = function () {
	    if (this.finalized) {
	      return;
	    }
	    this.finalized = true;
	    var blocks = this.blocks, i = this.lastByteIndex;
	    blocks[16] = this.block;
	    blocks[i >> 2] |= EXTRA[i & 3];
	    this.block = blocks[16];
	    if (i >= 56) {
	      if (!this.hashed) {
	        this.hash();
	      }
	      blocks[0] = this.block;
	      blocks[16] = blocks[1] = blocks[2] = blocks[3] =
	      blocks[4] = blocks[5] = blocks[6] = blocks[7] =
	      blocks[8] = blocks[9] = blocks[10] = blocks[11] =
	      blocks[12] = blocks[13] = blocks[14] = blocks[15] = 0;
	    }
	    blocks[14] = this.hBytes << 3 | this.bytes >> 29;
	    blocks[15] = this.bytes << 3;
	    this.hash();
	  };

	  Sha1.prototype.hash = function () {
	    var a = this.h0, b = this.h1, c = this.h2, d = this.h3, e = this.h4;
	    var f, j, t, blocks = this.blocks;

	    for(j = 16; j < 80; ++j) {
	      t = blocks[j - 3] ^ blocks[j - 8] ^ blocks[j - 14] ^ blocks[j - 16];
	      blocks[j] =  (t << 1) | (t >>> 31);
	    }

	    for(j = 0; j < 20; j += 5) {
	      f = (b & c) | ((~b) & d);
	      t = (a << 5) | (a >>> 27);
	      e = t + f + e + 1518500249 + blocks[j] << 0;
	      b = (b << 30) | (b >>> 2);

	      f = (a & b) | ((~a) & c);
	      t = (e << 5) | (e >>> 27);
	      d = t + f + d + 1518500249 + blocks[j + 1] << 0;
	      a = (a << 30) | (a >>> 2);

	      f = (e & a) | ((~e) & b);
	      t = (d << 5) | (d >>> 27);
	      c = t + f + c + 1518500249 + blocks[j + 2] << 0;
	      e = (e << 30) | (e >>> 2);

	      f = (d & e) | ((~d) & a);
	      t = (c << 5) | (c >>> 27);
	      b = t + f + b + 1518500249 + blocks[j + 3] << 0;
	      d = (d << 30) | (d >>> 2);

	      f = (c & d) | ((~c) & e);
	      t = (b << 5) | (b >>> 27);
	      a = t + f + a + 1518500249 + blocks[j + 4] << 0;
	      c = (c << 30) | (c >>> 2);
	    }

	    for(; j < 40; j += 5) {
	      f = b ^ c ^ d;
	      t = (a << 5) | (a >>> 27);
	      e = t + f + e + 1859775393 + blocks[j] << 0;
	      b = (b << 30) | (b >>> 2);

	      f = a ^ b ^ c;
	      t = (e << 5) | (e >>> 27);
	      d = t + f + d + 1859775393 + blocks[j + 1] << 0;
	      a = (a << 30) | (a >>> 2);

	      f = e ^ a ^ b;
	      t = (d << 5) | (d >>> 27);
	      c = t + f + c + 1859775393 + blocks[j + 2] << 0;
	      e = (e << 30) | (e >>> 2);

	      f = d ^ e ^ a;
	      t = (c << 5) | (c >>> 27);
	      b = t + f + b + 1859775393 + blocks[j + 3] << 0;
	      d = (d << 30) | (d >>> 2);

	      f = c ^ d ^ e;
	      t = (b << 5) | (b >>> 27);
	      a = t + f + a + 1859775393 + blocks[j + 4] << 0;
	      c = (c << 30) | (c >>> 2);
	    }

	    for(; j < 60; j += 5) {
	      f = (b & c) | (b & d) | (c & d);
	      t = (a << 5) | (a >>> 27);
	      e = t + f + e - 1894007588 + blocks[j] << 0;
	      b = (b << 30) | (b >>> 2);

	      f = (a & b) | (a & c) | (b & c);
	      t = (e << 5) | (e >>> 27);
	      d = t + f + d - 1894007588 + blocks[j + 1] << 0;
	      a = (a << 30) | (a >>> 2);

	      f = (e & a) | (e & b) | (a & b);
	      t = (d << 5) | (d >>> 27);
	      c = t + f + c - 1894007588 + blocks[j + 2] << 0;
	      e = (e << 30) | (e >>> 2);

	      f = (d & e) | (d & a) | (e & a);
	      t = (c << 5) | (c >>> 27);
	      b = t + f + b - 1894007588 + blocks[j + 3] << 0;
	      d = (d << 30) | (d >>> 2);

	      f = (c & d) | (c & e) | (d & e);
	      t = (b << 5) | (b >>> 27);
	      a = t + f + a - 1894007588 + blocks[j + 4] << 0;
	      c = (c << 30) | (c >>> 2);
	    }

	    for(; j < 80; j += 5) {
	      f = b ^ c ^ d;
	      t = (a << 5) | (a >>> 27);
	      e = t + f + e - 899497514 + blocks[j] << 0;
	      b = (b << 30) | (b >>> 2);

	      f = a ^ b ^ c;
	      t = (e << 5) | (e >>> 27);
	      d = t + f + d - 899497514 + blocks[j + 1] << 0;
	      a = (a << 30) | (a >>> 2);

	      f = e ^ a ^ b;
	      t = (d << 5) | (d >>> 27);
	      c = t + f + c - 899497514 + blocks[j + 2] << 0;
	      e = (e << 30) | (e >>> 2);

	      f = d ^ e ^ a;
	      t = (c << 5) | (c >>> 27);
	      b = t + f + b - 899497514 + blocks[j + 3] << 0;
	      d = (d << 30) | (d >>> 2);

	      f = c ^ d ^ e;
	      t = (b << 5) | (b >>> 27);
	      a = t + f + a - 899497514 + blocks[j + 4] << 0;
	      c = (c << 30) | (c >>> 2);
	    }

	    this.h0 = this.h0 + a << 0;
	    this.h1 = this.h1 + b << 0;
	    this.h2 = this.h2 + c << 0;
	    this.h3 = this.h3 + d << 0;
	    this.h4 = this.h4 + e << 0;
	  };

	  Sha1.prototype.hex = function () {
	    this.finalize();

	    var h0 = this.h0, h1 = this.h1, h2 = this.h2, h3 = this.h3, h4 = this.h4;

	    return HEX_CHARS[(h0 >> 28) & 0x0F] + HEX_CHARS[(h0 >> 24) & 0x0F] +
	           HEX_CHARS[(h0 >> 20) & 0x0F] + HEX_CHARS[(h0 >> 16) & 0x0F] +
	           HEX_CHARS[(h0 >> 12) & 0x0F] + HEX_CHARS[(h0 >> 8) & 0x0F] +
	           HEX_CHARS[(h0 >> 4) & 0x0F] + HEX_CHARS[h0 & 0x0F] +
	           HEX_CHARS[(h1 >> 28) & 0x0F] + HEX_CHARS[(h1 >> 24) & 0x0F] +
	           HEX_CHARS[(h1 >> 20) & 0x0F] + HEX_CHARS[(h1 >> 16) & 0x0F] +
	           HEX_CHARS[(h1 >> 12) & 0x0F] + HEX_CHARS[(h1 >> 8) & 0x0F] +
	           HEX_CHARS[(h1 >> 4) & 0x0F] + HEX_CHARS[h1 & 0x0F] +
	           HEX_CHARS[(h2 >> 28) & 0x0F] + HEX_CHARS[(h2 >> 24) & 0x0F] +
	           HEX_CHARS[(h2 >> 20) & 0x0F] + HEX_CHARS[(h2 >> 16) & 0x0F] +
	           HEX_CHARS[(h2 >> 12) & 0x0F] + HEX_CHARS[(h2 >> 8) & 0x0F] +
	           HEX_CHARS[(h2 >> 4) & 0x0F] + HEX_CHARS[h2 & 0x0F] +
	           HEX_CHARS[(h3 >> 28) & 0x0F] + HEX_CHARS[(h3 >> 24) & 0x0F] +
	           HEX_CHARS[(h3 >> 20) & 0x0F] + HEX_CHARS[(h3 >> 16) & 0x0F] +
	           HEX_CHARS[(h3 >> 12) & 0x0F] + HEX_CHARS[(h3 >> 8) & 0x0F] +
	           HEX_CHARS[(h3 >> 4) & 0x0F] + HEX_CHARS[h3 & 0x0F] +
	           HEX_CHARS[(h4 >> 28) & 0x0F] + HEX_CHARS[(h4 >> 24) & 0x0F] +
	           HEX_CHARS[(h4 >> 20) & 0x0F] + HEX_CHARS[(h4 >> 16) & 0x0F] +
	           HEX_CHARS[(h4 >> 12) & 0x0F] + HEX_CHARS[(h4 >> 8) & 0x0F] +
	           HEX_CHARS[(h4 >> 4) & 0x0F] + HEX_CHARS[h4 & 0x0F];
	  };

	  Sha1.prototype.toString = Sha1.prototype.hex;

	  Sha1.prototype.digest = function () {
	    this.finalize();

	    var h0 = this.h0, h1 = this.h1, h2 = this.h2, h3 = this.h3, h4 = this.h4;

	    return [
	      (h0 >> 24) & 0xFF, (h0 >> 16) & 0xFF, (h0 >> 8) & 0xFF, h0 & 0xFF,
	      (h1 >> 24) & 0xFF, (h1 >> 16) & 0xFF, (h1 >> 8) & 0xFF, h1 & 0xFF,
	      (h2 >> 24) & 0xFF, (h2 >> 16) & 0xFF, (h2 >> 8) & 0xFF, h2 & 0xFF,
	      (h3 >> 24) & 0xFF, (h3 >> 16) & 0xFF, (h3 >> 8) & 0xFF, h3 & 0xFF,
	      (h4 >> 24) & 0xFF, (h4 >> 16) & 0xFF, (h4 >> 8) & 0xFF, h4 & 0xFF
	    ];
	  };

	  Sha1.prototype.array = Sha1.prototype.digest;

	  Sha1.prototype.arrayBuffer = function () {
	    this.finalize();

	    var buffer = new ArrayBuffer(20);
	    var dataView = new DataView(buffer);
	    dataView.setUint32(0, this.h0);
	    dataView.setUint32(4, this.h1);
	    dataView.setUint32(8, this.h2);
	    dataView.setUint32(12, this.h3);
	    dataView.setUint32(16, this.h4);
	    return buffer;
	  };

	  var exports = createMethod();

	  if (COMMON_JS) {
	    module.exports = exports;
	  } else {
	    root.sha1 = exports;
	    if (AMD) {
	      define(function () {
	        return exports;
	      });
	    }
	  }
	})();

	$("#login-id").focus(function(){
	  $(this).css('border-color','');
	});

	$("#login-id").blur(function(){
	  if (!/^[a-zA-Z][a-zA-Z0-9_]{4,}$/.test($(this).val())) $(this).css('border-color','#D52349');
	  else $(this).css('border-color','#50B948');
	});

	$("#login-pw").focus(function(){
	  $(this).css('border-color','');
	});

	$("#login-pw").blur(function(){
	  if ($(this).val().length<8)  $(this).css('border-color','#D52349');
	  else $(this).css('border-color','#50B948');
	});

	$("#login-btn").click(function(){
	  var id = $("#login-id").val().toLowerCase();
	  var pw = $("#login-pw").val();
	  var guesttoken = $("#guest-token").val();
	  if (/^[a-zA-Z][a-zA-Z0-9_]{4,}$/.test(id) && pw.length >= 8){
	    $.ajax({
	      url: "auth.php",
	      method: "post",
	      data: { loginbtn : "true", guesttoken : guesttoken, loginid : id, loginpw : sha1(pw)},
	      success : function(response){
	        if (response == "login success"){
	          window.location="";
	        }
	        else if (response == "login failure"){
	          alert("Oops! Something wen't wrong!");
	          window.location="";
	        }
	      }
	    });
	  }
	});

	$("#signup-id").focus(function(){
	  $(this).css('border-color','');
	});

	$("#signup-id").blur(function(){
	  if (!/^[a-zA-Z][a-zA-Z0-9_]{4,}$/.test($(this).val())) $(this).css('border-color','#D52349')
	  else $(this).css('border-color','#50B948');
	});

	$("#signup-pw").focus(function(){
	  $(this).css('border-color','');
	});

	$("#signup-pw").blur(function(){
	  if ($(this).val().length<8)  $(this).css('border-color','#D52349');
	  else $(this).css('border-color','#50B948');
	});

	$("#signup-btn").click(function(){
	  $("*").css("cursor", "wait");
	  var id = $("#signup-id").val().toLowerCase();
	  var pw = $("#signup-pw").val();
	  var guesttoken = $("#guest-token").val();
	  if (/^[a-zA-Z][a-zA-Z0-9_]{4,}$/.test(id) && pw.length>=8){
	    $.ajax({
	      url: "auth.php",
	      type: "POST",
	      data: { signupbtn : "true", guesttoken : guesttoken, signupid : id, signuppw : pw},
	      success : function(response){
	      	$("*").css("cursor", "default");
	        if (response == "signup success"){
	          alert("Bravo! Let's enjoy your playlist!");
	          window.location="";
	        }
	        else if (response == "signup failure"){
	          alert("Oops! Something wen't wrong!");
	          window.location="";
	        }
	      }
	    });
	  }
	});
</script>
</body>
</html>
