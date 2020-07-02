<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
use Twilio\Rest\Client;

class Aloha
{
    private $csv = array();
    private $debug = array();
    private $data = array();
    private $results = array();


    public function __construct($params = array())
    {
        $this->_ci = &get_instance();
        if (array_key_exists('file', $params)) {
            $this->load_csv($params['file']);
        }
        $this->_ci->load->library('Streams');
    }

    public function getReport($data, $host_id, $send_to)
    {
        // If user selected the dates in reverse order then we fix it here.
        if ($data['endDate'] < $data['startDate']) {
            $endDate = $data['endDate'];
            $startDate = $data['startDate'];
            $data['endDate'] = $startDate;
            $data['startDate'] = $endDate;
        }

        // Get whole name of report.
        $report_name = $data['reportSetting'] . '.exp';  // 'Default.sls' . '.exp'
        // Convert string of time to ISO 8601 (YYYY-MM-DD).
        $start_date = substr($data['startDate'], 0, 4).'-'.substr($data['startDate'], -4);
        $start_date = substr($start_date, 0, 7).'-'.substr($start_date, -2);
        $end_date = substr($data['endDate'], 0, 4).'-'.substr($data['endDate'], -4);
        $end_date = substr($end_date, 0, 7).'-'.substr($end_date, -2);
        $unix_start_date = strtotime($start_date);
        $unix_end_date = strtotime($end_date);

        $report = $this->_ci->streams->entries->get_entries(array(
            'stream'    => 'aloha',
            'namespace' => 'reports',
            'limit'     => 1,
            // Multiple "where" values will fail, so need to put it into one "where" clause.
            'where'     => "host_id='$host_id' AND report_name='$report_name' AND start_date='$unix_start_date' AND end_date='$unix_end_date'",
        ));

        // If the report has already been pulled and saved to the database
        // then there is no need to have the host generate it again.
        if (array_key_exists('0', $report['entries'])) {
            $report_html = $report['entries'][0]['report_html'];
            $report_csv  = $report['entries'][0]['report_csv'];
        } else {
            $days = $this->findDays($data);
            $this->_ci->load->library('connect');

            $cmd = $this->buildExportCommand($data);

            //before we execute, we need to create and start a script to kill the rpt.exe if it hangs after x seconds
            $killtask = $this->_ci->connect->hostcmd('for %I in (\"%SWITCHR_DIR%\") do echo taskkill /F /IM rpt.exe>"%SWITCHR_DIR%killrpt.bat"', $host_id);

            $time = $this->_ci->connect->hostcmd("time /T", $host_id);
            $timestamp = strtotime($time);
            $newtime = date('H:i', strtotime("+1 minute", $timestamp));

            $this->_ci->connect->hostcmd('at ' . $newtime . ' "%SWITCHR_DIR%killrpt.bat"', $host_id);
            $errorExp = $this->_ci->connect->hostcmd($cmd, $host_id);

            // Use new serverIsUp() here?
            if ($errorExp != false) {
                return '<div class="alert alert-warning">There was no connection to the host, check to make sure that it is online and connected to us. Also please make sure you are logged in.</div>';
            }
            if ($days > 320) {
                sleep(20);
            } elseif ($days > 200) {
                sleep(20);
            } elseif ($days > 10) {
                sleep(10);
            } elseif ($days < 10) {
                sleep(5);
            }
            $code = $this->getReportCode($data['type']);

            $response = $this->_ci->connect->hostcmd('type %IBERDIR%\\RPTEXPORT\\' . $data['reportSetting'] . '.csv', $host_id);  // Was '.exp'.
        }
        switch ($send_to) {
            case 'screen':
                //parse to screen
                if (!$report_html) {
                    $tmpfname = tempnam(getcwd()."/tmp", "FOO") . '.csv';
                    $file = fopen($tmpfname, 'w');
                    fwrite($file, $response);
                    fclose($file);
                    $report_html = $this->parseReport($tmpfname);
                    // Save the report to the database since it doesn't exist.
                    if ($data['startDate'] != 'Today') {
                        $entry_data = array(
                            'host_id'     => $host_id,
                            'report_name' => $report_name,
                            'start_date'  => $start_date,
                            'end_date'    => $end_date,
                            'report_html' => $report_html,
                            'report_csv'  => $response,
                        );
                        $this->_ci->streams->entries->insert_entry($entry_data, 'aloha', 'reports');
                    }
                    unlink($tmpfname);
                }
                echo $report_html;
                die();
            case 'email':
                //email to default user
                $config['protocol'] = Settings::get('mail_protocol');
                $config['charset'] = 'iso-8859-1';
                $config['wordwrap'] = true;
                $config['smtp_host'] = Settings::get('mail_smtp_host');
                $config['smtp_user'] = Settings::get('mail_smtp_user');
                $config['smtp_pass'] = Settings::get('mail_smtp_pass');
                $config['smtp_port'] = Settings::get('mail_smtp_port');
                $this->_ci->email->initialize($config);
                $this->_ci->email->from('no-reply@switchr.io', 'Switchr Reports');
                $this->_ci->email->to($this->_ci->current_user->email);
                $this->_ci->email->subject('Report - ' . $data['type']);

                //create tmp file to attach
                if (!$report_csv) {
                    $tmpfname = tempnam(getcwd()."/tmp", "FOO") . '.csv';
                    $file = fopen($tmpfname, 'w');
                    fwrite($file, $response);
                    fclose($file);
                    $this->_ci->email->attach($tmpfname, 'attachment', $data['mod'] ."-". $data['type'] ."-Report_ " . $data['startDate'] ."-". $data['endDate'] . ".csv");
                    // Save the report to the database since it doesn't exist.
                    if ($data['startDate'] != 'Today') {
                        $report_html = $this->parseReport($tmpfname);
                        $entry_data = array(
                            'host_id'     => $host_id,
                            'report_name' => $report_name,
                            'start_date'  => strtotime($start_date),
                            'end_date'    => strtotime($end_date),
                            'report_html' => $report_html,
                            'report_csv'  => $response,
                        );
                        $this->_ci->streams->entries->insert_entry($entry_data, 'aloha', 'reports');
                    }
                } else {
                    $tmpfname = tempnam(getcwd()."/tmp", "FOO") . '.csv';
                    $file = fopen($tmpfname, 'w');
                    fwrite($file, $report_csv);
                    fclose($file);
                    $this->_ci->email->attach($tmpfname, 'attachment', $data['mod'] ."-". $data['type'] ."-Report_ " . $data['startDate'] ."-". $data['endDate'] . ".csv");
                }
                $this->_ci->email->set_mailtype("html");
                $report_html = '<table><tbody>' . $report_html;
                $report_html .= '</tbody></table>';
                $this->_ci->email->message($report_html);
                $sent = $this->_ci->email->send();
                if ($tmpfname) {
                    unlink($tmpfname);
                }
                //echo $this->_ci->email->print_debugger();
                die();
            case 'sms':
                if ($report_csv) {
                    $response = $report_csv;
                }
                $tmpfname = tempnam(getcwd()."/tmp", $data['startDate'] ."-". $data['endDate']."-") . '.csv';
                $file = fopen($tmpfname, 'w');
                fwrite($file, $response);
                fclose($file);
                // Save the report to the database since it doesn't exist.
                if ($data['startDate'] != 'Today') {
                    $report_html = $this->parseReport($tmpfname);
                    $entry_data = array(
                        'host_id'     => $host_id,
                        'report_name' => $report_name,
                        'start_date'  => strtotime($start_date),
                        'end_date'    => strtotime($end_date),
                        'report_html' => $report_html,
                        'report_csv'  => $response,
                    );
                    $this->_ci->streams->entries->insert_entry($entry_data, 'aloha', 'reports');
                }
                //sms to users phone number in profile
                $path = '/addons/shared_addons/libraries/twilio-php-master';
                // set_include_path(getcwd() . $path);
                set_include_path($_SERVER['DOCUMENT_ROOT'] . $path);
                include_once('Twilio/autoload.php');
                // Use the REST API Client to make requests to the Twilio REST API

                // Your Account SID and Auth Token from twilio.com/console
                $sid = 'AC21c9d4d9a6f60bfc4242647ca09d32f4';
                $token = 'ede141b921b9a0386d944dd2690088d2';
                $client = new Client($sid, $token);
                // Use the client to do fun stuff like send text messages!
                $client->messages->create(
                    // the number you'd like to send the message to
                    '+1'.$data['sendToNum'],
                    array(
                        // A Twilio phone number you purchased at twilio.com/console
                        'from' => '+19704451208',
                        // the body of the text message you'd like to send
                        'body' => "A report has been sent: " . substr(BASE_URL, 0, -1) . $tmpfname
                    )
                );
                //unlink($tmpfname);  // Can't delete if it's needed to be viewed.
                die();
        }
        return false;
    }

    public function buildExportCommand($data)
    {
        $code = $this->getReportCode($data['type']);

        if ($data['startDate'] == 'Today') {
            $cmd = '%IBERDIR%/BIN/RPT.EXE /DATE DATA /X' . $code['code'] . ' /load ' . $data['reportSetting'] . '.set /NODEPOSIT';
        } else {
            $cmd = '%IBERDIR%/BIN/RPT.EXE /DATE ' . $data['startDate'] . ' /X'.$code['code']. ' /DAYS ' . $this->findDays($data) . ' /load ' . $data['reportSetting'] . '.set /NODEPOSIT';
        }

        return $cmd;
    }

    //the /XC we use in the command for a sales report for example..the X is to flag export, C for sales.
    public function getReportCode($longType)
    {
        switch ($longType) {
            case 'sls':
                $type = 'sls';
                $code = 'C';
                break;
            case 'pay':
                $type = 'pay';
                $code = 'Y';
                break;
            case 'pmx':
                $type = 'pmx';
                $code = 'P';
                break;
            case 'lbr':
                $type = 'lbr';
                $code = 'A';
                break;
            // Employee break report
            case 'ebr':
                $type = 'brk';
                $code = 'B';
                break;
            // Weekly sales
            case 'wsr':
                $type = 'wsr';
                $code = 'CW';
                break;
            case 'adp':
                $type = 'adp';
                $code = 'D';
                break;
            // Delivery driver
            case 'dd':
                $type = 'ddr';
                $code = 'DD';
                break;
            // Tip income report
            case 'tip':
                $type = 'tip';
                $code = 'J';
                break;
            // Daily hourly sales and labor
            case 'hrs':
                $type = 'hrs';
                $code = 'H';
                break;
            // Weekly hourly sales and labor
            case 'whsl':
                $type = 'whsl';
                $code = 'HW';
                break;
            case 'wpmix':
                $type = 'wpmix';
                $code = 'PQ';
                break;
            case 'foh':
                $type = 'owe';
                $code = '7';
                break;
            case 'otf':
                $type = 'otf';
                $code = '11';
                break        ;
            case 'pd':
                $type = 'pay';
                $code = 'Y';
                break;
            case 'srev':
                $type = 'rev';
                $code = 'Z';
                break;
            case 'void':
                $type = 'voi';
                $code = 'V';
                break;
            case 'od':
                $type = 'od';
                $code = 'OD';
                break;
            case 'otw':
                $type = 'otw';
                $code = 'O';
                break;
            // case 'otf':
            //	$type = 'otf';
            //	$code = '11';
            //	break;
        }
        return array('type' => $type, 'code' => $code);
    }

    public function findDays($data)
    {
        /*
        Aloha Reports Notice:
        when you pull a report for the current day, it does not accept it as Ymd format. you must use the verbiage "DATA" and execute the report commandline with that verbiage.
        if pulling a report for some date plus the current date, you must then convert the date back to time to see how many days are within date1 and date2.
        ie: date1 is yesterday 4/9/2013, date2 is today. You must instead of using the word 'date' as the baseDate, you would use the baseDate 4/9, and then find how many days till current date
        then you would use those day differences as the /days flag with the baseDate.
        */

        $today = date('Ymd');
        if ($data['startDate'] != 'DATA' && $data['endDate'] != 'DATA') {
            // Sort the days if they put the range backwards
            $days = (strtotime($data['endDate'])-strtotime($data['startDate']));
            $days = ($days/60/60/24);
        } elseif ($data['startDate'] != 'DATA' && $data['endDate'] == 'DATA') {
            $days = (strtotime($today)-strtotime($data['baseDate']));
            $days = ($days/60/60/24);
        } else {
            $days = 0;
        }
        return $days;
    }

    public function downloadFile()
    {
        $tmpfname = tempnam("/tmp", "FOO") . '.csv';
        $file = fopen(getcwd().$tmpfname, 'w');
        fwrite($file, 'data');
        fclose($file);
        if (file_exists(getcwd().$tmpfname)) {
            header("Content-type: octet/stream");
            header("Content-disposition: attachment; filename=". getcwd().$tmpfname.";");
            header("Content-Length: ".filesize(getcwd().$tmpfname));
            readfile(getcwd().$tmpfname);
            //unlink($tmpfname);
            exit;
        } else {
            die("Error: File not found.");
        }
        die();
    }

    public function parseReport($file)
    {
        $report = null;
        $handle = fopen($file, 'r');
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {  // Reads one row on each loop.
            $num = count($data);  // Number of elements in array.
            $hr = false;  // Used to insert <hr>.
            if (substr($data[1], 0, 5) == "Voids") {  // Specific check for "Voids" on sales report. Catch it and insert <hr> above it.
                //echo '<tr style="display:grid;"><td><hr></td></tr>';
                $report .= '<tr style="display:grid;"><td><hr></td></tr>';
            }
            // echo '<tr>';
            $report .= '<tr>';
            for ($c=1; $c < $num; $c++) {  // For element in row.
                // Echo <hr> after every "Total" if "Total" is the first element in the row. Specific to sales report.
                if ($c == 1 && substr($data[$c], 0, 5) == "Total") {
                    $hr = true;
                }
                // Commented out to reduce spacing in favor of <hr>.
                // 1. if the whole line is blank leave it blank.
                // 2. if first line is blank it is a total line at some point or title/sub title
                // if ($data[$c] == " " || $data[$c] == "" || $data[$c] == "&#32;") {
                // } else {
                if (intval($data[$c]) || $data[$c] == "0.00 " || $data[$c] == "0.00" || $data[$c] == "0" || floatval($data[$c])) {
                    // echo '<td style="text-align:center; padding: 5px;">' . $data[$c] . '</td>';
                    $report .= '<td style="text-align:center; padding: 5px;">' . $data[$c] . '</td>';
                } else {
                    $splitData = explode(" ", $data[$c]);
                    if (isset($splitData[0])) {
                        $dataArray[0] = substr($splitData[0], 0, 4);
                    }

                    if (isset($splitData[1])) {
                        $dataArray[1] = substr($splitData[1], 0, 4);
                    }

                    if ($this->_ci->agent->is_mobile()) {
                        // echo '<td style="font-weight: bold; text-align:center; padding: 5px">';
                        $report .= '<td style="font-weight: bold; text-align:center; padding: 5px">';
                        if (isset($dataArray[0])) {
                            // echo $dataArray[0];
                            $report .= $dataArray[0];
                        }
                        // echo '<hr />';
                        if (isset($dataArray[1])) {
                            // echo $dataArray[1];
                            $report .= $dataArray[1];
                        }
                        // echo '</td>';
                        $report .= '</td>';
                    } else {
                        // echo '<td style="font-weight: bold; text-align:center;">' . $data[$c] . '</td>';
                        $report .= '<td style="font-weight: bold; text-align:center;">' . $data[$c] . '</td>';
                    }
                }
                // }
            }
            // echo '</tr>';
            $report .= '</tr>';
            if ($hr) {
                // echo '<tr style="display:grid;"><td><hr></td></tr>';
                $report .= '<tr style="display:grid;"><td><hr></td></tr>';
            }
        }
        fclose($handle);
        return $report;
    }

    public function getReportSettings($host_id)
    {
        $this->_ci->load->model('hosts/hosts_m');
        $host = $this->_ci->hosts_m->get_host($host_id);

        //connect to host with command to determine which .exp files are in the %IBERDIR%/rptExport dir
        $response = $this->_ci->connect->hostcmd("dir %IBERDIR%\\rptExport\\*.exp /B", $host_id);

        //clean response
        if (!preg_match("/exp/i", $response)) {
            return false;
        }
        $output = preg_split('/\s+/', $response);
        $files = array();
        foreach ($output as $key => $value) {
            // Check to make sure that "exp" is in the string and "\" is not in the string
            // (we're only interested in filenames, not anything with a path).
            // Then add to the array of files.
            // If this block is not used then the whole string is returned, SSH errors and all.
            if (substr($value, -3) == "exp" && !strpos($value, "\\")) {
                $files[] = $value;
            }
        }

        return $files;
    }

    //return array of dated subdirectorys in Aloha that we can option to report from to avoid crashes giving it non-existing dates.
    public function getDatedSubs($host_id)
    {
        $this->_ci->load->model('hosts/hosts_m');
        $host = $this->_ci->hosts_m->get_host($host_id);
        //command to suppress the output of last entry
        //$this->_ci->connect->hostcmd('echo ""', $host_id);
        $response = $this->_ci->connect->hostcmd("dir /B %IBERDIR%", $host_id);
        $newOut = array();
        $output = preg_split('/\s+/', $response);
        foreach ($output as $row => $value) {
            // If the string has anything other than digits and is not 8 characters long then discard.
            // Otherwise it's what we want. Dated subdirs all look like: 20170807
            $no_match = preg_match('/[^0-9]/', $value);
            if ($no_match == false && strlen($value) == 8) {
                $newOut[] = $value;
            }
        }
        arsort($newOut);
        return $newOut;
    }
    public function refreshData($host_id)
    {
        $this->_ci->load->model('hosts/hosts_m');
        $host = $this->_ci->hosts_m->get_host($host_id);

        $response = $this->_ci->connect->hostcmd("echo \"STOP\">%IBERDIR%/tmp/STOP", $host_id, false);
        return $response;
    }
    //this corrects an aloha terminal when it reads "waiting to grind date YYYYMMDD"
    public function fixWaitingGrind($host_id, $date)
    {
        $this->_ci->load->model('hosts/hosts_m');
        $fix_response = $this->_ci->connect->hostcmd("echo \"sometextinafile\">%IBERDIR%/".$date."/gnddbf30.xxx", $host_id, false);
        $regrind_response = $this->_ci->connect->hostcmd("%IBERDIR%/bin/grind.exe /date " . $date, $host_id, false);
        return $fix_response;
    }
    public function settleBatch($host_id)
    {
        $this->_ci->load->model('hosts/hosts_m');
        //remove any old answer files so that the batch can recreate it without error of existing already.
        $response = $this->_ci->connect->hostcmd('del "%IBERDIR%\EDC\10000001.ans"', $host_id, false);
        $response = $this->_ci->connect->hostcmd('copy "%IBERDIR%\AUTOSETTLE\10000001.req" "%IBERDIR%\EDC\10000001.req"', $host_id, false);
        return $response;
    }
    public function getLastSettled($host_id)
    {
        $this->_ci->load->model('hosts/hosts_m');
        //remove any old answer files so that the batch can recreate it without error of existing already.
        $response = $this->_ci->connect->hostcmd("for %a in (\"%EDCPROCPATH%\CHANGED\") do set LASTSETTLE=%~ta", $host_id, false);
        $date = substr($response, 37);
        return $date;
    }
    public function getDebugFile($host_id, $filename){
        $this->_ci->load->model('hosts/hosts_m');
        $response = $this->_ci->connect->hostcmd("type c:\\temp\\" . urldecode($filename), $host_id, false);
        if($response == "" || $response == " "){
            return false;
        }
        return explode("\n", $response);
    }
}
