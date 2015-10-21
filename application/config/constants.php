<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
 */
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 					'ab');
define('FOPEN_READ_WRITE_CREATE', 				'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 			'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/* General Admin constants */
define('SUPPORT_EMAIL', 'director@smbstreamline.com.au');
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

define('LOGIN_MESSAGE_SESSION_IDLE', 0);
define('LOGIN_MESSAGE_SESSION_EXPIRED', 1);


// In addition to these, special regular expressions should be set up to prevent
// the following patterns:
// - filenames ending with CLSID's
//   \.[a-z][a-z0-9]{2,3}\s*\.[a-z0-9]{3}$
// - Filenames with many contiguous white spaces in them
//    \s{10,}
// - double file extensions
// \.[a-z][a-z0-9]{2,3}\s*\.[a-z0-9]{3}$c
define('RESTRICTED_EXTENSIONS', serialize(array(
    'ade','adp','app','bas',
    'bat','chm','class','cmd',  'cnf','com','cpl',
    'crt','dll','exe',  'fxp',  'hlp','hta','inf',
    'ins','isp','js',   'jse',  'lnk','mad','maf',
    'mag','mam','maq',  'mar',  'mas','mat','mav',
    'maw','mdb','mde',  'mhtml','msc','msi','msp',
    'mst','ops','pcd',  'pif',  'prf','prg','reg',
    'scf','scr','sct',  'shb',  'shs','url','vb',
    'vbe','vbs','wsc',  'wsf',  'wsh','xnk',
    'ADE','ADP','APP','BAS',
    'BAT','CHM','CLASS','CMD',  'CNF','COM','CPL',
    'CRT','DLL','EXE',  'FXP',  'HLP','HTA','INF',
    'INS','ISP','JS',   'JSE',  'LNK','MAD','MAF',
    'MAG','MAM','MAQ',  'MAR',  'MAS','MAT','MAV',
    'MAW','MDB','MDE',  'MHTML','MSC','MSI','MSP',
    'MST','OPS','PCD',  'PIF',  'PRF','PRG','REG',
    'SCF','SCR','SCT',  'SHB',  'SHS','URL','VB',
    'VBE','VBS','WSC',  'WSF',  'WSH','XNK'
    )));

/* User system */
define('USERS_MESSAGE_UPDATED_OK', 1);

define('USERS_CONTACT_TYPE_EMAIL', 1);
define('USERS_CONTACT_TYPE_PHONE', 2);
define('USERS_CONTACT_TYPE_MOBILE', 3);
define('USERS_CONTACT_TYPE_FAX', 4);

/* Companies */
define('COMPANY_ROLE_SUPPLIER', 1);
define('COMPANY_ROLE_CUSTOMER', 2);


// Create a JS file on-the-fly to declare custom constants
$constants = get_defined_constants();

$userconstants = $constants;
$jsconstants = "var constants = {};\n";
$allowedconstants = array('^USER', '^COMPANY');
$forbiddenconstants = array();

foreach ($userconstants as $constant => $value) {
    $constantok = false;
    foreach ($allowedconstants as $pattern) {
        if (preg_match('/'.$pattern.'/', $constant)) {
            $constantok = true;
        }
    }
    foreach ($forbiddenconstants as $pattern) {
        if (preg_match('/'.$pattern.'/', $constant)) {
            $constantok = false;
        }
    }
    if ($constantok) {
        $jsconstants .= "constants.$constant = '".str_replace("'", "\'", $value)."';\n";
    }
}
file_put_contents(ROOTPATH.'/includes/js/constants.js', $jsconstants);

/* End of file constants.php */
/* Location: ./system/application/config/constants.php */
